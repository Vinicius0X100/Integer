<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SisMatrizBatismo extends Model
{
    use HasFactory;

    protected $connection = 'sismatriz_main';
    protected $table = 'batismos';
    public $timestamps = false;

    protected $fillable = [
        'paroquia_id',
        // Add other fields if known
    ];
}
