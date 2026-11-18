<?php
require_once __DIR__ . '/acceso.php';

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'datos';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $esquema = file_get_contents(__DIR__ . '/Tabla.sql');
    if ($esquema === false) {
        throw new RuntimeException('No se pudo leer el esquema de incidencias.');
    }
    $pdo->exec($esquema);
} catch (PDOException $e) {
    http_response_code(500);
    die('No fue posible conectar con la base de datos de incidencias.');
}
?>
