<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comunicado extends Model
{
    protected $table = 'comunicados';

    public $timestamps = false;

    protected $fillable = [
        'bloque',
        'asunto',
        'cuerpo',
        'emisor',
        'destinatario',
        'score_riesgo',
        'estado',
        'fecha_creacion',
        'fecha_envio',
    ];

    public function destinatarios()
    {
        return $this->hasMany(
            ComunicadoDestinatario::class,
            'comunicado_id'
        );
    }
}
