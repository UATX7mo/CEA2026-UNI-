<?php
require_once __DIR__ . '/autorizacion.php';
exigirAccesoAdmon();
require_once __DIR__ . '/../complementos/conexion.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function respuestaLink(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$ruta = trim($_POST['url'] ?? '');
$categoria = strtolower(trim($_POST['categoria'] ?? ''));
$categoriasValidas = ['preescolar', 'primaria', 'secundaria'];

if ($categoria === 'administrador') {
    respuestaLink(400, ['ok' => false, 'mensaje' => 'No se pueden agregar módulos de Administrador.']);
}
if ($nombre === '' || $ruta === '' || mb_strlen($nombre) > 120 || mb_strlen($ruta) > 255
    || !in_array($categoria, $categoriasValidas, true)) {
    respuestaLink(400, ['ok' => false, 'mensaje' => 'Completa el nombre, una ruta válida y una categoría.']);
}

$raiz = realpath(__DIR__ . '/..');
$ruta = str_replace('\\', '/', $ruta);
if (preg_match('~^https?://~i', $ruta)) {
    $partesUrl = parse_url($ruta);
    $host = strtolower($partesUrl['host'] ?? '');
    $proyecto = basename($raiz ?: dirname(__DIR__));
    $prefijoWeb = '/' . $proyecto . '/';
    $rutaUrl = $partesUrl['path'] ?? '';
    if (!in_array($host, ['localhost', '127.0.0.1', '[::1]', '::1'], true)
        || isset($partesUrl['user']) || isset($partesUrl['pass'])
        || isset($partesUrl['query']) || isset($partesUrl['fragment'])
        || strpos($rutaUrl, $prefijoWeb) !== 0) {
        respuestaLink(400, ['ok' => false, 'mensaje' => 'La URL debe ser local y apuntar a una página dentro de este proyecto.']);
    }
    $ruta = substr($rutaUrl, strlen($prefijoWeb));
} else {
    $ruta = ltrim($ruta, '/');
    $proyecto = basename($raiz ?: dirname(__DIR__));
    if (strpos($ruta, $proyecto . '/') === 0) {
        $ruta = substr($ruta, strlen($proyecto) + 1);
    }
}

if (preg_match('~(^|/)\.\.(?:/|$)|[?#]|^[a-z][a-z0-9+.-]*:~i', $ruta)) {
    respuestaLink(400, ['ok' => false, 'mensaje' => 'Usa una ruta relativa a una página PHP local del proyecto.']);
}
if (pathinfo($ruta, PATHINFO_EXTENSION) === '') {
    $ruta .= '.php';
}
if (strtolower(pathinfo($ruta, PATHINFO_EXTENSION)) !== 'php') {
    respuestaLink(400, ['ok' => false, 'mensaje' => 'La página indicada debe ser un archivo PHP del proyecto.']);
}

$archivo = realpath($raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ruta));
if ($raiz === false || $archivo === false || !is_file($archivo)) {
    respuestaLink(404, ['ok' => false, 'mensaje' => 'No se encontró ese archivo PHP dentro del proyecto.']);
}

$prefijoRaiz = rtrim($raiz, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
if (stripos($archivo, $prefijoRaiz) !== 0) {
    respuestaLink(400, ['ok' => false, 'mensaje' => 'La ruta debe permanecer dentro del proyecto.']);
}

$rutaRelativa = str_replace('\\', '/', substr($archivo, strlen($prefijoRaiz)));
$categoriaRequerida = categoriaRequeridaUrlModulo($rutaRelativa);
if ($categoriaRequerida === 'administrador') {
    respuestaLink(400, ['ok' => false, 'mensaje' => 'Los apartados del Administrador no se pueden registrar como módulos.']);
}
if ($categoriaRequerida !== null && $categoriaRequerida !== $categoria) {
    respuestaLink(400, ['ok' => false, 'mensaje' => 'La categoría seleccionada no corresponde a ese apartado.']);
}
$archivosInternos = [
    'admon/actualizar_url.php', 'admon/agregar_url.php', 'admon/eliminar_url.php', 'admon/estado_modulo.php',
    'admon/validar_modulo.php', 'admon/auto_validar_modulo.php', 'admon/autorizacion.php',
];
if (in_array(strtolower($rutaRelativa), $archivosInternos, true)) {
    respuestaLink(400, ['ok' => false, 'mensaje' => 'Ese archivo es interno y no se puede registrar como módulo.']);
}

try {
    $stmt = $pdo->prepare('SELECT id FROM urls_sistema WHERE url = :url LIMIT 1');
    $stmt->execute([':url' => $rutaRelativa]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        respuestaLink(409, ['ok' => false, 'mensaje' => 'Esa ruta ya está registrada.']);
    }

    $insertar = $pdo->prepare('INSERT INTO urls_sistema (nombre, url, estado, categoria) VALUES (:nombre, :url, 1, :categoria)');
    $insertar->execute([':nombre' => $nombre, ':url' => $rutaRelativa, ':categoria' => $categoria]);
    $id = (int) $pdo->lastInsertId();
} catch (Throwable $error) {
    error_log('Error al agregar link en urls_sistema: ' . $error->getMessage());
    respuestaLink(500, ['ok' => false, 'mensaje' => 'No se pudo guardar el link en la base de datos.']);
}

respuestaLink(201, [
    'ok' => true,
    'modulo' => ['id' => $id, 'nombre' => $nombre, 'url' => $rutaRelativa, 'estado' => 1, 'categoria' => $categoria],
]);
