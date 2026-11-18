<?php
require_once __DIR__ . '/autorizacion.php';
exigirAccesoAdmon();
require_once __DIR__ . '/../complementos/conexion.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!isset($_POST['id']) || !ctype_digit((string) $_POST['id']) || (int) $_POST['id'] < 1) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Solicitud de eliminación inválida.']);
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM urls_sistema WHERE id = :id');
    $stmt->execute([':id' => (int) $_POST['id']]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'mensaje' => 'El link ya no existe.']);
        exit;
    }
    echo json_encode(['ok' => true]);
} catch (Throwable $error) {
    error_log('Error al eliminar link en urls_sistema: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'No se pudo eliminar el link.']);
}
