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
        'paroco_foto',
        'paroco_mensagem',
        'paroco_email',
        'paroco_ordenacao',
        'paroco_aniversario',
        'facebook',
        'instagram',
        'twitter',
        'youtube',
        'slug',
    ];

    protected $casts = [
        'added_at' => 'datetime',
        'paroco_ordenacao' => 'date',
        'paroco_aniversario' => 'date',
    ];
}
