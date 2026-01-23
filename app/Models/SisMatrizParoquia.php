<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SisMatrizParoquia extends Model
{
    use HasFactory;

    protected $connection = 'sismatriz_main';
    protected $table = 'paroquias_superadmin';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'diocese',
        'region',
        'added_at',
        'status',
        'paroco',
        'foto',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];
}
