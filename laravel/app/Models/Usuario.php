<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'correo',
        'password',
        'rol',
        'estado',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
    ];
}