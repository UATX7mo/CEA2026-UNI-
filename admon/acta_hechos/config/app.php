<?php
declare(strict_types=1);

// Los secretos locales no se versionan. Las variables de entorno tienen prioridad.
$pimsLocal = is_file(__DIR__ . '/local.php') ? require __DIR__ . '/local.php' : [];
if (!is_array($pimsLocal)) {
    throw new RuntimeException('Configuración local inválida.');
}

function pims_config(string $clave, string $defecto = ''): string
{
    global $pimsLocal;
    $valor = getenv($clave);
    return $valor !== false ? $valor : (string) ($pimsLocal[$clave] ?? $defecto);
}

define('PIMS_ROOT', dirname(__DIR__));
define('PIMS_STORAGE', pims_config('PIMS_STORAGE_PATH', PIMS_ROOT . '/storage'));
define('PIMS_PRODUCTION', pims_config('PIMS_ENV', 'development') === 'production');
date_default_timezone_set('America/Mexico_City');
