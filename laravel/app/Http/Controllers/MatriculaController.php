<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MatriculaController extends Controller
{
    private const ESTADOS = ['matriculado', 'retirado', 'finalizado', 'pendiente'];

    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $matriculas = Matricula::query()
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('codigo_estudiante', 'like', "%{$buscar}%")
                      ->orWhere('nombre_estudiante', 'like', "%{$buscar}%")
                      ->orWhere('programa_academico', 'like', "%{$buscar}%")
                      ->orWhere('curso', 'like', "%{$buscar}%");
                });
            })
            ->orderByDesc('fecha_registro')
            ->paginate(15)
            ->withQueryString();

        return view('matriculas.index', compact('matriculas', 'buscar'));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $datos['fecha_registro'] = $datos['fecha_registro'] ?? now()->toDateString();

        Matricula::create($datos);

        return redirect()->route('matriculas.index')
            ->with('success', 'Matrícula registrada correctamente.');
    }

    public function update(Request $request, Matricula $matricula)
    {
        $matricula->update($this->validar($request));

        return redirect()->back()
            ->with('success', 'Matrícula actualizada correctamente.');
    }

    public function destroy(Matricula $matricula)
    {
        $matricula->delete();

        return redirect()->back()
            ->with('success', 'Matrícula eliminada correctamente.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre_estudiante'  => ['required', 'string', 'max:150'],
            'codigo_estudiante'  => ['required', 'string', 'max:30'],
            'programa_academico' => ['required', 'string', 'max:150'],
            'periodo_academico'  => ['required', 'string', 'max:30'],
            'curso'              => ['required', 'string', 'max:150'],
            'estado_matricula'   => ['required', Rule::in(self::ESTADOS)],
            'bloque'             => ['nullable', 'string', 'max:50'],
            'score_riesgo'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fecha_registro'     => ['nullable', 'date'],
        ]);
    }
}