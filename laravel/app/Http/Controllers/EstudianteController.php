<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EstudianteController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $estudiantes = Estudiante::query()
            ->when($buscar, function ($query, $buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombres_apellidos', 'like', "%{$buscar}%")
                      ->orWhere('codigo_estudiante', 'like', "%{$buscar}%")
                      ->orWhere('dni', 'like', "%{$buscar}%")
                      ->orWhere('correo', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('nombres_apellidos')
            ->paginate(10)
            ->onEachSide(1)
            ->withQueryString();

        return view('estudiantes.index', compact('estudiantes', 'buscar'));
    }

    public function store(Request $request)
    {
        Estudiante::create($this->validar($request));

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante registrado correctamente.');
    }

    public function update(Request $request, Estudiante $estudiante)
    {
        $estudiante->update($this->validar($request, $estudiante->id));

        return redirect()->back()
            ->with('success', 'Estudiante actualizado correctamente.');
    }

    public function destroy(Estudiante $estudiante)
    {
        $estudiante->delete();

        return redirect()->back()
            ->with('success', 'Estudiante eliminado correctamente.');
    }

    private function validar(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'codigo_estudiante'  => ['required', 'string', 'max:30',
                Rule::unique('estudiantes', 'codigo_estudiante')->ignore($id)],
            'nombres_apellidos'  => ['required', 'string', 'max:150'],
            'dni'                => ['required', 'digits:8',
                Rule::unique('estudiantes', 'dni')->ignore($id)],
            'correo'             => ['required', 'email', 'max:150',
                Rule::unique('estudiantes', 'correo')->ignore($id)],
            'programa_academico' => ['nullable', 'string', 'max:150'],
            'estado'             => ['required', Rule::in(['activo', 'inactivo'])],
        ]);
    }
}