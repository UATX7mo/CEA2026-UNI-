<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/pages/layout.php';

pims_exigir_login();
pims_exigir_permiso('acta.editar');

$id = filter_var(pims_input($_GET, 'id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$fila = $id === false ? null : pims_obtener_acta($id);
if ($fila === null) {
    http_response_code(404);
    exit('No se encontró el acta solicitada.');
}

$datos = pims_fila_a_campos($fila);
$motivos = pims_motivos();
$errores = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!pims_csrf_valido(pims_input($_POST, 'csrf_token'))) {
        http_response_code(403);
        $errores[] = 'La sesión expiró. Recarga e intenta de nuevo.';
    } else {
        $datos = pims_leer_acta();
        $errores = pims_validar_acta($datos);
        $alumno = $errores === [] ? pims_obtener_alumno_por_matricula($datos['matricula'], $datos['nivel']) : null;
        if ($alumno === null) {
            $errores[] = 'La matrícula no corresponde a un alumno registrado y activo.';
        } else {
            $datos['nombre'] = $alumno['nombre'];
            $datos['nivel'] = $alumno['nivel'];
            $datos['grado'] = $alumno['grado'];
            $datos['grupo'] = $alumno['grupo'];
            $errores = pims_validar_acta($datos);
        }

        if ($errores === []) {
            try {
                pims_actualizar_acta(pims_db(), (int) $id, [
                    'matricula' => $datos['matricula'],
                    'nombre_alumno' => mb_strtoupper($datos['nombre']),
                    'fecha_emision' => $datos['fecha'],
                    'hora_evento' => $datos['hora'] ?: null,
                    'suscriptor' => mb_strtoupper($datos['suscriptor']),
                    'nivel' => $datos['nivel'] ?: null,
                    'grado' => $datos['grado'] ?: null,
                    'grupo' => $datos['grupo'] ?: null,
                    'motivo' => $datos['motivo'],
                    'hechos' => $datos['hechos'],
                    'acciones' => $datos['acciones'] ?: null,
                    'hora_conclusion' => $datos['hora_conclusion'] ?: null,
                ]);
                pims_auditar('acta.actualizada', (string) $id);
                $_SESSION['pims_aviso'] = 'El acta se actualizó correctamente.';
                header('Location: listado.php', true, 303);
                exit;
            } catch (Throwable $exception) {
                pims_log_error($exception);
                $errores[] = 'No se pudo actualizar el acta. Contacta con administración.';
            }
        }
    }
}

pims_cabecera('Editar acta', true);
?>
<p class="login-subtitle">Editando <?= e($datos['folio']) ?>. Los datos del alumno se toman del catálogo por matrícula.</p>
<?php if ($errores !== []): ?><p class="login-error" role="alert"><?= e(implode(' ', $errores)) ?></p><?php endif; ?>
<form method="post" action="editar_acta.php?id=<?= (int) $id ?>" class="login-form">
    <?php pims_token_input(); ?>
    <label for="matricula">Matrícula</label>
    <input id="matricula" name="matricula" value="<?= e($datos['matricula']) ?>" maxlength="5" pattern="[A-Za-z0-9]{1,5}" inputmode="text" readonly required>
    <label for="nombre">Nombre</label>
    <input id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>" readonly required>
    <label for="fecha">Fecha</label>
    <input id="fecha" name="fecha" type="date" value="<?= e($datos['fecha']) ?>" required>
    <label for="hora">Hora del acontecimiento</label>
    <input id="hora" name="hora" type="time" value="<?= e($datos['hora']) ?>">
    <label for="hora_conclusion">Hora de conclusión</label>
    <input id="hora_conclusion" name="hora_conclusion" type="time" value="<?= e($datos['hora_conclusion']) ?>">
    <label for="suscriptor">Nombre de quien suscribe</label>
    <input id="suscriptor" name="suscriptor" value="<?= e($datos['suscriptor']) ?>" required>
    <label for="nivel">Nivel</label>
    <input id="nivel" name="nivel" value="<?= e($datos['nivel']) ?>" readonly>
    <label for="grado">Grado</label>
    <input id="grado" name="grado" value="<?= e($datos['grado']) ?>" readonly>
    <label for="grupo">Grupo</label>
    <input id="grupo" name="grupo" value="<?= e($datos['grupo']) ?>" readonly>
    <label for="motivo">Motivo</label>
    <select id="motivo" name="motivo" required>
        <option value="">Seleccionar</option>
        <?php foreach ($motivos as $clave => $etiqueta): ?>
            <option value="<?= e($clave) ?>" <?= $datos['motivo'] === $clave ? 'selected' : '' ?>><?= e($etiqueta) ?></option>
        <?php endforeach; ?>
    </select>
    <label for="hechos">Hechos</label>
    <textarea id="hechos" name="hechos" rows="7" required><?= e($datos['hechos']) ?></textarea>
    <label for="acciones">Acciones</label>
    <textarea id="acciones" name="acciones" rows="4"><?= e($datos['acciones']) ?></textarea>
    <div class="save-actions">
        <a class="btn btn-secondary" href="listado.php">Cancelar</a>
        <button class="btn btn-primary" type="submit">Guardar cambios</button>
    </div>
</form>
<?php pims_pie(); ?>
