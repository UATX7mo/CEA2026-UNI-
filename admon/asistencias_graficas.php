<?php
require_once __DIR__ . '/autorizacion.php';
autorizarPaginaAdmon();
if (rolAdmonActual() !== 'admin') {
    header('Location: ' . rutaInicialAdmon(rolAdmonActual()));
    exit;
}

$inicioPredeterminado = (int) date('n') >= 8 ? (int) date('Y') : (int) date('Y') - 1;
$inicioPeriodo = filter_var($_GET['periodo'] ?? $inicioPredeterminado, FILTER_VALIDATE_INT);
if (!$inicioPeriodo || $inicioPeriodo < 2000 || $inicioPeriodo > 2100) $inicioPeriodo = $inicioPredeterminado;
$modo = ($_GET['modo'] ?? 'mes') === 'semana' ? 'semana' : 'mes';
$fechaInicio = sprintf('%04d-08-01', $inicioPeriodo);
$fechaFin = sprintf('%04d-07-31', $inicioPeriodo + 1);
$pdoAsistencias = new PDO('mysql:host=localhost;dbname=datos;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
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

if ($modo === 'semana') {
    $sqlAsistencia = "SELECT YEARWEEK(fecha, 3) AS periodo, COUNT(*) AS total FROM asistencia WHERE fecha BETWEEN :inicio AND :fin GROUP BY YEARWEEK(fecha, 3)";
    $sqlFalta = "SELECT YEARWEEK(fecha, 3) AS periodo, COUNT(*) AS total FROM faltas_escolares WHERE fecha BETWEEN :inicio AND :fin GROUP BY YEARWEEK(fecha, 3)";
} else {
    $sqlAsistencia = "SELECT DATE_FORMAT(fecha, '%Y-%m') AS periodo, COUNT(*) AS total FROM asistencia WHERE fecha BETWEEN :inicio AND :fin GROUP BY DATE_FORMAT(fecha, '%Y-%m')";
    $sqlFalta = "SELECT DATE_FORMAT(fecha, '%Y-%m') AS periodo, COUNT(*) AS total FROM faltas_escolares WHERE fecha BETWEEN :inicio AND :fin GROUP BY DATE_FORMAT(fecha, '%Y-%m')";
}
$stmtAsistencia = $pdoAsistencias->prepare($sqlAsistencia);
$stmtAsistencia->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
$conteoAsistencia = [];
foreach ($stmtAsistencia as $row) $conteoAsistencia[(string) $row['periodo']] = (int) $row['total'];
$stmtFaltas = $pdoAsistencias->prepare($sqlFalta);
$stmtFaltas->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
$conteoFaltas = [];
foreach ($stmtFaltas as $row) $conteoFaltas[(string) $row['periodo']] = (int) $row['total'];

$labels = [];
$asistencias = [];
$faltas = [];
$meses = ['Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio'];
if ($modo === 'mes') {
    for ($i = 0; $i < 12; $i++) {
        $timestamp = strtotime($fechaInicio . " +{$i} months");
        $clave = date('Y-m', $timestamp);
        $labels[] = $meses[$i] . ' ' . date('Y', $timestamp);
        $asistencias[] = $conteoAsistencia[$clave] ?? 0;
        $faltas[] = $conteoFaltas[$clave] ?? 0;
    }
} else {
    $inicio = new DateTime($fechaInicio);
    $fin = new DateTime($fechaFin);
    $inicio->modify('monday this week');
    $numeroSemana = 1;
    while ($inicio <= $fin) {
        $clave = $inicio->format('oW');
        $labels[] = 'Semana ' . $numeroSemana . ' · ' . $inicio->format('d/m');
        $asistencias[] = $conteoAsistencia[$clave] ?? 0;
        $faltas[] = $conteoFaltas[$clave] ?? 0;
        $inicio->modify('+1 week');
        $numeroSemana++;
    }
}

$adminControlUrlsPage = true;
require __DIR__ . '/encabezado.php';
$escape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<style>
    .graf-asistencias{min-height:70vh;padding:34px 18px 60px;background:#f4f6f9;color:#202124;font-family:system-ui,-apple-system,"Segoe UI",sans-serif}.graf-wrap{max-width:1320px;margin:auto}
    .graf-header{display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap;margin-bottom:24px}.graf-header h1{margin:0;color:#111;font-size:clamp(28px,4vw,40px);font-weight:750}.graf-header p{margin:6px 0 0;color:#667085}
    .graf-card{padding:24px;background:#fff;border:1px solid #e6e8ec;border-radius:16px;box-shadow:0 8px 24px #1111110b;margin-bottom:22px}.graf-card h2{font-size:20px;margin:0 0 18px;font-weight:700}.graf-filtros{display:flex;align-items:end;gap:12px;flex-wrap:wrap}.graf-filtros .form-group{min-width:180px}.graf-negro{background:#111;color:white;border-color:#111}.graf-negro:hover{background:#333;color:#fff}.graf-resumen{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.graf-kpi{padding:18px;border-radius:12px;background:#f8f7f2;border:1px solid #eee8d7}.graf-kpi strong{display:block;color:#111;font-size:30px}.graf-kpi span{color:#697386;font-size:14px}.graf-canvas{height:420px;position:relative}
    @media(max-width:650px){.graf-card{padding:18px}.graf-resumen{grid-template-columns:1fr}.graf-canvas{height:340px}}
</style>
<main class="graf-asistencias"><div class="graf-wrap">
  <header class="graf-header"><div><h1>Gráficas de asistencias y faltas</h1><p>Comparativo del periodo escolar por mes o semana.</p></div><a href="asistencias.php" class="btn graf-negro"><i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>Volver a asistencias</a></header>
  <section class="graf-card"><form method="get" class="graf-filtros">
    <div class="form-group"><label class="form-label" for="periodo">Periodo escolar</label><select class="form-select" name="periodo" id="periodo"><?php for ($y = (int) date('Y') + 1; $y >= (int) date('Y') - 8; $y--): ?><option value="<?= $y ?>" <?= $inicioPeriodo === $y ? 'selected' : '' ?>><?= $y ?>–<?= $y + 1 ?></option><?php endfor; ?></select></div>
    <div class="form-group"><label class="form-label" for="modo">Agrupar por</label><select class="form-select" name="modo" id="modo"><option value="mes" <?= $modo === 'mes' ? 'selected' : '' ?>>Mes</option><option value="semana" <?= $modo === 'semana' ? 'selected' : '' ?>>Semana</option></select></div>
    <button type="submit" class="btn graf-negro"><i class="fa-solid fa-chart-line me-1" aria-hidden="true"></i>Ver gráfica</button>
  </form></section>
  <section class="graf-card"><h2>Periodo <?= $inicioPeriodo ?>–<?= $inicioPeriodo + 1 ?></h2><div class="graf-resumen"><div class="graf-kpi"><strong><?= array_sum($asistencias) ?></strong><span>Registros de asistencia del escáner</span></div><div class="graf-kpi"><strong><?= array_sum($faltas) ?></strong><span>Faltas capturadas con motivo</span></div></div></section>
  <section class="graf-card"><div class="graf-canvas"><canvas id="graficaAsistencias" aria-label="Comparación de asistencias y faltas por periodo" role="img"></canvas></div></section>
</div></main>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
<script>
const contextoGrafica = document.getElementById('graficaAsistencias');
new Chart(contextoGrafica, {
  type: 'bar',
  data: {
    labels: <?= json_encode($labels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
    datasets: [
      { label: 'Asistencias del escáner', data: <?= json_encode($asistencias) ?>, backgroundColor: '#111111', borderRadius: 5 },
      { label: 'Faltas registradas', data: <?= json_encode($faltas) ?>, backgroundColor: '#d4af37', borderRadius: 5 }
    ]
  },
  options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Registros' } }, x: { ticks: { autoSkip: <?= $modo === 'semana' ? 'true' : 'false' ?>, maxRotation: 60, minRotation: 0 } } }, plugins: { legend: { position: 'bottom' }, tooltip: { enabled: true } } }
});
</script>
<?php require __DIR__ . '/pie.php'; ?>
