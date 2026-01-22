<?php

namespace App\Http\Controllers;

use App\Models\SystemHealthLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SystemHealthController extends Controller
{
    public function index()
    {
        $systems = ['SisMatriz', 'SisMatriz Ticket', 'Sacratech Cloud'];
        $currentStatus = [];

        foreach ($systems as $system) {
            $latest = SystemHealthLog::where('system_name', $system)->latest()->first();
            
            // Calculate uptime percentage for last 24h
            $totalChecks24h = SystemHealthLog::where('system_name', $system)
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->count();
            
            $onlineChecks24h = SystemHealthLog::where('system_name', $system)
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->where('status', true)
                ->count();
                
            $uptime24h = $totalChecks24h > 0 ? round(($onlineChecks24h / $totalChecks24h) * 100, 2) : 100;

            $currentStatus[$system] = [
                'log' => $latest,
                'uptime_24h' => $uptime24h
            ];
        }

        $logs = SystemHealthLog::latest()->paginate(20);

        return view('system_health.index', compact('currentStatus', 'logs'));
    }

    public function generatePdf()
    {
        $logs = SystemHealthLog::latest()->limit(500)->get();
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('system_health.pdf', compact('logs'));
        return $pdf->download('monitoramento_sistema_' . date('Y-m-d_H-i') . '.pdf');
    }

    public function metrics(Request $request)
    {
        $range = $request->get('range', 'day'); // day, week, month
        $now = Carbon::now();
        $startDate = $now->copy()->startOfDay();

        if ($range === 'week') {
            $startDate = $now->copy()->subDays(7)->startOfDay();
        } elseif ($range === 'month') {
            $startDate = $now->copy()->subDays(30)->startOfDay();
        }

        $logs = SystemHealthLog::where('created_at', '>=', $startDate)
            ->orderBy('created_at')
            ->get()
            ->groupBy('system_name');

        $data = [];
        
        // Ensure all systems are present even if no logs
        $systems = ['SisMatriz', 'SisMatriz Ticket', 'Sacratech Cloud'];
        
        foreach ($systems as $system) {
            $entries = $logs->get($system, collect());
            
            $data[$system] = $entries->map(function ($entry) {
                return [
                    'time' => $entry->created_at->format('Y-m-d H:i'),
                    'response_time' => $entry->response_time_ms,
                    'status' => $entry->status,
                ];
            });
        }

        return response()->json($data);
    }
}
