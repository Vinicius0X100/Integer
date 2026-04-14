<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicesAutomationsController extends Controller
{
    private function setSetting(string $key, string $value): void
    {
        $conn = DB::connection('integer');
        $table = $conn->table('settings');

        $exists = $table->where('key', $key)->exists();

        if ($exists) {
            $table->where('key', $key)->update([
                'value' => $value,
                'updated_at' => now(),
            ]);

            return;
        }

        $table->insert([
            'key' => $key,
            'value' => $value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function index()
    {
        $settings = DB::connection('integer')
            ->table('settings')
            ->whereIn('key', [
                'automation.sismatriz_auto_deactivate.enabled',
                'automation.sismatriz_auto_deactivate.max_inactive_days',
            ])
            ->pluck('value', 'key');

        $enabled = (bool) (int) ($settings['automation.sismatriz_auto_deactivate.enabled'] ?? 0);
        $maxInactiveDays = (int) ($settings['automation.sismatriz_auto_deactivate.max_inactive_days'] ?? 90);

        return view('services_automations.index', [
            'enabled' => $enabled,
            'maxInactiveDays' => $maxInactiveDays,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'in:1'],
            'max_inactive_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);

        $enabled = ($validated['enabled'] ?? null) === '1';

        if ($enabled && empty($validated['max_inactive_days'])) {
            return back()
                ->withInput()
                ->with('error', 'Informe o limite de dias para inativação automática.');
        }

        $existingMaxInactiveDays = (int) (DB::connection('integer')
            ->table('settings')
            ->where('key', 'automation.sismatriz_auto_deactivate.max_inactive_days')
            ->value('value') ?? 90);

        $maxInactiveDays = (int) ($validated['max_inactive_days'] ?? $existingMaxInactiveDays);

        $this->setSetting('automation.sismatriz_auto_deactivate.enabled', $enabled ? '1' : '0');
        $this->setSetting('automation.sismatriz_auto_deactivate.max_inactive_days', (string) $maxInactiveDays);

        return back()->with('success', 'Configurações atualizadas com sucesso!');
    }
}
