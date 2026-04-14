<?php

namespace App\Http\Controllers;

use App\Models\AutomationAuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AutomationAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AutomationAuditLog::query();

        if ($request->filled('automation_key')) {
            $query->where('automation_key', $request->string('automation_key'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        if ($start) {
            $query->where('started_at', '>=', $start);
        }
        if ($end) {
            $query->where('started_at', '<=', $end);
        }

        $logs = $query->orderByDesc('started_at')->paginate(20)->withQueryString();

        $automationKeys = AutomationAuditLog::query()
            ->select('automation_key')
            ->whereNotNull('automation_key')
            ->groupBy('automation_key')
            ->orderBy('automation_key')
            ->pluck('automation_key');

        return view('automation_audits.index', [
            'logs' => $logs,
            'automationKeys' => $automationKeys,
            'selectedAutomationKey' => $request->input('automation_key'),
            'selectedStatus' => $request->input('status'),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
