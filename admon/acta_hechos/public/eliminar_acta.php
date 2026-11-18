<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';

pims_exigir_login();
pims_exigir_permiso('acta.eliminar');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido.');
}

if (!pims_csrf_valido(pims_input($_POST, 'csrf_token'))) {
    http_response_code(403);
    exit('La sesión expiró. Recarga e intenta de nuevo.');
}

$id = filter_var(pims_input($_POST, 'id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    http_response_code(422);
    exit('Identificador de acta no válido.');
}

try {
    $eliminada = pims_eliminar_acta(pims_db(), (int) $id);
    if (!$eliminada) {
        http_response_code(404);
        exit('No se encontró el acta solicitada.');
    }
    pims_auditar('acta.eliminada', (string) $id);
    $_SESSION['pims_aviso'] = 'El acta se eliminó correctamente.';
    header('Location: listado.php', true, 303);
    exit;
} catch (Throwable $exception) {
    pims_log_error($exception);
    http_response_code(503);
    exit('No se pudo eliminar el acta. Contacta con administración.');
}
