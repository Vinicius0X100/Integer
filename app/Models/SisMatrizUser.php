<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SisMatrizUser extends Model
{
    use HasFactory;

    protected $connection = 'sismatriz_ticket';
    protected $table = 'users';

    protected $fillable = [
        'sacratech_id',
        'name',
        'email',
        'role',
        'password',
        'email_verified_at',
        'welcome_email_sent',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
