<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
require_once PIMS_ROOT . '/config/institucion.php';
require_once __DIR__ . '/ui/iconos.php';
require_once __DIR__ . '/security/seguridad.php';
require_once __DIR__ . '/database/conexion.php';
require_once __DIR__ . '/security/permisos.php';
require_once __DIR__ . '/security/cuentas.php';
require_once __DIR__ . '/security/session.php';
require_once __DIR__ . '/alumnos/repository.php';
require_once __DIR__ . '/actas/datos.php';
require_once __DIR__ . '/actas/consultas.php';
require_once __DIR__ . '/actas/repository.php';

set_exception_handler(static function (Throwable $error): void {
    pims_log_error($error);
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "No se pudo completar la operación. Consulta storage/logs.\n");
        exit(1);
    }
    http_response_code(503);
    echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>Servicio no disponible</title><h1>Servicio no disponible</h1><p>No se pudo completar la operación. Contacta con administración.</p></html>';
});

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store, private');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: no-referrer');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; frame-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'none'; object-src 'none'");
    if (pims_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    } elseif (PIMS_PRODUCTION) {
        http_response_code(403);
        exit('El sistema requiere HTTPS.');
    }
}

