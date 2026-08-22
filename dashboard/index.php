<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/db.php';


/*
|--------------------------------------------------------------------------
| CEFIC - DASHBOARD DE INSTRUMENTOS
|--------------------------------------------------------------------------
| Instrumentos:
| 1. Integración de controles de seguridad
| 2. MTTR
| 3. Clasificación de amenazas
| 4. Ejecuciones seguras
| 5. MTTD
| 6. Disponibilidad
|--------------------------------------------------------------------------
*/

$instrumento = $_GET['instrumento'] ?? 'controles';

$escenario = $_GET['escenario'] ?? 'todos';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$flujo = $_GET['flujo'] ?? 'todos';

$instrumentos = [
    'controles' => 'Integración de controles',
    'mttr' => 'MTTR',
    'clasificacion' => 'Clasificación de amenazas',
    'ejecuciones' => 'Ejecuciones seguras',
    'mttd' => 'MTTD',
    'disponibilidad' => 'Disponibilidad'
];

if (!array_key_exists($instrumento, $instrumentos)) {
    $instrumento = 'controles';
}

/*
|--------------------------------------------------------------------------
| Funciones auxiliares
|--------------------------------------------------------------------------
*/

function e($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function escenarioTexto($escenario)
{
    if ($escenario === 'sin_seguridad') {
        return 'Sin seguridad';
    }

    if ($escenario === 'con_seguridad') {
        return 'Con seguridad';
    }

    return 'Todos';
}

function boolTexto($valor)
{
    return ((int)$valor === 1) ? 'Sí' : 'No';
}

function filtrosSQL(&$params, &$types)
{
    global $escenario, $fecha_inicio, $fecha_fin, $flujo;

    $where = [];

    if ($escenario !== 'todos') {
        $where[] = "escenario = ?";
        $params[] = $escenario;
        $types .= 's';
    }

    if ($flujo !== 'todos' && $flujo !== '') {
        $where[] = "flujo = ?";
        $params[] = $flujo;
        $types .= 's';
    }

    return $where;
}

/*
|--------------------------------------------------------------------------
| Obtener flujos disponibles
|--------------------------------------------------------------------------
*/

$flujos = [];

$stmt = $pdo->query("
    SELECT DISTINCT flujo
    FROM ejecuciones
    WHERE flujo IS NOT NULL
    ORDER BY flujo
");

$flujos = $stmt->fetchAll(PDO::FETCH_COLUMN);

/*
|--------------------------------------------------------------------------
| Datos principales
|--------------------------------------------------------------------------
*/

$datos = [];
$totales = [];

switch ($instrumento) {

    /*
    |--------------------------------------------------------------------------
    | 1. INTEGRACIÓN DE CONTROLES
    |--------------------------------------------------------------------------
    */

    case 'controles':

        $params = [];
        $types = '';

        $where = [];

        if ($escenario !== 'todos') {
            $where[] = "i.escenario = ?";
            $params[] = $escenario;
            $types .= 's';
        }

        if ($flujo !== 'todos' && $flujo !== '') {
            $where[] = "i.flujo = ?";
            $params[] = $flujo;
            $types .= 's';
        }

        $sql = "
            SELECT
                i.flujo,
                i.proceso,
                i.escenario,
                i.control_id,
                c.numero_control,
                c.nombre_control,
                i.implementado,
                i.evidencia_tecnica,
                i.observaciones,
                i.fecha_evaluacion
            FROM integracion_controles i
            INNER JOIN controles_seguridad c
                ON c.id = i.control_id
        ";

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= "
            ORDER BY
                i.flujo,
                i.escenario,
                c.numero_control
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = count($datos);
        $implementados = 0;

        foreach ($datos as $fila) {
            if ((int)$fila['implementado'] === 1) {
                $implementados++;
            }
        }

        $porcentaje = $total > 0
            ? ($implementados / $total) * 100
            : 0;

        $totales = [
            'total' => $total,
            'implementados' => $implementados,
            'porcentaje' => $porcentaje
        ];

        break;


    /*
    |--------------------------------------------------------------------------
    | 2. MTTR
    |--------------------------------------------------------------------------
    */

    case 'mttr':

        $params = [];
        $where = [];

        if ($escenario !== 'todos') {
            $where[] = "escenario = ?";
            $params[] = $escenario;
        }

        if ($flujo !== 'todos' && $flujo !== '') {
            $where[] = "flujo = ?";
            $params[] = $flujo;
        }

        if ($fecha_inicio !== '') {
            $where[] = "DATE(hora_deteccion) >= ?";
            $params[] = $fecha_inicio;
        }

        if ($fecha_fin !== '') {
            $where[] = "DATE(hora_deteccion) <= ?";
            $params[] = $fecha_fin;
        }

        $sql = "
            SELECT
                id,
                flujo,
                proceso,
                escenario,
                id_incidente,
                hora_deteccion,
                hora_resolucion,
                categoria,
                accion_ejecutada,
                CASE
                    WHEN hora_deteccion IS NOT NULL
                    AND hora_resolucion IS NOT NULL
                    THEN TIMESTAMPDIFF(
                        SECOND,
                        hora_deteccion,
                        hora_resolucion
                    ) / 60
                    ELSE NULL
                END AS tiempo_respuesta
            FROM log_eventos
        ";

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY hora_deteccion ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $suma = 0;
        $resueltos = 0;

        foreach ($datos as $fila) {
            if ($fila['tiempo_respuesta'] !== null) {
                $suma += (float)$fila['tiempo_respuesta'];
                $resueltos++;
            }
        }

        $mttr = $resueltos > 0
            ? $suma / $resueltos
            : 0;

        $totales = [
            'total' => count($datos),
            'suma' => $suma,
            'mttr' => $mttr
        ];

        break;


    /*
    |--------------------------------------------------------------------------
    | 3. CLASIFICACIÓN DE AMENAZAS
    |--------------------------------------------------------------------------
    */

    case 'clasificacion':

        $params = [];
        $where = [];

        if ($escenario !== 'todos') {
            $where[] = "escenario = ?";
            $params[] = $escenario;
        }

        if ($flujo !== 'todos' && $flujo !== '') {
            $where[] = "flujo = ?";
            $params[] = $flujo;
        }

        if ($fecha_inicio !== '') {
            $where[] = "DATE(fecha_procesamiento) >= ?";
            $params[] = $fecha_inicio;
        }

        if ($fecha_fin !== '') {
            $where[] = "DATE(fecha_procesamiento) <= ?";
            $params[] = $fecha_fin;
        }

        $sql = "
            SELECT
                id,
                id_ejecucion,
                flujo,
                proceso,
                escenario,
                fecha_procesamiento,
                categoria_sistema,
                categoria_real,
                clasificacion_correcta,
                score_riesgo
            FROM clasificaciones_amenazas
        ";

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY fecha_procesamiento ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $correctas = 0;
        $incorrectas = 0;

        foreach ($datos as $fila) {
            if ((int)$fila['clasificacion_correcta'] === 1) {
                $correctas++;
            } else {
                $incorrectas++;
            }
        }

        $total = count($datos);

        $porcentaje = $total > 0
            ? ($correctas / $total) * 100
            : 0;

        $totales = [
            'total' => $total,
            'correctas' => $correctas,
            'incorrectas' => $incorrectas,
            'porcentaje' => $porcentaje
        ];

        break;


    /*
    |--------------------------------------------------------------------------
    | 4. EJECUCIONES SEGURAS
    |--------------------------------------------------------------------------
    */

    case 'ejecuciones':

        $params = [];
        $where = [];

        if ($escenario !== 'todos') {
            $where[] = "escenario = ?";
            $params[] = $escenario;
        }

        if ($flujo !== 'todos' && $flujo !== '') {
            $where[] = "flujo = ?";
            $params[] = $flujo;
        }

        if ($fecha_inicio !== '') {
            $where[] = "DATE(inicio) >= ?";
            $params[] = $fecha_inicio;
        }

        if ($fecha_fin !== '') {
            $where[] = "DATE(inicio) <= ?";
            $params[] = $fecha_fin;
        }

        $sql = "
            SELECT
                id,
                flujo,
                proceso,
                escenario,
                inicio,
                fin,
                estado,
                incidente,
                activo_comprometido,
                ejecucion_segura
            FROM ejecuciones
        ";

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY inicio ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = count($datos);
        $seguras = 0;

        foreach ($datos as $fila) {
            if ((int)$fila['ejecucion_segura'] === 1) {
                $seguras++;
            }
        }

        $porcentaje = $total > 0
            ? ($seguras / $total) * 100
            : 0;

        $totales = [
            'total' => $total,
            'seguras' => $seguras,
            'porcentaje' => $porcentaje
        ];

        break;


    /*
    |--------------------------------------------------------------------------
    | 5. MTTD
    |--------------------------------------------------------------------------
    */

    case 'mttd':

        $params = [];
        $where = [];

        if ($escenario !== 'todos') {
            $where[] = "escenario = ?";
            $params[] = $escenario;
        }

        if ($flujo !== 'todos' && $flujo !== '') {
            $where[] = "flujo = ?";
            $params[] = $flujo;
        }

        if ($fecha_inicio !== '') {
            $where[] = "DATE(hora_ingreso) >= ?";
            $params[] = $fecha_inicio;
        }

        if ($fecha_fin !== '') {
            $where[] = "DATE(hora_ingreso) <= ?";
            $params[] = $fecha_fin;
        }

        $sql = "
            SELECT
                id,
                flujo,
                proceso,
                escenario,
                id_incidente,
                hora_ingreso,
                hora_deteccion,
                categoria,
                CASE
                    WHEN hora_ingreso IS NOT NULL
                    AND hora_deteccion IS NOT NULL
                    THEN TIMESTAMPDIFF(
                        SECOND,
                        hora_ingreso,
                        hora_deteccion
                    ) / 60
                    ELSE NULL
                END AS tiempo_deteccion
            FROM log_eventos
        ";

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY hora_ingreso ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $suma = 0;
        $detectados = 0;

        foreach ($datos as $fila) {
            if ($fila['tiempo_deteccion'] !== null) {
                $suma += (float)$fila['tiempo_deteccion'];
                $detectados++;
            }
        }

        $mttd = $detectados > 0
            ? $suma / $detectados
            : 0;

        $totales = [
            'total' => count($datos),
            'suma' => $suma,
            'mttd' => $mttd
        ];

        break;


    /*
    |--------------------------------------------------------------------------
    | 6. DISPONIBILIDAD
    |--------------------------------------------------------------------------
    */

    case 'disponibilidad':

        $params = [];
        $where = [];

        if ($escenario !== 'todos') {
            $where[] = "escenario = ?";
            $params[] = $escenario;
        }

        if ($flujo !== 'todos' && $flujo !== '') {
            $where[] = "flujo = ?";
            $params[] = $flujo;
        }

        if ($fecha_inicio !== '') {
            $where[] = "DATE(periodo_inicio) >= ?";
            $params[] = $fecha_inicio;
        }

        if ($fecha_fin !== '') {
            $where[] = "DATE(periodo_fin) <= ?";
            $params[] = $fecha_fin;
        }

        $sql = "
            SELECT
                id,
                flujo,
                proceso,
                escenario,
                periodo_inicio,
                periodo_fin,
                tiempo_programado_min,
                tiempo_disponible_min,
                tiempo_indisponible_min,
                porcentaje_disponibilidad,
                observaciones
            FROM disponibilidad_flujos
        ";

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY periodo_inicio ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $programado = 0;
        $disponible = 0;
        $indisponible = 0;

        foreach ($datos as $fila) {
            $programado += (float)$fila['tiempo_programado_min'];
            $disponible += (float)$fila['tiempo_disponible_min'];
            $indisponible += (float)$fila['tiempo_indisponible_min'];
        }

        $porcentaje = $programado > 0
            ? ($disponible / $programado) * 100
            : 0;

        $totales = [
            'programado' => $programado,
            'disponible' => $disponible,
            'indisponible' => $indisponible,
            'porcentaje' => $porcentaje
        ];

        break;
}

/*
|--------------------------------------------------------------------------
| URLs de navegación
|--------------------------------------------------------------------------
*/

function urlInstrumento($nombre)
{
    global $escenario, $flujo, $fecha_inicio, $fecha_fin;

    return '?instrumento=' . urlencode($nombre)
        . '&escenario=' . urlencode($escenario)
        . '&flujo=' . urlencode($flujo)
        . '&fecha_inicio=' . urlencode($fecha_inicio)
        . '&fecha_fin=' . urlencode($fecha_fin);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>CEFIC - Instrumentos de Evaluación</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
    background: #ffffff;
    font-family: Arial, Helvetica, sans-serif;
    color: #000;
}

.container {
    width: 96%;
    max-width: 1450px;
    margin: 20px auto;
}

.header {
    border: 2px solid #000;
    background: #e2f0d9;
    padding: 20px;
    text-align: center;
}

.header h1 {
    margin: 0;
    font-size: 24px;
}

.header p {
    margin: 8px 0 0;
    font-size: 14px;
}

.menu {
    margin-top: 15px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
}

.menu a {
    text-decoration: none;
    color: #000;
    border: 1px solid #000;
    background: #f2f2f2;
    padding: 10px 14px;
    font-size: 13px;
}

.menu a:hover {
    background: #d9ead3;
}

.menu a.active {
    background: #c6e0b4;
    font-weight: bold;
}

.filtros {
    margin-top: 15px;
    border: 2px solid #000;
    background: #f8fbf5;
    padding: 15px;
}

.filtros form {
    display: flex;
    flex-wrap: wrap;
    align-items: end;
    gap: 15px;
}

.filtro {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filtro label {
    font-weight: bold;
    font-size: 13px;
}

.filtro select,
.filtro input {
    border: 1px solid #000;
    padding: 7px;
    min-width: 170px;
    background: #fff;
}

.btn {
    border: 1px solid #000;
    background: #e2f0d9;
    padding: 8px 18px;
    cursor: pointer;
    font-weight: bold;
}

.instrumento {
    margin-top: 20px;
    border: 2px solid #000;
    background: #e2f0d9;
    padding: 25px;
}

.titulo {
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 25px;
}

.datos-instrumento {
    width: 75%;
    margin: 0 auto 25px;
}

.dato {
    display: flex;
    margin-bottom: 10px;
    font-size: 14px;
}

.dato .label {
    width: 220px;
    font-weight: bold;
    text-align: right;
    margin-right: 12px;
}

.dato .valor {
    border-bottom: 1px solid #000;
    min-width: 300px;
    padding-left: 5px;
}

.tabla-contenedor {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #e2f0d9;
    font-size: 13px;
}

th,
td {
    border: 1px solid #000;
    padding: 8px;
    text-align: center;
    vertical-align: middle;
}

th {
    font-weight: bold;
}

tr:nth-child(even) {
    background: rgba(255,255,255,0.25);
}

.total-box {
    margin: 25px auto 0;
    width: 75%;
}

.total-row {
    display: flex;
    margin: 8px 0;
    font-weight: bold;
}

.total-row .label {
    width: 320px;
    text-align: right;
    margin-right: 10px;
}

.indicador {
    margin-top: 25px;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
}

.indicador .resultado {
    font-size: 22px;
}

.estado-si {
    font-weight: bold;
}

.estado-no {
    font-weight: bold;
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border: 1px solid #000;
    font-size: 12px;
}

.footer {
    text-align: center;
    margin: 20px 0;
    font-size: 12px;
}

.vacio {
    text-align: center;
    padding: 30px;
    font-style: italic;
}

@media print {

    body {
        background: #fff;
    }

    .menu,
    .filtros {
        display: none;
    }

    .container {
        width: 100%;
        margin: 0;
    }

    .instrumento {
        margin-top: 0;
    }

}

@media (max-width: 800px) {

    .datos-instrumento,
    .total-box {
        width: 100%;
    }

    .dato {
        flex-direction: column;
    }

    .dato .label {
        width: 100%;
        text-align: left;
        margin-bottom: 4px;
    }

    .dato .valor {
        min-width: 100%;
    }

}

</style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>CEFIC</h1>


        <div class="menu">

            <?php foreach ($instrumentos as $key => $nombre): ?>

                <a
                    href="<?= e(urlInstrumento($key)) ?>"
                    class="<?= $instrumento === $key ? 'active' : '' ?>"
                >
                    <?= e($nombre) ?>
                </a>

            <?php endforeach; ?>

        </div>

    </div>


    <!-- FILTROS -->

    <div class="filtros">

        <form method="GET">

            <input
                type="hidden"
                name="instrumento"
                value="<?= e($instrumento) ?>"
            >

            <div class="filtro">

                <label>Flujo</label>

                <select name="flujo">

                    <option value="todos">
                        Todos
                    </option>

                    <?php foreach ($flujos as $f): ?>

                        <option
                            value="<?= e($f) ?>"
                            <?= $flujo === $f ? 'selected' : '' ?>
                        >
                            <?= e($f) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="filtro">

                <label>Escenario</label>

                <select name="escenario">

                    <option
                        value="todos"
                        <?= $escenario === 'todos' ? 'selected' : '' ?>
                    >
                        Todos
                    </option>

                    <option
                        value="sin_seguridad"
                        <?= $escenario === 'sin_seguridad' ? 'selected' : '' ?>
                    >
                        Sin seguridad
                    </option>

                    <option
                        value="con_seguridad"
                        <?= $escenario === 'con_seguridad' ? 'selected' : '' ?>
                    >
                        Con seguridad
                    </option>

                </select>

            </div>


            <div class="filtro">

                <label>Fecha inicio</label>

                <input
                    type="date"
                    name="fecha_inicio"
                    value="<?= e($fecha_inicio) ?>"
                >

            </div>


            <div class="filtro">

                <label>Fecha fin</label>

                <input
                    type="date"
                    name="fecha_fin"
                    value="<?= e($fecha_fin) ?>"
                >

            </div>


            <button class="btn" type="submit">
                Consultar
            </button>

            <button
                class="btn"
                type="button"
                onclick="window.print()"
            >
                Imprimir
            </button>

        </form>

    </div>


    <!-- ==========================================================
         INSTRUMENTO 1
         ========================================================== -->

    <?php if ($instrumento === 'controles'): ?>

    <div class="instrumento">

        <div class="titulo">
            LISTA DE COTEJO — INTEGRACIÓN DE CONTROLES DE SEGURIDAD
        </div>

        <div class="datos-instrumento">

            <div class="dato">
                <div class="label">Flujo evaluado:</div>
                <div class="valor">
                    <?= $flujo === 'todos' ? 'Todos' : e($flujo) ?>
                </div>
            </div>

            <div class="dato">
                <div class="label">Escenario:</div>
                <div class="valor">
                    <?= e(escenarioTexto($escenario)) ?>
                </div>
            </div>

            <div class="dato">
                <div class="label">Fecha:</div>
                <div class="valor">
                    <?= date('d/m/Y') ?>
                </div>
            </div>

        </div>

        <div class="tabla-contenedor">

            <table>

                <thead>

                    <tr>
                        <th>N.º</th>
                        <th>Control / función de seguridad</th>
                        <th>Implementado</th>
                        <th>Evidencia</th>
                        <th>Observación</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (!$datos): ?>

                    <tr>
                        <td colspan="5" class="vacio">
                            No existen registros para los filtros seleccionados.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($datos as $fila): ?>

                    <tr>

                        <td>
                            <?= e($fila['numero_control']) ?>
                        </td>

                        <td style="text-align:left;">
                            <?= e($fila['nombre_control']) ?>
                        </td>

                        <td>
                            <?= boolTexto($fila['implementado']) ?>
                        </td>

                        <td style="text-align:left;">
                            <?= e($fila['evidencia_tecnica'] ?? '') ?>
                        </td>

                        <td style="text-align:left;">
                            <?= e($fila['observaciones'] ?? '') ?>
                        </td>

                    </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div class="total-box">

            <div class="total-row">
                <div class="label">
                    N.º de controles de seguridad integrados:
                </div>
                <div>
                    <?= e($totales['implementados']) ?>
                </div>
            </div>

            <div class="total-row">
                <div class="label">
                    N.º de controles de seguridad requeridos:
                </div>
                <div>
                    <?= e($totales['total']) ?>
                </div>
            </div>

        </div>

        <div class="indicador">

            Índice de integración:

            <span class="resultado">
                <?= number_format($totales['porcentaje'], 2) ?> %
            </span>

        </div>

    </div>

    <?php endif; ?>


    <!-- ==========================================================
         INSTRUMENTO 2 - MTTR
         ========================================================== -->

    <?php if ($instrumento === 'mttr'): ?>

    <div class="instrumento">

        <div class="titulo">
            FICHA SLA — TIEMPO MEDIO DE RESPUESTA (MTTR)
        </div>

        <div class="datos-instrumento">

            <div class="dato">
                <div class="label">Flujo evaluado:</div>
                <div class="valor">
                    <?= $flujo === 'todos' ? 'Todos' : e($flujo) ?>
                </div>
            </div>

            <div class="dato">
                <div class="label">Periodo de evaluación:</div>
                <div class="valor">
                    <?= $fecha_inicio ?: '---' ?>
                    &nbsp; — &nbsp;
                    <?= $fecha_fin ?: '---' ?>
                </div>
            </div>

            <div class="dato">
                <div class="label">Escenario:</div>
                <div class="valor">
                    <?= e(escenarioTexto($escenario)) ?>
                </div>
            </div>

        </div>

        <div class="tabla-contenedor">

            <table>

                <thead>

                    <tr>
                        <th>N.º</th>
                        <th>ID Incidente</th>
                        <th>Hora detección</th>
                        <th>Hora resolución</th>
                        <th>Tiempo (min)</th>
                        <th>Tipo de incidente</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (!$datos): ?>

                    <tr>
                        <td colspan="6" class="vacio">
                            No existen registros.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($datos as $i => $fila): ?>

                    <tr>

                        <td><?= $i + 1 ?></td>

                        <td>
                            <?= e($fila['id_incidente']) ?>
                        </td>

                        <td>
                            <?= e($fila['hora_deteccion']) ?>
                        </td>

                        <td>
                            <?= e($fila['hora_resolucion'] ?? 'Pendiente') ?>
                        </td>

                        <td>
                            <?= $fila['tiempo_respuesta'] !== null
                                ? number_format($fila['tiempo_respuesta'], 2)
                                : 'Pendiente'
                            ?>
                        </td>

                        <td>
                            <?= e($fila['categoria']) ?>
                        </td>

                    </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div class="total-box">

            <div class="total-row">
                <div class="label">
                    Total de incidentes registrados:
                </div>
                <div>
                    <?= e($totales['total']) ?>
                </div>
            </div>

            <div class="total-row">
                <div class="label">
                    Suma de tiempos de respuesta:
                </div>
                <div>
                    <?= number_format($totales['suma'], 2) ?> minutos
                </div>
            </div>

        </div>

        <div class="indicador">

            Tiempo Medio de Respuesta (MTTR) =

            <span class="resultado">
                <?= number_format($totales['mttr'], 2) ?>
            </span>

            minutos

        </div>

    </div>

    <?php endif; ?>


    <!-- ==========================================================
         INSTRUMENTO 3 - CLASIFICACIÓN
         ========================================================== -->

    <?php if ($instrumento === 'clasificacion'): ?>

    <div class="instrumento">

        <div class="titulo">
            FICHA DE CLASIFICACIÓN DE AMENAZAS
        </div>

        <div class="datos-instrumento">

            <div class="dato">
                <div class="label">Flujo evaluado:</div>
                <div class="valor">
                    <?= $flujo === 'todos' ? 'Todos' : e($flujo) ?>
                </div>
            </div>

            <div class="dato">
                <div class="label">Periodo de evaluación:</div>
                <div class="valor">
                    <?= $fecha_inicio ?: '---' ?>
                    &nbsp; — &nbsp;
                    <?= $fecha_fin ?: '---' ?>
                </div>
            </div>

            <div class="dato">
                <div class="label">Escenario:</div>
                <div class="valor">
                    <?= e(escenarioTexto($escenario)) ?>
                </div>
            </div>

        </div>

        <div class="tabla-contenedor">

            <table>

                <thead>

                    <tr>
                        <th>N.º</th>
                        <th>ID Evento</th>
                        <th>Clasificación del sistema</th>
                        <th>Clasificación real</th>
                        <th>Correcto</th>
                        <th>Score de riesgo</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (!$datos): ?>

                    <tr>
                        <td colspan="6" class="vacio">
                            No existen registros.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($datos as $i => $fila): ?>

                    <tr>

                        <td><?= $i + 1 ?></td>

                        <td>
                            <?= e($fila['id_ejecucion']) ?>
                        </td>

                        <td>
                            <?= e($fila['categoria_sistema']) ?>
                        </td>

                        <td>
                            <?= e($fila['categoria_real']) ?>
                        </td>

                        <td>
                            <?= boolTexto($fila['clasificacion_correcta']) ?>
                        </td>

                        <td>
                            <?= e($fila['score_riesgo']) ?>
                        </td>

                    </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div class="total-box">

            <div class="total-row">
                <div class="label">
                    Total correctamente clasificados:
                </div>
                <div>
                    <?= e($totales['correctas']) ?>
                </div>
            </div>

            <div class="total-row">
                <div class="label">
                    Total incorrectamente clasificados:
                </div>
                <div>
                    <?= e($totales['incorrectas']) ?>
                </div>
            </div>

            <div class="total-row">
                <div class="label">
                    Total amenazas procesadas:
                </div>
                <div>
                    <?= e($totales['total']) ?>
                </div>
            </div>

        </div>

        <div class="indicador">

            Tasa de clasificación correcta de amenazas =

            <span class="resultado">
                <?= number_format($totales['porcentaje'], 2) ?> %
            </span>

        </div>

    </div>

    <?php endif; ?>


    <!-- ==========================================================
         INSTRUMENTO 4 - EJECUCIONES SEGURAS
         ========================================================== -->

    <?php if ($instrumento === 'ejecuciones'): ?>

    <div class="instrumento">

        <div class="titulo">
            LISTA DE COTEJO — EJECUCIONES SEGURAS
        </div>

        <div class="datos-instrumento">

            <div class="dato">
                <div class="label">Flujo evaluado:</div>
                <div class="valor">
                    <?= $flujo === 'todos' ? 'Todos' : e($flujo) ?>
                </div>
            </div>

            <div class="dato">
                <div class="label">Periodo de evaluación:</div>
                <div class="valor">
                    <?= $fecha_inicio ?: '---' ?>
                    &nbsp; — &nbsp;
                    <?= $fecha_fin ?: '---' ?>
                </div>
            </div>

            <div class="dato">
                <div class="label">Escenario:</div>
                <div class="valor">
                    <?= e(escenarioTexto($escenario)) ?>
                </div>
            </div>

        </div>

        <div class="tabla-contenedor">

            <table>

                <thead>

                    <tr>
                        <th>N.º</th>
                        <th>ID ejecución</th>
                        <th>¿Hubo incidente?</th>
                        <th>¿Activos comprometidos?</th>
                        <th>Ejecución segura</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (!$datos): ?>

                    <tr>
                        <td colspan="5" class="vacio">
                            No existen registros.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($datos as $i => $fila): ?>

                    <tr>

                        <td>
                            <?= $i + 1 ?>
                        </td>

                        <td>
                            <?= e($fila['id']) ?>
                        </td>

                        <td>
                            <?= boolTexto($fila['incidente']) ?>
                        </td>

                        <td>
                            <?= boolTexto($fila['activo_comprometido']) ?>
                        </td>

                        <td>
                            <strong>
                                <?= boolTexto($fila['ejecucion_segura']) ?>
                            </strong>
                        </td>

                    </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div class="total-box">

            <div class="total-row">
                <div class="label">
                    TOTAL de ejecuciones:
                </div>
                <div>
                    <?= e($totales['total']) ?>
                </div>
            </div>

            <div class="total-row">
                <div class="label">
                    TOTAL ejecuciones seguras:
                </div>
                <div>
                    <?= e($totales['seguras']) ?>
                </div>
            </div>

        </div>

        <div class="indicador">

            Porcentaje de ejecuciones seguras del flujo =

            <span class="resultado">
                <?= number_format($totales['porcentaje'], 2) ?> %
            </span>

        </div>

    </div>

    <?php endif; ?>


    <!-- ==========================================================
         INSTRUMENTO 5 - MTTD
         ========================================================== -->

    <?php if ($instrumento === 'mttd'): ?>

    <div class="instrumento">

        <div class="titulo">
            FICHA SLA — TIEMPO MEDIO DE DETECCIÓN (MTTD)
        </div>

        <div class="datos-instrumento">

            <div class="dato">
                <div class="label">Flujo evaluado:</div>
                <div class="valor">
                    <?= $flujo === 'todos' ? 'Todos' : e($flujo) ?>
                </div>
            </div>

            <div class="dato">
                <div class="label">Periodo de evaluación:</div>
                <div class="valor">
                    <?= $fecha_inicio ?: '---' ?>
                    &nbsp; — &nbsp;
                    <?= $fecha_fin ?: '---' ?>
                </div>
            </div>

            <div class="dato">
                <div class="label">Escenario:</div>
                <div class="valor">
                    <?= e(escenarioTexto($escenario)) ?>
                </div>
            </div>

        </div>

        <div class="tabla-contenedor">

            <table>

                <thead>

                    <tr>
                        <th>N.º</th>
                        <th>Evento</th>
                        <th>Hora ingreso</th>
                        <th>Hora detección</th>
                        <th>Tiempo detección (min)</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (!$datos): ?>

                    <tr>
                        <td colspan="5" class="vacio">
                            No existen registros.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($datos as $i => $fila): ?>

                    <tr>

                        <td>
                            <?= $i + 1 ?>
                        </td>

                        <td>
                            <?= e($fila['id_incidente']) ?>
                        </td>

                        <td>
                            <?= e($fila['hora_ingreso']) ?>
                        </td>

                        <td>
                            <?= e($fila['hora_deteccion'] ?? 'Pendiente') ?>
                        </td>

                        <td>
                            <?= $fila['tiempo_deteccion'] !== null
                                ? number_format($fila['tiempo_deteccion'], 2)
                                : 'Pendiente'
                            ?>
                        </td>

                    </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div class="indicador">

            Tiempo Medio de Detección (MTTD) =

            <span class="resultado">
                <?= number_format($totales['mttd'], 2) ?>
            </span>

            minutos

        </div>

    </div>

    <?php endif; ?>


    <!-- ==========================================================
         INSTRUMENTO 6 - DISPONIBILIDAD
         ========================================================== -->

    <?php if ($instrumento === 'disponibilidad'): ?>

    <div class="instrumento">

        <div class="titulo">
            REGISTRO DE DISPONIBILIDAD DEL FLUJO
        </div>

        <div class="datos-instrumento">

            <div class="dato">
                <div class="label">Flujo evaluado:</div>
                <div class="valor">
                    <?= $flujo === 'todos' ? 'Todos' : e($flujo) ?>
                </div>
            </div>

            <div class="dato">
                <div class="label">Periodo de evaluación:</div>
                <div class="valor">
                    <?= $fecha_inicio ?: '---' ?>
                    &nbsp; — &nbsp;
                    <?= $fecha_fin ?: '---' ?>
                </div>
            </div>

            <div class="dato">
                <div class="label">Escenario:</div>
                <div class="valor">
                    <?= e(escenarioTexto($escenario)) ?>
                </div>
            </div>

        </div>

        <div class="tabla-contenedor">

            <table>

                <thead>

                    <tr>
                        <th>Día</th>
                        <th>Horas evaluadas</th>
                        <th>Horas con fallos</th>
                        <th>Horas disponibles</th>
                        <th>Observación</th>
                    </tr>

                </thead>

                <tbody>

                <?php if (!$datos): ?>

                    <tr>
                        <td colspan="5" class="vacio">
                            No existen registros.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($datos as $fila): ?>

                    <tr>

                        <td>
                            <?= e(date(
                                'd/m/Y',
                                strtotime($fila['periodo_inicio'])
                            )) ?>
                        </td>

                        <td>
                            <?= number_format(
                                ((float)$fila['tiempo_programado_min']) / 60,
                                2
                            ) ?>
                        </td>

                        <td>
                            <?= number_format(
                                ((float)$fila['tiempo_indisponible_min']) / 60,
                                2
                            ) ?>
                        </td>

                        <td>
                            <?= number_format(
                                ((float)$fila['tiempo_disponible_min']) / 60,
                                2
                            ) ?>
                        </td>

                        <td>
                            <?= e($fila['observaciones'] ?? '') ?>
                        </td>

                    </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div class="total-box">

            <div class="total-row">
                <div class="label">
                    TOTAL de horas activas:
                </div>
                <div>
                    <?= number_format(
                        $totales['disponible'] / 60,
                        2
                    ) ?>
                </div>
            </div>

            <div class="total-row">
                <div class="label">
                    TOTAL horas evaluadas:
                </div>
                <div>
                    <?= number_format(
                        $totales['programado'] / 60,
                        2
                    ) ?>
                </div>
            </div>

            <div class="total-row">
                <div class="label">
                    TOTAL de horas con fallo:
                </div>
                <div>
                    <?= number_format(
                        $totales['indisponible'] / 60,
                        2
                    ) ?>
                </div>
            </div>

        </div>

        <div class="indicador">

            Tasa de disponibilidad del flujo automatizado =

            <span class="resultado">
                <?= number_format($totales['porcentaje'], 2) ?> %
            </span>

        </div>

    </div>

    <?php endif; ?>


    <div class="footer">

        @LIANN MELANNY

    </div>

</div>

</body>
</html>