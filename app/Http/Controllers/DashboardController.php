<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 0. Notícias do Mundo Real (RSS CNN Brasil - Tecnologia)
        $news = [];
        try {
            $response = Http::timeout(3)->get('https://www.cnnbrasil.com.br/tecnologia/feed/');
            if ($response->successful()) {
                $xml = simplexml_load_string($response->body());
                if ($xml) {
                    $count = 0;
                    foreach ($xml->channel->item as $item) {
                        if ($count >= 3) break; // Pegar apenas as 3 últimas
                        
                        // Extrair imagem se houver (media:content ou enclosure)
                        $image = null;
                        $nsMedia = $item->children('http://search.yahoo.com/mrss/');
                        if ($nsMedia && $nsMedia->content) {
                            $attributes = $nsMedia->content->attributes();
                            $image = (string)$attributes['url'];
                        }

                        $news[] = [
                            'title' => (string)$item->title,
                            'link' => (string)$item->link,
                            'date' => Carbon::parse((string)$item->pubDate)->diffForHumans(),
                            'image' => $image
                        ];
                        $count++;
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail
        }

        // 1. Total de Usuários
        $totalUsers = User::count();

        // 2. Usuários Ativos vs Inativos
        $activeUsers = User::where('status', 1)->count();
        $inactiveUsers = User::where('status', 0)->count();

        // 3. Novos Usuários (Últimos 30 dias)
        $newUsersMonth = User::where('criado_em', '>=', now()->subDays(30))->count();
        
        // Comparativo com mês anterior para setinha (growth)
        $newUsersLastMonth = User::whereBetween('criado_em', [now()->subDays(60), now()->subDays(30)])->count();
        $growth = $newUsersLastMonth > 0 
            ? (($newUsersMonth - $newUsersLastMonth) / $newUsersLastMonth) * 100 
            : ($newUsersMonth > 0 ? 100 : 0);

        // --- Novas Métricas Financeiras ---
        
        // Lucro Realizado (Concluídos) - Este Mês
        $lucroRealizadoMes = Servico::where('status', 'concluido')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('lucro_estimado');

        // Adicionar Recorrência ao Lucro do Mês
        $lucroRealizadoMes += Servico::where('recorrente', 1)
            ->where('status', '!=', 'cancelado')
            ->where('data_servico', '<=', now()->endOfMonth())
            ->where(function($query) {
                $query->where('prazo_entrega', '>=', now()->startOfMonth())
                      ->orWhereNull('prazo_entrega');
            })
            ->sum('valor_recorrencia');

        // Lucro Presumido (Em Andamento/Pendente) - Total
        $lucroPresumido = Servico::whereIn('status', ['pendente', 'em_andamento'])
            ->sum('lucro_estimado');

        // Serviços Concluídos Total
        $servicosConcluidos = Servico::where('status', 'concluido')->count();

        // 4. Dados para o Gráfico (Últimos 6 meses)
        $months = collect([]);
        $userCounts = collect([]);
        $lucroCounts = collect([]);
        $receitaCounts = collect([]); // New
        $custoCounts = collect([]);   // New
        $createdServicesCounts = collect([]); // New
        $completedServicesCounts = collect([]); // New
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->locale('pt_BR')->isoFormat('MMMM');
            $year = $date->year;
            $month = $date->month;
            
            // Users
            $countUsers = User::whereYear('criado_em', $year)
                         ->whereMonth('criado_em', $month)
                         ->count();

            // Financeiro (Baseado em data de conclusão/update para status concluido)
            $financials = Servico::where('status', 'concluido')
                        ->whereYear('updated_at', $year)
                        ->whereMonth('updated_at', $month)
                        ->selectRaw('SUM(lucro_estimado) as lucro, SUM(valor_total) as receita, SUM(custo_interno) as custo')
                        ->first();
            
            // Adicionar Recorrência para o Mês do Loop
            $loopDateStart = Carbon::create($year, $month, 1)->startOfDay();
            $loopDateEnd = Carbon::create($year, $month, 1)->endOfMonth();

            // Lógica de Recorrência (SaaS):
            // Considera o valor se o serviço for recorrente E estiver ativo no período (data_servico <= fim_mes E (prazo_entrega >= inicio_mes OU prazo_entrega IS NULL))
            // Assumindo que status 'cancelado' invalida a receita, mas outros status (pendente/em_andamento/concluido) mantêm a cobrança ativa se estiver dentro do prazo.
            $recurrenceVal = Servico::where('recorrente', 1)
                ->where('status', '!=', 'cancelado') // Exclui apenas cancelados
                ->where('data_servico', '<=', $loopDateEnd)
                ->where(function($query) use ($loopDateStart) {
                    $query->where('prazo_entrega', '>=', $loopDateStart)
                          ->orWhereNull('prazo_entrega'); // Se prazo for null, assume contrato indeterminado
                })
                ->sum('valor_recorrencia');

            $finalLucro = ($financials->lucro ?? 0) + $recurrenceVal;
            $finalReceita = ($financials->receita ?? 0) + $recurrenceVal;

            // Serviços (Produtividade)
            $created = Servico::whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->count();
            
            $completed = Servico::where('status', 'concluido')
                        ->whereYear('updated_at', $year)
                        ->whereMonth('updated_at', $month)
                        ->count();
            
            $months->push(ucfirst($monthName));
            $userCounts->push($countUsers);
            $lucroCounts->push($finalLucro);
            $receitaCounts->push($finalReceita);
            $custoCounts->push($financials->custo ?? 0);
            $createdServicesCounts->push($created);
            $completedServicesCounts->push($completed);
        }

        // 5. Distribuição por Papel (Role)
        $roles = User::select('papel', DB::raw('count(*) as total'))
                     ->groupBy('papel')
                     ->pluck('total', 'papel');
        
        // 6. Ticket Médio (Serviços Concluídos)
        $totalRevenue = Servico::where('status', 'concluido')->sum('valor_total');
        $ticketMedio = $servicosConcluidos > 0 ? $totalRevenue / $servicosConcluidos : 0;

        // 7. Lucro por Quadrimestre (Últimos 4)
        $quadrimesters = collect([]);
        $dateIterator = now();

        for ($i = 0; $i < 4; $i++) {
            $currentMonth = $dateIterator->month;
            $currentYear = $dateIterator->year;
            
            if ($currentMonth >= 1 && $currentMonth <= 4) {
                $qStart = Carbon::create($currentYear, 1, 1)->startOfDay();
                $qEnd = Carbon::create($currentYear, 4, 30)->endOfDay();
                $qLabel = "1º Quad $currentYear";
                $nextDate = Carbon::create($currentYear - 1, 12, 31);
            } elseif ($currentMonth >= 5 && $currentMonth <= 8) {
                $qStart = Carbon::create($currentYear, 5, 1)->startOfDay();
                $qEnd = Carbon::create($currentYear, 8, 31)->endOfDay();
                $qLabel = "2º Quad $currentYear";
                $nextDate = Carbon::create($currentYear, 4, 30);
            } else {
                $qStart = Carbon::create($currentYear, 9, 1)->startOfDay();
                $qEnd = Carbon::create($currentYear, 12, 31)->endOfDay();
                $qLabel = "3º Quad $currentYear";
                $nextDate = Carbon::create($currentYear, 8, 31);
            }

            $lucroQuad = Servico::where('status', 'concluido')
                ->whereBetween('updated_at', [$qStart, $qEnd])
                ->sum('lucro_estimado');

            // Adicionar recorrência mês a mês dentro do quadrimestre
            $iterDate = $qStart->copy();
            while ($iterDate->lte($qEnd)) {
                $mStart = $iterDate->copy()->startOfMonth();
                $mEnd = $iterDate->copy()->endOfMonth();

                $recurrenceMonth = Servico::where('recorrente', 1)
                    ->where('status', '!=', 'cancelado')
                    ->where('data_servico', '<=', $mEnd)
                    ->where(function($query) use ($mStart) {
                        $query->where('prazo_entrega', '>=', $mStart)
                              ->orWhereNull('prazo_entrega');
                    })
                    ->sum('valor_recorrencia');
                
                $lucroQuad += $recurrenceMonth;
                $iterDate->addMonth();
            }

            $quadrimesters->push([
                'label' => $qLabel,
                'lucro' => $lucroQuad,
                'is_current' => $i === 0
            ]);

            $dateIterator = $nextDate;
        }

        return view('dashboard', compact(
            'totalUsers', 
            'activeUsers', 
            'inactiveUsers', 
            'newUsersMonth', 
            'growth',
            'months',
            'userCounts',
            'lucroCounts',
            'receitaCounts',
            'custoCounts',
            'createdServicesCounts',
            'completedServicesCounts',
            'roles',
            'news',
            'lucroRealizadoMes',
            'lucroPresumido',
            'servicosConcluidos',
            'ticketMedio',
            'quadrimesters'
        ));
    }
}
