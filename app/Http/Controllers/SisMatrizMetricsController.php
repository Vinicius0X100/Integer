<?php

namespace App\Http\Controllers;

use App\Models\SisMatrizMainUser;
use App\Models\SisMatrizUserAccess;
use App\Models\SisMatrizParoquia;
use App\Models\SisMatrizRegister;
use App\Models\SisMatrizVinWatched;
use App\Models\SisMatrizBatismo;
use App\Http\Controllers\SisMatrizMainUserController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SisMatrizMetricsController extends Controller
{
    public function index(Request $request)
    {
        // Filters
        $paroquiaId = $request->input('paroquia_id');
        $roleId = $request->input('role');
        
        // Date Filter (Defaults to last 60 days if not provided)
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->subDays(60)->startOfDay();

        // --- 1. Quantitative Cards (KPIs) ---

        // Fixed KPI: Users Count (Defaults to Acolytes (8) if no role selected, or specific role if filtered)
        $usersQuery = SisMatrizMainUser::query();
        
        if ($paroquiaId) {
            $usersQuery->where('paroquia_id', $paroquiaId);
        }

        if ($roleId) {
            $usersQuery->whereRaw("FIND_IN_SET(?, rule)", [$roleId]);
        } else {
            // Default to Acolytes (8)
            $usersQuery->whereRaw("FIND_IN_SET('8', rule)");
        }
        
        $usersCount = $usersQuery->count();

        // Accesses (Filtered by Date Range)
        $accessQuery = SisMatrizUserAccess::query()
            ->whereBetween('access_date', [$startDate, $endDate]);

        // Filter accesses by Parish/Role (requires join)
        if ($paroquiaId || $roleId) {
            $accessQuery->whereHas('user', function($q) use ($paroquiaId, $roleId) {
                if ($paroquiaId) {
                    $q->where('paroquia_id', $paroquiaId);
                }
                if ($roleId) {
                    $q->whereRaw("FIND_IN_SET(?, rule)", [$roleId]);
                }
            });
        }

        $totalAccesses = $accessQuery->count();
        
        // --- New Metrics (Registers, Watcheds, Batismos) ---
        // Filter only by Paroquia as requested AND Date Range (assuming created_at exists)
        
        // Registers
        $registersQuery = SisMatrizRegister::query();
        if ($paroquiaId) {
            $registersQuery->where('paroquia_id', $paroquiaId);
        }
        $registersQuery->whereBetween('created_at', [$startDate, $endDate]);
        $registersCount = $registersQuery->count();

        // Apurações (VinWatcheds)
        $vinWatchedsQuery = SisMatrizVinWatched::query();
        if ($paroquiaId) {
            $vinWatchedsQuery->where('paroquia_id', $paroquiaId);
        }
        $vinWatchedsQuery->whereBetween('created_at', [$startDate, $endDate]);
        $vinWatchedsCount = $vinWatchedsQuery->count();

        // Batismos
        $batismosQuery = SisMatrizBatismo::query();
        if ($paroquiaId) {
            $batismosQuery->where('paroquia_id', $paroquiaId);
        }
        $batismosQuery->whereBetween('created_at', [$startDate, $endDate]);
        $batismosCount = $batismosQuery->count();


        // Breakdown by Device
        // Clone query for each device type to respect filters
        $webAccesses = (clone $accessQuery)->where('device_type', 1)->count();
        $androidAccesses = (clone $accessQuery)->where('device_type', 2)->count();
        $iosAccesses = (clone $accessQuery)->where('device_type', 3)->count();

        // Chart Data (Daily Evolution)
        $dailyAccesses = $accessQuery->select('access_date', 'device_type', DB::raw('count(*) as total'))
            ->groupBy('access_date', 'device_type')
            ->orderBy('access_date')
            ->get();

        // Prepare Chart Data
        $chartData = [];
        // Generate all dates in range
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        $dates = [];
        foreach ($period as $date) {
            $dates[$date->format('Y-m-d')] = $date->format('d/m');
        }

        $dataWeb = array_fill_keys(array_keys($dates), 0);
        $dataAndroid = array_fill_keys(array_keys($dates), 0);
        $dataIOS = array_fill_keys(array_keys($dates), 0);

        foreach ($dailyAccesses as $access) {
            $dateKey = $access->access_date->format('Y-m-d'); // Access date is already cast to date
            // Handle string date if cast failed
            if (is_string($access->access_date)) {
                 $dateKey = substr($access->access_date, 0, 10);
            }
            
            if (isset($dataWeb[$dateKey])) {
                if ($access->device_type == 1) $dataWeb[$dateKey] += $access->total;
                if ($access->device_type == 2) $dataAndroid[$dateKey] += $access->total;
                if ($access->device_type == 3) $dataIOS[$dateKey] += $access->total;
            }
        }

        $paroquias = SisMatrizParoquia::orderBy('name')->get();
        $roles = SisMatrizMainUserController::ROLES;

        return view('sismatriz_main.metrics', [
            'usersCount' => $usersCount,
            'totalAccesses' => $totalAccesses,
            'webAccesses' => $webAccesses,
            'androidAccesses' => $androidAccesses,
            'iosAccesses' => $iosAccesses,
            'registersCount' => $registersCount,
            'vinWatchedsCount' => $vinWatchedsCount,
            'batismosCount' => $batismosCount,
            'chartLabels' => array_values($dates),
            'chartDataWeb' => array_values($dataWeb),
            'chartDataAndroid' => array_values($dataAndroid),
            'chartDataIOS' => array_values($dataIOS),
            'paroquias' => $paroquias,
            'roles' => $roles,
            'selectedParoquia' => $paroquiaId,
            'selectedRole' => $roleId,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }
}
