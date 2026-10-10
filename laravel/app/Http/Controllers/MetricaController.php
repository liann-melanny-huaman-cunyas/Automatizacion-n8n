<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricaController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Filtros recibidos
        |--------------------------------------------------------------------------
        */

        $escenario = $request->get('escenario', 'todos');
        $flujo = $request->get('flujo', 'todos');


        /*
        |--------------------------------------------------------------------------
        | Mapeo de procesos
        |--------------------------------------------------------------------------
        |
        | El usuario selecciona:
        |
        | 01 = Clasificador de correos
        | 02 = Certificados
        | 03 = Matrícula
        | 04 = Comunicados
        |
        | En las tablas principales:
        |
        | Sin seguridad:
        | 01, 02, 03, 04
        |
        | Con seguridad:
        | 05, 06, 07, 08
        |
        */

        $flujos = collect([
            '01',
            '02',
            '03',
            '04',
        ]);

        $nombresFlujos = [
            '01' => 'Clasificador de correos',
            '02' => 'Certificados',
            '03' => 'Matrícula',
            '04' => 'Comunicados',
        ];


        /*
        |--------------------------------------------------------------------------
        | Determinar flujo real
        |--------------------------------------------------------------------------
        |
        | Para las tablas principales:
        |
        | 01 + sin_seguridad = 01
        | 01 + con_seguridad = 05
        |
        | 02 + sin_seguridad = 02
        | 02 + con_seguridad = 06
        |
        | 03 + sin_seguridad = 03
        | 03 + con_seguridad = 07
        |
        | 04 + sin_seguridad = 04
        | 04 + con_seguridad = 08
        |
        */

        $flujoReal = $flujo;

        if ($flujo !== 'todos' && $escenario === 'con_seguridad') {

            $flujoReal = (string) ((int) $flujo + 4);

            $flujoReal = str_pad(
                $flujoReal,
                2,
                '0',
                STR_PAD_LEFT
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Función para aplicar filtros
        |--------------------------------------------------------------------------
        |
        | Esta función se utiliza para:
        | - log_eventos
        | - clasificaciones_amenazas
        | - ejecuciones
        | - disponibilidad_flujos
        |
        */

        $aplicar = function ($query, $alias = null) use (
            $escenario,
            $flujoReal
        ) {

            $p = $alias ? $alias . '.' : '';

            if ($escenario !== 'todos') {
                $query->where(
                    $p . 'escenario',
                    $escenario
                );
            }

            if ($flujoReal !== 'todos') {
                $query->where(
                    $p . 'flujo',
                    $flujoReal
                );
            }

            return $query;
        };


        /*
        |--------------------------------------------------------------------------
        | MTTD - Tiempo Medio de Detección
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
        | MTTR - Tiempo Medio de Respuesta
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
        | Clasificación correcta
        |--------------------------------------------------------------------------
        */

        $clasificacionQuery = DB::table(
            'clasificaciones_amenazas'
        );

        $aplicar($clasificacionQuery);

        $totalClasificacion = (clone $clasificacionQuery)
            ->count();

        $correctas = (clone $clasificacionQuery)
            ->where(
                'clasificacion_correcta',
                1
            )
            ->count();

        $clasificacion = $totalClasificacion > 0
            ? ($correctas / $totalClasificacion) * 100
            : 0;


        /*
        |--------------------------------------------------------------------------
        | Ejecuciones seguras
        |--------------------------------------------------------------------------
        */

        $ejecucionesQuery = DB::table(
            'ejecuciones'
        );

        $aplicar($ejecucionesQuery);

        $totalEjecuciones = (clone $ejecucionesQuery)
            ->count();

        $seguras = (clone $ejecucionesQuery)
            ->where(
                'ejecucion_segura',
                1
            )
            ->count();

        $ejecucionesSeguras = $totalEjecuciones > 0
            ? ($seguras / $totalEjecuciones) * 100
            : 0;


        /*
        |--------------------------------------------------------------------------
        | Integración de controles
        |--------------------------------------------------------------------------
        |
        | IMPORTANTE:
        |
        | Esta tabla NO utiliza 05-08.
        |
        | Utiliza:
        |
        | 01 + sin_seguridad
        | 01 + con_seguridad
        | 02 + sin_seguridad
        | 02 + con_seguridad
        | etc.
        |
        | Por eso aquí utilizamos $flujo y NO $flujoReal.
        |
        */

        $controlesQuery = DB::table(
            'integracion_controles'
        );

        if ($escenario !== 'todos') {
            $controlesQuery->where(
                'escenario',
                $escenario
            );
        }

        if ($flujo !== 'todos') {
            $controlesQuery->where(
                'flujo',
                $flujo
            );
        }

        $totalControles = (clone $controlesQuery)
            ->count();

        $implementados = (clone $controlesQuery)
            ->where(
                'implementado',
                1
            )
            ->count();

        $integracion = $totalControles > 0
            ? ($implementados / $totalControles) * 100
            : 0;


        /*
        |--------------------------------------------------------------------------
        | Disponibilidad
        |--------------------------------------------------------------------------
        */

        $disponibilidadQuery = DB::table(
            'disponibilidad_flujos'
        );

        $aplicar($disponibilidadQuery);

        $programado = (clone $disponibilidadQuery)
            ->sum(
                'tiempo_programado_min'
            );

        $disponible = (clone $disponibilidadQuery)
            ->sum(
                'tiempo_disponible_min'
            );

        $disponibilidad = $programado > 0
            ? ($disponible / $programado) * 100
            : 0;


        /*
        |--------------------------------------------------------------------------
        | Métricas
        |--------------------------------------------------------------------------
        */

        $metricas = [

            'mttd' => $mttd,

            'mttr' => $mttr,

            'clasificacion' => $clasificacion,

            'ejecuciones' => $ejecucionesSeguras,

            'controles' => $integracion,

            'disponibilidad' => $disponibilidad,

        ];


        /*
        |--------------------------------------------------------------------------
        | Vista
        |--------------------------------------------------------------------------
        */

        return view(
            'metricas.index',
            compact(
                'metricas',
                'escenario',
                'flujo',
                'flujos',
                'nombresFlujos'
            )
        );
    }
}