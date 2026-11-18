<?php
require_once __DIR__ . '/autorizacion.php';
require_once __DIR__ . '/../complementos/conexion.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$pagina = str_replace('\\', '/', trim($_POST['url'] ?? $_GET['url'] ?? ''));
$pagina = ltrim($pagina, '/');
$nombreArchivo = basename($pagina);

if ($pagina === '') {
    http_response_code(400);
    echo json_encode(['estado' => null]);
    exit;
}

$stmt = $pdo->prepare('SELECT estado, categoria, url FROM urls_sistema WHERE url = :ruta OR url = :pagina ORDER BY (url = :rutaOrden) DESC LIMIT 1');
$stmt->execute([':ruta' => $pagina, ':pagina' => $nombreArchivo, ':rutaOrden' => $pagina]);
$modulo = $stmt->fetch(PDO::FETCH_ASSOC);

// Los módulos administrativos siempre se consideran activos; no se pueden bloquear.
$categoria = strtolower(trim((string) ($modulo['categoria'] ?? '')));
$categoriaRequerida = $modulo ? categoriaRequeridaUrlModulo((string) $modulo['url']) : null;
if ($categoriaRequerida !== null) {
    $categoria = $categoriaRequerida;
}
if (!in_array($categoria, ['preescolar', 'primaria', 'secundaria'], true)) {
    $categoria = 'administrador';
}
$estado = $modulo ? ($categoria === 'administrador' ? 1 : (int) $modulo['estado']) : null;
echo json_encode(['estado' => $estado]);
