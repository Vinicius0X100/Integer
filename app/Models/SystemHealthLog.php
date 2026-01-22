<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemHealthLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_name',
        'endpoint',
        'status',
        'response_time_ms',
        'status_code',
        'error_message',
    ];

    protected $casts = [
        'status' => 'boolean',
        'response_time_ms' => 'integer',
        'status_code' => 'integer',
    ];
}
