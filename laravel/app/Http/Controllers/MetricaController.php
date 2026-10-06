<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricaController extends Controller
{
    public function index(Request $request)
    {
        $escenario = $request->get('escenario', 'todos');
        $flujo = $request->get('flujo', 'todos');

        $aplicar = function ($query, $alias = null) use ($escenario, $flujo) {

            $p = $alias ? $alias . '.' : '';

            if ($escenario !== 'todos') {
                $query->where($p . 'escenario', $escenario);
            }

            if ($flujo !== 'todos') {
                $query->where($p . 'flujo', $flujo);
            }

            return $query;
        };

        /*
        |--------------------------------------------------------------------------
        | MTTD
        |--------------------------------------------------------------------------
        */

        $mttdQuery = DB::table('log_eventos')
            ->whereNotNull('hora_ingreso')
            ->whereNotNull('hora_deteccion');

        $aplicar($mttdQuery);

        $mttd = (clone $mttdQuery)
            ->selectRaw("
                AVG(
                    TIMESTAMPDIFF(
                        SECOND,
                        hora_ingreso,
                        hora_deteccion
                    ) / 60
                ) AS promedio
            ")
            ->value('promedio') ?? 0;

        /*
        |--------------------------------------------------------------------------
        | MTTR
        |--------------------------------------------------------------------------
        */

        $mttrQuery = DB::table('log_eventos')
            ->whereNotNull('hora_deteccion')
            ->whereNotNull('hora_resolucion');

        $aplicar($mttrQuery);

        $mttr = (clone $mttrQuery)
            ->selectRaw("
                AVG(
                    TIMESTAMPDIFF(
                        SECOND,
                        hora_deteccion,
                        hora_resolucion
                    ) / 60
                ) AS promedio
            ")
            ->value('promedio') ?? 0;

        /*
        |--------------------------------------------------------------------------
        | CLASIFICACIÓN CORRECTA
        |--------------------------------------------------------------------------
        */

        $clasificacionQuery =
            DB::table('clasificaciones_amenazas');

        $aplicar($clasificacionQuery);

        $totalClasificacion =
            (clone $clasificacionQuery)->count();

        $correctas =
            (clone $clasificacionQuery)
                ->where('clasificacion_correcta', 1)
                ->count();

        $clasificacion = $totalClasificacion > 0
            ? ($correctas / $totalClasificacion) * 100
            : 0;

        /*
        |--------------------------------------------------------------------------
        | EJECUCIONES SEGURAS
        |--------------------------------------------------------------------------
        */

        $ejecucionesQuery = DB::table('ejecuciones');

        $aplicar($ejecucionesQuery);

        $totalEjecuciones =
            (clone $ejecucionesQuery)->count();

        $seguras =
            (clone $ejecucionesQuery)
                ->where('ejecucion_segura', 1)
                ->count();

        $ejecucionesSeguras = $totalEjecuciones > 0
            ? ($seguras / $totalEjecuciones) * 100
            : 0;

        /*
        |--------------------------------------------------------------------------
        | CONTROLES
        |--------------------------------------------------------------------------
        */

        $controlesQuery =
            DB::table('integracion_controles as i');

        $aplicar($controlesQuery, 'i');

        $totalControles =
            (clone $controlesQuery)->count();

        $implementados =
            (clone $controlesQuery)
                ->where('i.implementado', 1)
                ->count();

        $integracion = $totalControles > 0
            ? ($implementados / $totalControles) * 100
            : 0;

        /*
        |--------------------------------------------------------------------------
        | DISPONIBILIDAD
        |--------------------------------------------------------------------------
        */

        $disponibilidadQuery =
            DB::table('disponibilidad_flujos');

        $aplicar($disponibilidadQuery);

        $programado =
            (clone $disponibilidadQuery)
                ->sum('tiempo_programado_min');

        $disponible =
            (clone $disponibilidadQuery)
                ->sum('tiempo_disponible_min');

        $disponibilidad = $programado > 0
            ? ($disponible / $programado) * 100
            : 0;

        /*
        |--------------------------------------------------------------------------
        | FLUJOS
        |--------------------------------------------------------------------------
        */

        $flujos = DB::table('ejecuciones')
            ->whereNotNull('flujo')
            ->distinct()
            ->orderBy('flujo')
            ->pluck('flujo');

        $metricas = [
            'mttd' => $mttd,
            'mttr' => $mttr,
            'clasificacion' => $clasificacion,
            'ejecuciones' => $ejecucionesSeguras,
            'controles' => $integracion,
            'disponibilidad' => $disponibilidad,
        ];

        return view('metricas.index', compact(
            'metricas',
            'escenario',
            'flujo',
            'flujos'
        ));
    }
}