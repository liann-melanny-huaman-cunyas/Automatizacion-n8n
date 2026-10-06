<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $estadisticas = [
            'estudiantes' => DB::table('estudiantes')
                ->where('estado', 'activo')
                ->count(),

            'matriculas' => DB::table('matriculas')->count(),

            'certificados_pendientes' => DB::table('certificados')
                ->where('estado', 'pendiente')
                ->count(),

            'comunicados' => DB::table('comunicados')->count(),

            'ejecuciones' => DB::table('ejecuciones')->count(),

            'incidentes' => DB::table('log_eventos')->count(),
        ];

        $ejecuciones = DB::table('ejecuciones')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('dashboard.index', compact(
            'estadisticas',
            'ejecuciones'
        ));
    }
}