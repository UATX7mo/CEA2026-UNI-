<?php
require_once __DIR__ . '/autorizacion.php';
autorizarPaginaAdmon();

if (rolAdmonActual() !== 'admin') {
    header('Location: ' . rutaInicialAdmon(rolAdmonActual()));
    exit;
}

$pdoAsistencias = new PDO(
    'mysql:host=localhost;dbname=datos;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);
$pdoAsistencias->exec("CREATE TABLE IF NOT EXISTS faltas_escolares (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nivel VARCHAR(20) NOT NULL,
    alumno_id INT NOT NULL,
    curp VARCHAR(255) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    grado VARCHAR(50) NOT NULL DEFAULT '',
    grupo VARCHAR(30) NOT NULL DEFAULT '',
    fecha DATE NOT NULL,
    motivo TEXT NOT NULL,
    registrado_por VARCHAR(100) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY falta_alumno_dia (nivel, alumno_id, fecha),
    KEY idx_faltas_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$niveles = [
    'preescolar' => ['tabla' => 'preescolar', 'nombre' => 'Preescolar'],
    'primaria' => ['tabla' => 'primaria', 'nombre' => 'Primaria'],
    'secundaria' => ['tabla' => 'secundaria', 'nombre' => 'Secundaria'],
];

if (empty($_SESSION['csrf_asistencias'])) {
    $_SESSION['csrf_asistencias'] = bin2hex(random_bytes(32));
}

$hoy = date('Y-m-d');
$desde = $_GET['desde'] ?? date('Y-m-01');
$hasta = $_GET['hasta'] ?? $hoy;
$desdeValido = DateTime::createFromFormat('Y-m-d', $desde) !== false;
$hastaValido = DateTime::createFromFormat('Y-m-d', $hasta) !== false;
if (!$desdeValido) $desde = date('Y-m-01');
if (!$hastaValido) $hasta = $hoy;
if ($desde > $hasta) [$desde, $hasta] = [$hasta, $desde];

$mensaje = $_SESSION['mensaje_asistencias'] ?? null;
unset($_SESSION['mensaje_asistencias']);
$errorFormulario = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['csrf_asistencias'], $token)) {
        $errorFormulario = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
    } else {
        $identificador = explode('|', (string) ($_POST['alumno'] ?? ''), 2);
        $nivel = $identificador[0] ?? '';
        $alumnoId = filter_var($identificador[1] ?? '', FILTER_VALIDATE_INT);
        $fechaFalta = (string) ($_POST['fecha'] ?? '');
        $motivo = trim((string) ($_POST['motivo'] ?? ''));

        if (!isset($niveles[$nivel]) || !$alumnoId || DateTime::createFromFormat('Y-m-d', $fechaFalta) === false || $motivo === '') {
            $errorFormulario = 'Selecciona un alumno, una fecha válida y escribe el motivo de la falta.';
        } else {
            $tabla = $niveles[$nivel]['tabla'];
            $consultaAlumno = $pdoAsistencias->prepare("SELECT id_insc, nombre_alum, ape_pat, ape_mat, curp, grado, grupo FROM inscripcion2026.{$tabla} WHERE id_insc = :id LIMIT 1");
            $consultaAlumno->execute([':id' => $alumnoId]);
            $alumno = $consultaAlumno->fetch();
            if (!$alumno) {
                $errorFormulario = 'No se encontró el alumno seleccionado en el padrón.';
            } else {
                $nombreCompleto = trim(implode(' ', array_filter([
                    $alumno['nombre_alum'], $alumno['ape_pat'], $alumno['ape_mat']
                ], static fn($parte) => trim((string) $parte) !== '')));
                try {
                    $guardarFalta = $pdoAsistencias->prepare('INSERT INTO faltas_escolares (nivel, alumno_id, curp, nombre, grado, grupo, fecha, motivo, registrado_por) VALUES (:nivel, :alumno_id, :curp, :nombre, :grado, :grupo, :fecha, :motivo, :registrado_por) ON DUPLICATE KEY UPDATE curp = VALUES(curp), nombre = VALUES(nombre), grado = VALUES(grado), grupo = VALUES(grupo), motivo = VALUES(motivo), registrado_por = VALUES(registrado_por)');
                    $guardarFalta->execute([
                        ':nivel' => $nivel,
                        ':alumno_id' => $alumnoId,
                        ':curp' => $alumno['curp'],
                        ':nombre' => $nombreCompleto,
                        ':grado' => $alumno['grado'] ?? '',
                        ':grupo' => $alumno['grupo'] ?? '',
                        ':fecha' => $fechaFalta,
                        ':motivo' => $motivo,
                        ':registrado_por' => (string) $_SESSION['admin'],
                    ]);
                    $_SESSION['mensaje_asistencias'] = 'La falta quedó registrada. Si ya existía para ese día, se actualizó el motivo.';
                    header('Location: asistencias.php?desde=' . urlencode($desde) . '&hasta=' . urlencode($hasta));
                    exit;
                } catch (PDOException $e) {
                    $errorFormulario = 'No se pudo guardar la falta. Revisa la conexión con la base de datos.';
                }
            }
        }
    }
}

$alumnos = [];
foreach ($niveles as $nivel => $info) {
    $stmtAlumnos = $pdoAsistencias->query("SELECT id_insc, nombre_alum, ape_pat, ape_mat, grado, grupo FROM inscripcion2026.{$info['tabla']} ORDER BY ape_pat, ape_mat, nombre_alum");
    foreach ($stmtAlumnos as $rowAlumno) {
        $rowAlumno['nivel'] = $nivel;
        $rowAlumno['nivel_nombre'] = $info['nombre'];
        $alumnos[] = $rowAlumno;
    }
}

$consultaAsistencias = $pdoAsistencias->prepare('SELECT id, nombre, matricula, grado, grupo, fecha, hora FROM asistencia WHERE fecha BETWEEN :desde AND :hasta ORDER BY fecha DESC, hora DESC LIMIT 1000');
$consultaAsistencias->execute([':desde' => $desde, ':hasta' => $hasta]);
$registrosAsistencia = $consultaAsistencias->fetchAll();
$registrosListaProfesores = [];
$errorListaProfesores = null;
try {
    $consultaListaProfesores = $pdoAsistencias->prepare('SELECT id_asistencia, fecha, hora, docente, matricula, escolaridad FROM datos.listaprofesores WHERE fecha BETWEEN :desde AND :hasta ORDER BY fecha DESC, hora DESC LIMIT 1000');
    $consultaListaProfesores->execute([':desde' => $desde, ':hasta' => $hasta]);
    $registrosListaProfesores = $consultaListaProfesores->fetchAll();
} catch (PDOException $e) {
    error_log('No se pudieron consultar las asistencias de docentes: ' . $e->getMessage());
    $errorListaProfesores = 'No se pudieron consultar las asistencias de docentes. Verifica que la base de datos datos y la tabla listaprofesores estén disponibles en MySQL.';
}
$consultaFaltas = $pdoAsistencias->prepare('SELECT id, nivel, nombre, grado, grupo, fecha, motivo, registrado_por FROM faltas_escolares WHERE fecha BETWEEN :desde AND :hasta ORDER BY fecha DESC, nombre ASC');
$consultaFaltas->execute([':desde' => $desde, ':hasta' => $hasta]);
$registrosFaltas = $consultaFaltas->fetchAll();

$adminControlUrlsPage = true;
require __DIR__ . '/encabezado.php';
$escape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<style>
    .asistencias-page{--negro:#111;--oro:#d4af37;--fondo:#f4f6f9;min-height:70vh;padding:32px 18px 56px;background:var(--fondo);color:#202124;font-family:system-ui,-apple-system,"Segoe UI",sans-serif}
    .asistencias-wrap{max-width:1440px;margin:auto}.asistencias-head{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:24px;flex-wrap:wrap}
    .asistencias-head h1{font-size:clamp(28px,4vw,40px);font-weight:750;margin:0;color:var(--negro)}.asistencias-head p{margin:6px 0 0;color:#667085}
    .asistencias-card{background:#fff;border:1px solid #e6e8ec;border-radius:16px;box-shadow:0 8px 24px #1111110b;padding:22px;margin-bottom:22px}
    .asistencias-card h2{font-size:21px;font-weight:700;margin:0 0 16px}.asistencias-grid{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(300px,.8fr);gap:22px;align-items:start}
    .asistencias-table{font-size:14px}.asistencias-table thead th{white-space:nowrap;background:#111;color:#fff;border-color:#333}.asistencias-table td{vertical-align:middle}
    .asistencias-pill{display:inline-block;border-radius:99px;padding:4px 10px;background:#f5edd2;color:#594500;font-weight:650;font-size:12px}
    .asistencias-filter{display:flex;gap:12px;align-items:end;flex-wrap:wrap}.asistencias-filter .form-group{min-width:160px}
    .asistencias-primary{background:#111;color:#fff;border-color:#111}.asistencias-primary:hover{background:#333;color:#fff;border-color:#333}
    .asistencias-graph{background:#d4af37;color:#111;border-color:#d4af37;font-weight:700}.asistencias-graph:hover{background:#c09c25;color:#111;border-color:#c09c25}
    .asistencias-empty{padding:28px;text-align:center;color:#6b7280}.asistencias-count{font-size:13px;color:#687184;margin:0 0 12px}
    @media(max-width:900px){.asistencias-grid{grid-template-columns:1fr}.asistencias-card{padding:17px}.table-responsive{font-size:13px}}
</style>
<main class="asistencias-page">
  <div class="asistencias-wrap">
    <header class="asistencias-head">
      <div><h1>Mostrar asistencias</h1><p>Consulta los registros del escáner y captura las faltas con su motivo.</p></div>
      <a class="btn asistencias-graph" href="asistencias_graficas.php"><i class="fa-solid fa-chart-column me-2" aria-hidden="true"></i>Gráficas</a>
    </header>

    <?php if ($mensaje): ?><div class="alert alert-success" role="status"><?= $escape($mensaje) ?></div><?php endif; ?>
    <?php if ($errorFormulario): ?><div class="alert alert-danger" role="alert"><?= $escape($errorFormulario) ?></div><?php endif; ?>

    <section class="asistencias-card" aria-labelledby="filtro-title">
      <h2 id="filtro-title">Periodo de consulta</h2>
      <form method="get" class="asistencias-filter">
        <div class="form-group"><label class="form-label" for="desde">Desde</label><input class="form-control" type="date" id="desde" name="desde" value="<?= $escape($desde) ?>" required></div>
        <div class="form-group"><label class="form-label" for="hasta">Hasta</label><input class="form-control" type="date" id="hasta" name="hasta" value="<?= $escape($hasta) ?>" required></div>
        <button class="btn asistencias-primary" type="submit"><i class="fa-solid fa-filter me-1" aria-hidden="true"></i>Aplicar</button>
        <span class="asistencias-pill"><?= count($registrosAsistencia) ?> asistencias</span>
        <span class="asistencias-pill"><?= count($registrosFaltas) ?> faltas reportadas</span>
      </form>
    </section>

    <div class="asistencias-grid">
      <section class="asistencias-card" aria-labelledby="scanner-title">
        <h2 id="scanner-title"><i class="fa-solid fa-qrcode me-2" aria-hidden="true"></i>Registros del escáner</h2>
        <p class="asistencias-count">Se muestran hasta 1,000 registros del rango seleccionado.</p>
        <?php if (!$registrosAsistencia): ?><div class="asistencias-empty">No hay asistencias registradas en este periodo.</div><?php else: ?>
          <div class="table-responsive"><table class="table table-hover asistencias-table"><thead><tr><th>Fecha</th><th>Hora</th><th>Alumno</th><th>Matrícula</th><th>Grado</th><th>Grupo</th></tr></thead><tbody>
          <?php foreach ($registrosAsistencia as $registro): ?><tr><td><?= $escape($registro['fecha']) ?></td><td><?= $escape($registro['hora']) ?></td><td><?= $escape($registro['nombre']) ?></td><td><?= $escape($registro['matricula']) ?></td><td><?= $escape($registro['grado']) ?></td><td><?= $escape($registro['grupo']) ?></td></tr><?php endforeach; ?>
          </tbody></table></div>
        <?php endif; ?>
      </section>

      <section class="asistencias-card" aria-labelledby="falta-title">
        <h2 id="falta-title"><i class="fa-solid fa-pen-to-square me-2" aria-hidden="true"></i>Registrar una falta</h2>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= $escape($_SESSION['csrf_asistencias']) ?>">
          <div class="mb-3"><label class="form-label" for="alumno">Alumno</label><select class="form-select" id="alumno" name="alumno" required><option value="">Selecciona alumno y sección</option>
            <?php foreach ($alumnos as $alumno): $nombreAlumno = trim($alumno['nombre_alum'] . ' ' . $alumno['ape_pat'] . ' ' . $alumno['ape_mat']); ?>
              <option value="<?= $escape($alumno['nivel'] . '|' . $alumno['id_insc']) ?>"><?= $escape($alumno['nivel_nombre'] . ' · ' . $nombreAlumno . ' · ' . $alumno['grado'] . '° ' . $alumno['grupo']) ?></option>
            <?php endforeach; ?>
          </select></div>
          <div class="mb-3"><label class="form-label" for="fecha-falta">Fecha de la falta</label><input class="form-control" type="date" id="fecha-falta" name="fecha" value="<?= $escape($hoy) ?>" required></div>
          <div class="mb-3"><label class="form-label" for="motivo">Motivo de la falta</label><textarea class="form-control" id="motivo" name="motivo" rows="4" maxlength="2000" placeholder="Escribe el motivo reportado por la familia o el alumno" required></textarea></div>
          <button class="btn asistencias-primary w-100" type="submit"><i class="fa-solid fa-floppy-disk me-2" aria-hidden="true"></i>Guardar falta</button>
        </form>
      </section>
    </div>

    
    <section class="asistencias-card" aria-labelledby="faltas-lista-title">
      <h2 id="faltas-lista-title">Faltas y motivos capturados</h2>
      <?php if (!$registrosFaltas): ?><div class="asistencias-empty">No hay faltas capturadas en este periodo.</div><?php else: ?>
        <div class="table-responsive"><table class="table table-hover asistencias-table"><thead><tr><th>Fecha</th><th>Sección</th><th>Alumno</th><th>Grado</th><th>Grupo</th><th>Motivo</th><th>Capturó</th></tr></thead><tbody>
        <?php foreach ($registrosFaltas as $falta): ?><tr><td><?= $escape($falta['fecha']) ?></td><td><?= $escape(ucfirst($falta['nivel'])) ?></td><td><?= $escape($falta['nombre']) ?></td><td><?= $escape($falta['grado']) ?></td><td><?= $escape($falta['grupo']) ?></td><td><?= nl2br($escape($falta['motivo'])) ?></td><td><?= $escape($falta['registrado_por']) ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
      <?php endif; ?>
    </section>
  </div>
</main>
<?php require __DIR__ . '/pie.php'; ?>
