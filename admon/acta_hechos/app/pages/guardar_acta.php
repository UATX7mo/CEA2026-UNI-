<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/layout.php';
pims_exigir_login();
pims_exigir_permiso('acta.registrar');
$datos = null;
$idActa = null;
$errores = [];
$titulo = 'Resultado del registro';
$responderJson = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!pims_csrf_valido(pims_input($_POST, 'csrf_token'))) {
        http_response_code(403);
        $errores[] = 'La sesión expiró. Abre nuevamente el formulario.';
        if ($responderJson) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['error' => $errores[0]], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } else {
        $datos = pims_leer_acta();
        $errores = pims_validar_acta($datos);
        if ($errores === []) {
            $alumno = pims_obtener_alumno_por_matricula($datos['matricula'], $datos['nivel']);
            if ($alumno === null) {
                $errores[] = 'La matrícula no corresponde a un alumno registrado y activo.';
            } else {
                // El catálogo es la fuente oficial; no se aceptan cambios
                // enviados desde el navegador para estos datos.
                $datos['nombre'] = $alumno['nombre'];
                $datos['nivel'] = $alumno['nivel'];
                $datos['grado'] = $alumno['grado'];
                $datos['grupo'] = $alumno['grupo'];
                $errores = pims_validar_acta($datos);
            }
        }
        if ($errores !== []) {
            http_response_code(422);
            if ($responderJson) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['error' => implode(' ', $errores)], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $datos = null;
        } else {
            $pdo = pims_db();
            try {
                $pdo->beginTransaction();
                $registro = pims_registrar_acta($pdo, [
                    'matricula' => mb_strtoupper($datos['matricula']), 'nombre_alumno' => mb_strtoupper($datos['nombre']),
                    'fecha_emision' => $datos['fecha'], 'hora_evento' => $datos['hora'] ?: null,
                    'suscriptor' => mb_strtoupper($datos['suscriptor']), 'nivel' => $datos['nivel'] ?: null,
                    'grado' => $datos['grado'] ?: null, 'grupo' => $datos['grupo'] ?: null, 'motivo' => $datos['motivo'],
                    'hechos' => $datos['hechos'], 'acciones' => $datos['acciones'] ?: null,
                    'hora_conclusion' => $datos['hora_conclusion'] ?: null, 'capturado_por' => pims_usuario_actual(),
                ]);
                $pdo->commit();
                try {
                    pims_auditar('acta.registrada', $registro['folio']);
                } catch (Throwable $logError) {
                    pims_log_error($logError);
                }
                if ($responderJson) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode([
                        'ok' => true,
                        'id' => $registro['id'],
                        'folio' => $registro['folio'],
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                header('Location: guardar_acta.php?id=' . $registro['id'], true, 303);
                exit;
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                pims_log_error($exception);
                http_response_code(503);
                if ($responderJson) {
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(['error' => 'No se pudo guardar el acta. Contacta con administración.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                $datos = null;
                $errores[] = 'No se pudo guardar el acta. Contacta con administración.';
            }
        }
    }
} else {
    $idActa = filter_var(pims_input($_GET, 'id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $fila = $idActa === false ? null : pims_obtener_acta($idActa);
    if ($fila === null) {
        header('Location: sistema.php');
        exit;
    }
    $datos = pims_fila_a_campos($fila);
    $titulo = 'Acta guardada';
}
pims_cabecera($titulo);
?>
<?php if ($errores !== []): ?>
    <p role="alert">No se pudo guardar el acta.</p>
    <ul class="save-errors"><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
<?php endif; ?>
<?php if ($datos !== null): ?>
    <p role="status">El acta está registrada en el sistema.</p>
    <dl class="save-summary">
        <?php foreach (['folio' => 'Folio', 'matricula' => 'Matrícula', 'nombre' => 'Alumno', 'fecha' => 'Fecha', 'nivel' => 'Nivel', 'motivo' => 'Motivo'] as $campo => $etiqueta): ?>
        <div><dt><?= e($etiqueta) ?></dt><dd><?= e($campo === 'motivo' ? pims_motivo_texto($datos[$campo]) : $datos[$campo]) ?></dd></div>
        <?php endforeach; ?>
    </dl>
    <form action="vista-previa.php" method="post">
        <?php pims_token_input(); ?><input type="hidden" name="id" value="<?= (int) $idActa ?>">
        <button class="btn btn-preview" type="submit"><?= pims_icono('documento') ?> Ver documento</button>
    </form>
<?php endif; ?>
<p><a class="btn btn-primary" href="sistema.php"><?= pims_icono('volver') ?> Regresar al sistema</a></p>
<?php pims_pie(); ?>
