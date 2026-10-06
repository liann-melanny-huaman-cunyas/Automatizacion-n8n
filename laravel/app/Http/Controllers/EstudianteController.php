<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;

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
            ->get();

        return view('estudiantes.index', compact(
            'estudiantes',
            'buscar'
        ));
    }
}