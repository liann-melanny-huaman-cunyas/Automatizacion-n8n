<?php

header('Content-Type: application/json; charset=utf-8');

$host = 'mysql';
$port = 3306;
$db   = 'cefic';
$user = 'cefic_user';
$pass = getenv('MYSQL_PASSWORD');

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $resultado = [];

    $consultas = [
        'ejecuciones' => "SELECT COUNT(*) AS total FROM ejecuciones",
        'log_eventos' => "SELECT COUNT(*) AS total FROM log_eventos",
        'clasificaciones_amenazas' => "SELECT COUNT(*) AS total FROM clasificaciones_amenazas",
        'disponibilidad_flujos' => "SELECT COUNT(*) AS total FROM disponibilidad_flujos"
    ];

    foreach ($consultas as $tabla => $sql) {
        $stmt = $pdo->query($sql);
        $resultado[$tabla] = (int) $stmt->fetch()['total'];
    }

    echo json_encode([
        'ok' => true,
        'base_datos' => 'cefic',
        'datos' => $resultado
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'error' => 'No se pudo conectar con MySQL',
        'detalle' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}