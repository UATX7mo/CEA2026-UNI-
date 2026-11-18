<?php
// Se carga automáticamente por Apache en cada solicitud PHP. El control se
// aplica únicamente a páginas registradas en urls_sistema.
if (PHP_SAPI === 'cli' || basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'auto_validar_modulo.php') {
    return;
}

if ((strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admon/') !== false
        || strpos($_SERVER['SCRIPT_NAME'] ?? '', '/fichas2026/') !== false
        || in_array(basename($_SERVER['SCRIPT_NAME'] ?? ''), ['index.php', 'login.php'], true))
    && session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$rol = $_SESSION['rol'] ?? (isset($_SESSION['admin']) ? 'admin' : null);
$archivoSolicitado = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
$raizProyecto = realpath(__DIR__ . '/..');
$rutaRelativa = ($archivoSolicitado && $raizProyecto && strpos($archivoSolicitado, $raizProyecto) === 0)
    ? str_replace('\\', '/', ltrim(substr($archivoSolicitado, strlen($raizProyecto)), DIRECTORY_SEPARATOR))
    : '';
$nombreArchivo = basename($archivoSolicitado ?: ($_SERVER['SCRIPT_NAME'] ?? ''));
$raizWeb = str_replace('\\', '/', $raizProyecto ?: '');
$documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
$baseWeb = $documentRoot !== '' && stripos($raizWeb, $documentRoot) === 0
    ? substr($raizWeb, strlen($documentRoot))
    : '/CEA2026';

// Toda página PHP de administración e inscripciones requiere una sesión
// autenticada, aunque la ruta todavía no esté registrada en Control de URLs.
$esRutaPrivada = strpos($rutaRelativa, 'admon/') === 0
    || strpos($rutaRelativa, 'fichas2026/') === 0;
if ($esRutaPrivada && empty($_SESSION['admin'])) {
    header('Location: ' . rtrim($baseWeb, '/') . '/login.php');
    exit;
}

$urlSeccionPropia = '';
$pdoEstadoModulo = null;
$estadoModulo = false;
$categoriaModulo = null;
$moduloRegistrado = false;

// Recupera categoría y estado antes de autorizar las rutas dinámicas por rol.
try {
    $pdoEstadoModulo = new PDO('mysql:host=localhost;dbname=datos;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    if ($rutaRelativa !== '') {
        $consultaModulo = $pdoEstadoModulo->prepare(
            'SELECT estado, categoria FROM urls_sistema WHERE url = :ruta OR url = :pagina ORDER BY (url = :rutaOrden) DESC LIMIT 1'
        );
        $consultaModulo->execute([
            ':ruta' => $rutaRelativa,
            ':pagina' => $nombreArchivo,
            ':rutaOrden' => $rutaRelativa,
        ]);
        $modulo = $consultaModulo->fetch();
        if ($modulo) {
            require_once __DIR__ . '/autorizacion.php';
            $categoriaGuardada = strtolower(trim((string) ($modulo['categoria'] ?? '')));
            $categoriaRequerida = categoriaRequeridaUrlModulo($rutaRelativa);
            $categoriaModulo = $categoriaRequerida ?? $categoriaGuardada;
            if (!in_array($categoriaModulo, ['preescolar', 'primaria', 'secundaria'], true)) {
                $categoriaModulo = 'administrador';
            }
            // Los apartados administrativos nunca se bloquean desde Control de URLs.
            $estadoModulo = $categoriaModulo === 'administrador' ? 1 : (int) $modulo['estado'];
            $moduloRegistrado = true;
        }
    }
} catch (Throwable $error) {
    $pdoEstadoModulo = null;
}

if (in_array($rol, ['primaria', 'secundaria', 'preescolar'], true)) {
    $destinos = [
        'primaria' => 'vista_inscripciones_prima.php',
        'secundaria' => 'vista_inscripciones_secu.php',
        'preescolar' => 'vista_inscripciones_prees.php',
    ];
    $destino = $destinos[$rol];
    $urlSeccionPropia = rtrim($baseWeb, '/') . '/admon/' . $destino;
    if ($rutaRelativa === 'index.php' || $rutaRelativa === 'login.php') {
        header('Location: ' . $urlSeccionPropia);
        exit;
    }

    if (strpos($rutaRelativa, 'admon/') === 0) {
        require_once __DIR__ . '/autorizacion.php';
        autorizarPaginaAdmon();
    } elseif (strpos($rutaRelativa, 'fichas2026/') === 0) {
        $paginasPorNivel = [
            'primaria' => ['primaria2026.php', 'busca_primaria.php', 'validar_curp_primaria.php', 'formato_primaria.php', 'insertar_ficha_primaria.php'],
            'secundaria' => ['secundaria2026.php', 'busca_secundaria.php', 'validar_curp_secundaria.php', 'formato_secundaria.php', 'insertar_ficha_secundaria.php'],
            'preescolar' => ['preescolar2026.php', 'busca_preescolar.php', 'validar_curp_prees.php', 'formato_preescolar.php', 'insertar_ficha_preescolar.php'],
        ];
        $paginasReportesAutorizadas = [
            'reporte_alumno.php',
            'generar_pdf_reporte.php',
            'lista_reportes.php',
        ];
        if (!in_array($nombreArchivo, $paginasPorNivel[$rol], true)
            && !in_array($nombreArchivo, $paginasReportesAutorizadas, true)
            && !($moduloRegistrado && $categoriaModulo === $rol)) {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'HEAD') {
                http_response_code(403);
                exit('No tienes permiso para acceder a esta sección.');
            }
            header('Location: ' . $urlSeccionPropia);
            exit;
        }
    } elseif ($rutaRelativa !== 'cerrar.php' && strtolower(pathinfo($rutaRelativa, PATHINFO_EXTENSION)) === 'php'
        && !($moduloRegistrado && $categoriaModulo === $rol)) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'HEAD') {
            http_response_code(403);
            exit('No tienes permiso para acceder a esta sección.');
        }
        header('Location: ' . $urlSeccionPropia);
        exit;
    }
}

if ($rol !== null && $rol !== 'admin' && strpos($rutaRelativa, 'fichas2026/') === 0) {
    // Los roles de sección ya se limitaron a sus páginas permitidas arriba.
    // Los usuarios con otros roles no pueden abrir las páginas internas.
    if (!in_array($rol, ['primaria', 'secundaria', 'preescolar'], true)) {
        http_response_code(403);
        exit('No tienes permiso para acceder a esta sección.');
    }
}

// Una falla de base de datos no convierte páginas en desactivadas ni bloquea el sitio.
try {
    if ($rutaRelativa !== '') {
        $esPaginaGet = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
            && strtolower(pathinfo($rutaRelativa, PATHINFO_EXTENSION)) === 'php';

        if ($moduloRegistrado && $estadoModulo === 0) {
            http_response_code(503);
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            if ($esPaginaGet) {
                $urlMonitor = rtrim($baseWeb, '/') . '/admon/modulo_monitor.js';
                $urlEstado = rtrim($baseWeb, '/') . '/admon/estado_modulo.php';
                $paginaAtributo = htmlspecialchars($rutaRelativa, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $endpointAtributo = htmlspecialchars($urlEstado, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $cssAtributo = htmlspecialchars(rtrim($baseWeb, '/') . '/admon/modulo_monitor.css', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $monitorAtributo = htmlspecialchars($urlMonitor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $script = '<link rel="stylesheet" href="' . $cssAtributo . '"><script src="' . $monitorAtributo . '" data-pagina="' . $paginaAtributo . '" data-endpoint="' . $endpointAtributo . '" data-modo="reactivar" defer></script>';
                exit('<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"><title>Módulo desactivado</title></head><body style="margin:0;min-height:100vh;display:grid;place-items:center;font-family:Arial,sans-serif;color:#171717;text-align:center"><main><h1>Módulo desactivado temporalmente</h1><p>Se abrirá automáticamente cuando la administración lo active.</p></main>' . $script . '</body></html>');
            }
            exit('<!doctype html><html lang="es"><meta charset="utf-8"><title>Módulo desactivado</title><body><h1>Módulo desactivado temporalmente</h1></body></html>');
        }

        // Monitoriza todas las páginas PHP HTML, incluso las que aún no estaban
        // registradas cuando se abrieron, para detectar links agregados después.
        if ($esPaginaGet && $pdoEstadoModulo instanceof PDO) {
            $urlMonitor = rtrim($baseWeb, '/') . '/admon/modulo_monitor.js';
            $urlEstado = rtrim($baseWeb, '/') . '/admon/estado_modulo.php';
            $urlCss = rtrim($baseWeb, '/') . '/admon/modulo_monitor.css';
            $paginaAtributo = htmlspecialchars($rutaRelativa, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $endpointAtributo = htmlspecialchars($urlEstado, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $monitorAtributo = htmlspecialchars($urlMonitor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $cssAtributo = htmlspecialchars($urlCss, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            ob_start(static function ($html) use ($paginaAtributo, $endpointAtributo, $monitorAtributo, $cssAtributo) {
                if (!is_string($html) || stripos($html, '<html') === false) {
                    return $html;
                }
                foreach (headers_list() as $cabecera) {
                    if (stripos($cabecera, 'Content-Type: application/json') === 0) {
                        return $html;
                    }
                }
                $script = '<link rel="stylesheet" href="' . $cssAtributo . '"><script data-urls-sistema-monitor src="' . $monitorAtributo . '" data-pagina="' . $paginaAtributo . '" data-endpoint="' . $endpointAtributo . '" data-modo="vigilar" defer></script>';
                if (stripos($html, '</body>') !== false) {
                    return str_ireplace('</body>', $script . '</body>', $html);
                }
                return $html . $script;
            });
        }
    }
} catch (Throwable $error) {
    // Deja que la página siga su flujo normal si la base no está disponible.
}
