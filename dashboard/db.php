<?php

$host = 'mysql';
$db   = 'cefic';
$user = 'cefic_user';
$pass = getenv('MYSQL_PASSWORD');

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {

    $pdo = new PDO($dsn, $user, $pass, $options);

    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET CHARACTER SET utf8mb4");

} catch (PDOException $e) {

    die("Error de conexión con MySQL: " . $e->getMessage());
}