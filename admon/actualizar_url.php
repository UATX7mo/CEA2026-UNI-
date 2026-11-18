<?php
require_once __DIR__ . '/autorizacion.php';
exigirAccesoAdmon();

require_once '../complementos/conexion.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!isset($_POST['id'], $_POST['estado'])
    || !ctype_digit((string) $_POST['id'])
    || !in_array((string) $_POST['estado'], ['0', '1'], true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Solicitud de estado inválida.']);
    exit;
}

$id = (int) $_POST['id'];
$estado = (int) $_POST['estado'];

$buscar = $pdo->prepare('SELECT categoria, estado, url FROM urls_sistema WHERE id = :id LIMIT 1');
$buscar->execute([':id' => $id]);
$modulo = $buscar->fetch(PDO::FETCH_ASSOC);
if (!$modulo) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'mensaje' => 'No se encontró el link.']);
    exit;
}
$categoriaModulo = strtolower(trim((string) ($modulo['categoria'] ?? '')));
if (!in_array($categoriaModulo, ['preescolar', 'primaria', 'secundaria'], true)) {
    $categoriaModulo = 'administrador';
}
$categoriaRequerida = categoriaRequeridaUrlModulo((string) $modulo['url']);
if ($estado === 0 && ($categoriaModulo === 'administrador'
    || $categoriaRequerida === 'administrador'
    || ($categoriaRequerida !== null && $categoriaRequerida !== $categoriaModulo))) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'mensaje' => 'Los módulos de Administrador no se pueden desactivar.']);
    exit;
}

$stmt = $pdo->prepare('UPDATE urls_sistema SET estado = :estado WHERE id = :id');
$stmt->execute([':estado' => $estado, ':id' => $id]);

$consulta = $pdo->prepare('SELECT estado FROM urls_sistema WHERE id = :id');
$consulta->execute([':id' => $id]);
$estadoGuardado = $consulta->fetchColumn();

if ($estadoGuardado === false) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'mensaje' => 'No se encontró el link.']);
    exit;
}

echo json_encode(['ok' => true, 'estado' => (int) $estadoGuardado]);
