<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

pims_exigir_login();
pims_exigir_permiso('acta.registrar');

header('Content-Type: application/json; charset=UTF-8');

$matricula = pims_texto(pims_input($_GET, 'matricula'));
$nivel = pims_texto(pims_input($_GET, 'nivel'));

if (preg_match('/^[A-Za-z0-9]{1,5}$/', $matricula) !== 1) {
    http_response_code(400);
    echo json_encode(['error' => 'La matrícula debe contener de 1 a 5 letras o números.'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!in_array($nivel, ['Preescolar', 'Primaria', 'Secundaria'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Selecciona un nivel educativo válido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$alumno = pims_obtener_alumno_por_matricula($matricula, $nivel);

if ($alumno === null) {
    http_response_code(404);
    echo json_encode(['error' => 'No se encontró un alumno con esa matrícula en el nivel seleccionado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['alumno' => $alumno], JSON_UNESCAPED_UNICODE);
