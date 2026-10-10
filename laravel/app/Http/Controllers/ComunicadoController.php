<?php

namespace App\Http\Controllers;

use App\Models\Comunicado;
use App\Models\ComunicadoDestinatario;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComunicadoController extends Controller
{
    public function index()
    {
        $comunicados = Comunicado::with('destinatarios.estudiante')
            ->orderByDesc('id')
            ->get();

        return view('comunicados.index', compact('comunicados'));
    }

    public function create()
    {
        $estudiantes = Estudiante::where('estado', 'activo')
            ->orderBy('nombres_apellidos')
            ->get();

        return view('comunicados.create', compact('estudiantes'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'asunto' => ['required', 'string', 'max:255'],
            'cuerpo' => ['required', 'string'],
            'estudiantes' => ['required', 'array', 'min:1'],
            'estudiantes.*' => ['integer', 'exists:estudiantes,id'],
        ]);

        DB::transaction(function () use ($datos) {

            $comunicado = Comunicado::create([
                'bloque' => 'sin_seguridad',
                'asunto' => $datos['asunto'],
                'cuerpo' => $datos['cuerpo'],
                'emisor' => 'direccion@cefic.edu.pe',
                'destinatario' => null,
                'score_riesgo' => null,
                'estado' => 'pendiente',
                'fecha_creacion' => now(),
                'fecha_envio' => null,
            ]);

            $estudiantes = Estudiante::where('estado', 'activo')
                ->whereIn('id', $datos['estudiantes'])
                ->get();

            foreach ($estudiantes as $estudiante) {

                ComunicadoDestinatario::create([
                    'comunicado_id' => $comunicado->id,
                    'estudiante_id' => $estudiante->id,
                    'correo' => $estudiante->correo,
                    'estado_envio' => 'pendiente',
                    'fecha_envio' => null,
                ]);
            }
        });

        return redirect()
            ->route('comunicados.index')
            ->with('success', 'Comunicado registrado correctamente.');
    }
}