<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricaController extends Controller
{
    private const ESCENARIOS = ['todos', 'sin_seguridad', 'con_seguridad'];

    private const NOMBRES_FLUJOS = [
        '01' => 'Clasificador de correos',
        '02' => 'Certificados',
        '03' => 'Matrícula',
        '04' => 'Comunicados',
    ];

    public function index(Request $request)
    {
        $nombresFlujos = self::NOMBRES_FLUJOS;
        $flujos = collect(array_keys($nombresFlujos));

        $escenario = $request->get('escenario', 'todos');
        if (!in_array($escenario, self::ESCENARIOS, true)) {
            $escenario = 'todos';
        }

        $flujo = $request->get('flujo', 'todos');
        if ($flujo !== 'todos') {
            $flujo = str_pad((string) $flujo, 2, '0', STR_PAD_LEFT);
            if (!isset($nombresFlujos[$flujo])) {
                $flujo = 'todos';
            }
        }

        /*
        | Cada tabla guarda el flujo de forma distinta:
        |
        | A) log_eventos y ejecuciones: 01-04 en ambos escenarios
        |    (con seguridad en comunicados también existe el 08).
        | B) clasificaciones_amenazas y disponibilidad_flujos:
        |    01-04 sin seguridad, 05-08 con seguridad (flujo + 4).
        | C) integracion_controles: 01-04 en ambos escenarios.
        */

        // A) 01-04 (+08 para comunicados con seguridad)
        $flujosA = null;
        if ($flujo !== 'todos') {
            $flujosA = [$flujo];
            if ($flujo === '04' && $escenario !== 'sin_seguridad') {
                $flujosA[] = '08';
            }
        }

        // B) 05-08 con seguridad
        $flujoB = $flujo;
        if ($flujo !== 'todos' && $escenario === 'con_seguridad') {
            $flujoB = str_pad((string) ((int) $flujo + 4), 2, '0', STR_PAD_LEFT);
        }

        $filtrarA = fn (Builder $q) => $q
            ->when($escenario !== 'todos', fn ($x) => $x->where('escenario', $escenario))
            ->when($flujosA, fn ($x) => $x->whereIn('flujo', $flujosA));

        $filtrarB = fn (Builder $q) => $q
            ->when($escenario !== 'todos', fn ($x) => $x->where('escenario', $escenario))
            ->when($flujoB !== 'todos', fn ($x) => $x->where('flujo', $flujoB));

        $filtrarC = fn (Builder $q) => $q
            ->when($escenario !== 'todos', fn ($x) => $x->where('escenario', $escenario))
            ->when($flujo !== 'todos', fn ($x) => $x->where('flujo', $flujo));

        // MTTD y MTTR (log_eventos)
        $mttd = $this->promedioMinutos($filtrarA(DB::table('log_eventos')), 'hora_ingreso', 'hora_deteccion');
        $mttr = $this->promedioMinutos($filtrarA(DB::table('log_eventos')), 'hora_deteccion', 'hora_resolucion');

        // Clasificación correcta
        $clasificacion = $this->porcentaje(
            $filtrarB(DB::table('clasificaciones_amenazas')),
            'clasificacion_correcta'
        );

        // Ejecuciones seguras
        $ejecucionesSeguras = $this->porcentaje(
            $filtrarA(DB::table('ejecuciones')),
            'ejecucion_segura'
        );

        // Integración de controles
        $integracion = $this->porcentaje(
            $filtrarC(DB::table('integracion_controles')),
            'implementado'
        );

        // Disponibilidad
        $totales = $filtrarB(DB::table('disponibilidad_flujos'))
            ->selectRaw('COALESCE(SUM(tiempo_programado_min),0) AS programado, COALESCE(SUM(tiempo_disponible_min),0) AS disponible')
            ->first();

        $disponibilidad = $totales->programado > 0
            ? ($totales->disponible / $totales->programado) * 100
            : null;

        $metricas = [
            'mttd'           => $mttd,
            'mttr'           => $mttr,
            'clasificacion'  => $clasificacion,
            'ejecuciones'    => $ejecucionesSeguras,
            'controles'      => $integracion,
            'disponibilidad' => $disponibilidad,
        ];

        $eventosPaginados = $filtrarA(DB::table('log_eventos'))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('metricas.index', compact(
            'metricas', 'escenario', 'flujo', 'flujos', 'nombresFlujos', 'eventosPaginados'
        ));
    }

    private function promedioMinutos(Builder $query, string $desde, string $hasta): ?float
    {
        $v = $query
            ->whereNotNull($desde)
            ->whereNotNull($hasta)
            ->selectRaw("AVG(TIMESTAMPDIFF(SECOND, {$desde}, {$hasta}) / 60) AS promedio")
            ->value('promedio');

        return $v === null ? null : (float) $v;
    }

    private function porcentaje(Builder $query, string $columnaOk): ?float
    {
        $fila = $query
            ->selectRaw("COUNT(*) AS total, COALESCE(SUM({$columnaOk} = 1), 0) AS ok")
            ->first();

        return $fila->total > 0 ? ($fila->ok / $fila->total) * 100 : null;
    }
}