<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $table = 'estudiantes';

    public $timestamps = false;

    protected $fillable = [
        'codigo_estudiante',
        'dni',
        'nombres_apellidos',
        'correo',
        'programa_academico',
        'estado',
    ];

    public function actividades()
    {
        return $this->hasMany(EstudianteActividad::class, 'estudiante_id');
    }

    public function comunicadoDestinatarios()
    {
        return $this->hasMany(ComunicadoDestinatario::class, 'estudiante_id');
    }
}