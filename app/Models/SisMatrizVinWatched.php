<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SisMatrizVinWatched extends Model
{
    use HasFactory;

    protected $connection = 'sismatriz_main';
    protected $table = 'vin_watcheds';
    public $timestamps = false;

    protected $fillable = [
        'paroquia_id',
        // Add other fields if known
    ];
}
