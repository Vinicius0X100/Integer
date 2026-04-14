<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationAuditLog extends Model
{
    use HasFactory;

    protected $connection = 'integer';

    protected $table = 'automation_audit_logs';

    protected $fillable = [
        'automation_key',
        'automation_name',
        'status',
        'started_at',
        'finished_at',
        'duration_ms',
        'summary',
        'details',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_ms' => 'integer',
        'summary' => 'array',
        'details' => 'array',
    ];
}
