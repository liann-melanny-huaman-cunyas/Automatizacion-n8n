<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $estadisticas = [
            'estudiantes'             => DB::table('estudiantes')->where('estado', 'activo')->count(),
            'matriculas'              => DB::table('matriculas')->count(),
            'certificados_pendientes' => DB::table('certificados')->where('estado', 'pendiente')->count(),
            'comunicados'             => DB::table('comunicados')->count(),
            'ejecuciones'             => DB::table('ejecuciones')->count(),
            'incidentes'              => DB::table('log_eventos')->count(),
        ];

        // Eventos por nivel de riesgo
        $porNivel = DB::table('log_eventos')
            ->select('nivel', DB::raw('COUNT(*) as total'))
            ->groupBy('nivel')
            ->pluck('total', 'nivel');

        // Comparación con / sin seguridad
        $porEscenario = DB::table('log_eventos')
            ->select(
                'escenario',
                DB::raw('COUNT(*) as total'),
                DB::raw('COALESCE(SUM(incidente_resuelto = 1),0) as resueltos'),
                DB::raw('COALESCE(AVG(TIMESTAMPDIFF(MINUTE, hora_ingreso, hora_deteccion)),0) as mttd')
            )
            ->groupBy('escenario')
            ->get()
            ->keyBy('escenario');

        // Comunicados por estado
        $comunicadosEstado = DB::table('comunicados')
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        // Ejecuciones: correctas vs con error
        $ejecEstado = DB::table('ejecuciones')
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $ejecuciones = DB::table('ejecuciones')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('dashboard.index', compact(
            'estadisticas', 'porNivel', 'porEscenario',
            'comunicadosEstado', 'ejecEstado', 'ejecuciones'
        ));
    }
}