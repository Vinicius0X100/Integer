<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NodalVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'nodal_organization_uuid',
        'organization_name',
        'document_type',
        'status',
        'document_url',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];
}
