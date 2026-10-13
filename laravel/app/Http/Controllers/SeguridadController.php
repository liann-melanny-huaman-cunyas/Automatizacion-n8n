<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeguridadController extends Controller
{
    public function index(Request $request)
    {
        $escenario = $request->get('escenario', 'todos');

        if (!in_array($escenario, ['todos', 'sin_seguridad', 'con_seguridad'], true)) {
            $escenario = 'todos';
        }

        $eventos = DB::table('log_eventos')
            ->when($escenario !== 'todos', fn ($q) => $q->where('escenario', $escenario))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('seguridad.index', compact('eventos', 'escenario'));
    }
}