<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComunicadoDestinatario extends Model
{
    protected $table = 'comunicado_destinatarios';

    public $timestamps = false;

    protected $fillable = [
        'comunicado_id',
        'estudiante_id',
        'correo',
        'estado_envio',
        'fecha_envio',
    ];

    public function comunicado()
    {
        return $this->belongsTo(
            Comunicado::class,
            'comunicado_id'
        );
    }

    public function estudiante()
    {
        return $this->belongsTo(
            Estudiante::class,
            'estudiante_id'
        );
    }
}
