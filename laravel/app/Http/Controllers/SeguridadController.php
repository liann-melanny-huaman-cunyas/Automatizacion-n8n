<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeguridadController extends Controller
{
    public function index(Request $request)
    {
        $escenario = $request->get('escenario', 'todos');

        $query = DB::table('log_eventos');

        if ($escenario !== 'todos') {
            $query->where('escenario', $escenario);
        }

        $eventos = $query
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return view('seguridad.index', compact(
            'eventos',
            'escenario'
        ));
    }
}