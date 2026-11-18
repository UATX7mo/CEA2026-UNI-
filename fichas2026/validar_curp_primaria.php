<?php
include_once '../complementos/conexion_fichas.php';

header('Content-Type: text/plain; charset=utf-8'); // opcional, pero recomendable

$curp = mb_strtoupper(trim($_POST['busca'] ?? ''));

if (!$curp) {
    echo "0";
    exit();
}

$sql_leer = 'SELECT * FROM primaria WHERE curp = ?';
$gsent = $pdo->prepare($sql_leer);
$gsent->execute([$curp]);
$alumno = $gsent->fetch(PDO::FETCH_ASSOC);

if (!$alumno) {
    echo "0"; // CURP no encontrado
} else {
    echo "1"; // CURP encontrado
}
exit();