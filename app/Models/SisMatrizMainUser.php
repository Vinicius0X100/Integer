<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SisMatrizMainUser extends Model
{
    use HasFactory;

    protected $connection = 'sismatriz_main';
    protected $table = 'users';

    protected $fillable = [
        'name',
        'user',
        'email',
        'password',
        'rule',
        'status',
        'is_pass_change',
        'login_attempts',
        'last_attempt',
        'avatar',
        'accepted_photo',
        'paroquia_id',
        'timezone',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'last_attempt' => 'datetime',
    ];

    public function paroquia()
    {
        return $this->belongsTo(SisMatrizParoquia::class, 'paroquia_id');
    }
}
