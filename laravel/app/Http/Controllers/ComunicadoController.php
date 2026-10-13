<?php

namespace App\Http\Controllers;

use App\Mail\ComunicadoMail;
use App\Models\Comunicado;
use App\Models\ComunicadoDestinatario;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $datos = $this->validar($request);

        $comunicado = DB::transaction(function () use ($datos) {
            $comunicado = Comunicado::create([
                'bloque'         => 'con_seguridad',
                'asunto'         => $datos['asunto'],
                'cuerpo'         => $datos['cuerpo'],
                'emisor'         => 'direccion@cefic.edu.pe',
                'destinatario'   => null,
                'score_riesgo'   => null,
                'estado'         => 'pendiente',
                'fecha_creacion' => now(),
                'fecha_envio'    => null,
            ]);

            $this->crearDestinatarios($comunicado, $datos['estudiantes']);

            return $comunicado;
        });

        [$tipo, $mensaje] = $this->analizarConN8n($comunicado);

        return redirect()->route('comunicados.index')->with($tipo, $mensaje);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'asunto'        => ['required', 'string', 'max:255'],
            'cuerpo'        => ['required', 'string'],
            'estudiantes'   => ['required', 'array', 'min:1'],
            'estudiantes.*' => ['integer', 'exists:estudiantes,id'],
        ]);
    }

    private function crearDestinatarios(Comunicado $comunicado, array $ids): void
    {
        $estudiantes = Estudiante::where('estado', 'activo')
            ->whereIn('id', $ids)
            ->get();

        foreach ($estudiantes as $estudiante) {
            ComunicadoDestinatario::create([
                'comunicado_id' => $comunicado->id,
                'estudiante_id' => $estudiante->id,
                'correo'        => $estudiante->correo,
                'estado_envio'  => 'pendiente',
                'fecha_envio'   => null,
            ]);
        }
    }

    private function analizarConN8n(Comunicado $comunicado): array
    {
        try {
            $respuesta = Http::timeout(40)
                ->acceptJson()
                ->withHeaders(['Authorization' => config('services.n8n.api_key')])
                ->post(config('services.n8n.webhook_url'), [
                    'comunicado_id' => $comunicado->id,
                    'asunto'        => $comunicado->asunto,
                    'cuerpo'        => $comunicado->cuerpo,
                    'emisor'        => $comunicado->emisor,
                ])
                ->throw()
                ->json();

            $estado = $respuesta['estado'] ?? 'pausado';
            if (!in_array($estado, ['enviado', 'pausado', 'cancelado'], true)) {
                $estado = 'pausado';
            }

            DB::transaction(function () use ($comunicado, $estado, $respuesta) {
                $comunicado->update([
                    'estado'       => $estado,
                    'score_riesgo' => $respuesta['score'] ?? null,
                    'fecha_envio'  => $estado === 'enviado' ? now() : null,
                ]);

                if ($estado === 'enviado') {
                    $comunicado->destinatarios()->update([
                        'estado_envio' => 'enviado',
                        'fecha_envio'  => now(),
                    ]);

                    foreach ($comunicado->destinatarios as $dest) {
                        Mail::to($dest->correo)->send(new ComunicadoMail($comunicado));
                    }
                } elseif ($estado === 'cancelado') {
                    $comunicado->destinatarios()->update([
                        'estado_envio' => 'cancelado',
                    ]);
                }
            });

            return match ($estado) {
                'enviado'   => ['success', 'Comunicado aprobado y enviado.'],
                'pausado'   => ['warning', 'Comunicado pausado para revisión (riesgo medio).'],
                'cancelado' => ['error',   'Comunicado cancelado automáticamente (riesgo alto).'],
            };
        } catch (\Throwable $e) {
            Log::error('Fallo análisis de seguridad n8n: ' . $e->getMessage(), [
                'comunicado_id' => $comunicado->id,
            ]);

            $comunicado->update(['estado' => 'pausado']);

            return ['warning', 'No se pudo validar la seguridad; el comunicado quedó pausado.'];
        }
    }
}