<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use Illuminate\Http\Request;

class MatriculaController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $matriculas = Matricula::query()
            ->when($buscar, function ($query) use ($buscar) {
                $query->where('codigo_estudiante', 'like', "%{$buscar}%")
                    ->orWhere('nombre_estudiante', 'like', "%{$buscar}%")
                    ->orWhere('programa_academico', 'like', "%{$buscar}%")
                    ->orWhere('curso', 'like', "%{$buscar}%");
            })
            ->orderByDesc('fecha_registro')
            ->paginate(15)
            ->withQueryString();

        return view('matriculas.index', compact('matriculas', 'buscar'));
    }
}