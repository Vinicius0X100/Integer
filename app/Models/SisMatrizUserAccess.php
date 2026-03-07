<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SisMatrizUserAccess extends Model
{
    use HasFactory;

    protected $connection = 'sismatriz_main';
    protected $table = 'user_access';
    public $timestamps = false; // Assuming no created_at/updated_at based on user description

    protected $fillable = [
        'user_id',
        'access_date',
        'access_time',
        'device_type',
    ];

    protected $casts = [
        'access_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(SisMatrizMainUser::class, 'user_id');
    }
}
