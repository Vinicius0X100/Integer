<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SisMatrizRegister extends Model
{
    use HasFactory;

    protected $connection = 'sismatriz_main';
    protected $table = 'registers';
    public $timestamps = false;

    protected $fillable = [
        'paroquia_id',
        // Add other fields if known
    ];
}
