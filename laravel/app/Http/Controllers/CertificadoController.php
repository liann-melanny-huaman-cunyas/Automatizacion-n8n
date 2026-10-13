<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CertificadoController extends Controller
{
    private const ESTADOS = ['pendiente', 'aprobado', 'rechazado', 'entregado'];

    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $certificados = Certificado::query()
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombres_apellidos', 'like', "%{$buscar}%")
                      ->orWhere('dni', 'like', "%{$buscar}%")
                      ->orWhere('remitente_email', 'like', "%{$buscar}%")
                      ->orWhere('nombre_actividad', 'like', "%{$buscar}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('certificados.index', compact('certificados', 'buscar'));
    }

    public function store(Request $request)
    {
        Certificado::create($this->validar($request));

        return redirect()->route('certificados.index')
            ->with('success', 'Certificado registrado correctamente.');
    }

    public function update(Request $request, Certificado $certificado)
    {
        $certificado->update($this->validar($request));

        return redirect()->back()
            ->with('success', 'Certificado actualizado correctamente.');
    }

    public function destroy(Certificado $certificado)
    {
        $certificado->delete();

        return redirect()->back()
            ->with('success', 'Certificado eliminado correctamente.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombres_apellidos' => ['required', 'string', 'max:150'],
            'dni'               => ['required', 'digits:8'],
            'remitente_email'   => ['required', 'email', 'max:150'],
            'nombre_actividad'  => ['required', 'string', 'max:200'],
            'tipo_certificado'  => ['required', 'string', 'max:100'],
            'estado'            => ['required', Rule::in(self::ESTADOS)],
            'score_riesgo'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bloque'            => ['nullable', 'string', 'max:50'],
        ]);
    }
}