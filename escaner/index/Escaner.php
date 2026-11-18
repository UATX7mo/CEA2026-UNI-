<?php
require_once __DIR__ . '/../../admon/autorizacion.php';

if (empty($_SESSION['admin'])) {
    header('Location: /CEA2026/login.php');
    exit;
}

$rolEscaner = rolAdmonActual();
if ($rolEscaner !== 'admin') {
    header('Location: /CEA2026/admon/' . rutaInicialAdmon($rolEscaner));
    exit;
}

function escanerBackendActivo(): bool
{
    $contexto = stream_context_create(['http' => ['timeout' => 0.5, 'ignore_errors' => true]]);
    $respuesta = @file_get_contents('http://127.0.0.1:5000/lector-raw', false, $contexto);
    if (!is_string($respuesta)) {
        return false;
    }
    $datos = json_decode($respuesta, true);
    return is_array($datos) && array_key_exists('texto_crudo', $datos) && array_key_exists('timestamp', $datos);
}

function iniciarEscanerAlEntrar(): bool
{
    if (escanerBackendActivo()) {
        return true;
    }

    // El entorno anterior fue creado con Python 3.14, que ya no está instalado.
    // Usamos el entorno reconstruido con la versión disponible en este equipo.
    $python = __DIR__ . '\\.venv313\\Scripts\\python.exe';
    $script = __DIR__ . '\\Scan.py';
    if (!is_file($python) || !is_file($script) || !function_exists('popen')) {
        return false;
    }

    $candado = @fopen(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'CEA2026_escaner.lock', 'c+');
    if (!is_resource($candado) || !@flock($candado, LOCK_EX)) {
        if (is_resource($candado)) {
            fclose($candado);
        }
        return escanerBackendActivo();
    }

    try {
        if (escanerBackendActivo()) {
            return true;
        }

        $log = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'CEA2026_escaner.log';
        $comando = 'start "" /B "' . $python . '" "' . $script . '" >> "' . $log . '" 2>&1';
        $proceso = @popen($comando, 'r');
        if (!is_resource($proceso)) {
            return false;
        }
        @pclose($proceso);

        $limite = microtime(true) + 8;
        do {
            if (escanerBackendActivo()) {
                return true;
            }
            usleep(250000);
        } while (microtime(true) < $limite);

        return false;
    } finally {
        @flock($candado, LOCK_UN);
        fclose($candado);
    }
}

$escanerListo = iniciarEscanerAlEntrar();

$adminEscanerPage = true;
require __DIR__ . '/../../admon/encabezado.php';
?>
<main class="contenedor">
    <h1>Sistema de Asistencia</h1>
    <p class="subtitulo">Por favor, acerque su credencial o código al lector</p>

    <div class="visor-contenedor">
        <img src="/CEA2026/IMG/scanner.gif" alt="Visor de escaneo" class="visor-gif" onerror="this.src='/CEA2026/escaner/index/scanner.gif'">
    </div>

    <div id="aviso-guia" class="aviso-guia">
        Mantenga el código quieto frente al lector
    </div>

    <?php if (!$escanerListo): ?>
        <div class="alert alert-warning mt-3" role="alert">
            No se pudo iniciar el lector automáticamente. Revisa que Apache pueda ejecutar Python y consulta %TEMP%\CEA2026_escaner.log para ver el error.
        </div>
    <?php endif; ?>

    <div id="mensaje">Esperando escaneo...</div>
    <div id="detalles" class="info"></div>
</main>

<script src="/CEA2026/JS/escaner.js"></script>
<?php require __DIR__ . '/../../admon/pie.php'; ?>
