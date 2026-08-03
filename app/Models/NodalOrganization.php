<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NodalOrganization extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'slug',
        'nodal_organization_id',
        'nodal_user_id',
        'owner_name',
        'owner_email',
        'nodal_login_url',
        'status',
        'provisioning_error',
        'provisionado_em',
    ];

    protected $casts = [
        'provisionado_em' => 'datetime',
    ];
}
