<?php
require_once __DIR__ . '/../admon/autorizacion.php';
if (empty($_SESSION['admin']) || !in_array(rolAdmonActual(), ['admin', 'primaria', 'secundaria', 'preescolar'], true)) {
    if (empty($_SESSION['admin'])) {
        header('Location: /CEA2026/login.php');
    } else {
        header('Location: /CEA2026/admon/' . rutaInicialAdmon(rolAdmonActual()));
    }
    exit;
}

require_once __DIR__ . '/../complementos/conexion_fichas.php';
$rolReporte = rolAdmonActual();
$categoriasReporte = [
    'preescolar' => 'Preescolar',
    'primaria' => 'Primaria',
    'secundaria' => 'Secundaria',
];
$nivelSeleccionado = $rolReporte === 'admin'
    ? strtolower(trim((string) ($_GET['nivel'] ?? '')))
    : $rolReporte;
if (!isset($categoriasReporte[$nivelSeleccionado])) {
    $nivelSeleccionado = '';
}
$conteosReporte = array_fill_keys(array_keys($categoriasReporte), 0);
foreach ($pdo->query('SELECT nivel, COUNT(*) AS total FROM reportes_alumnos GROUP BY nivel')->fetchAll(PDO::FETCH_ASSOC) as $conteo) {
    if (isset($conteosReporte[$conteo['nivel']])) {
        $conteosReporte[$conteo['nivel']] = (int) $conteo['total'];
    }
}
$resultado = [];
if ($nivelSeleccionado !== '') {
    $consultaReportes = $pdo->prepare('SELECT * FROM reportes_alumnos WHERE nivel = :nivel ORDER BY fecha_registro DESC, id DESC');
    $consultaReportes->execute([':nivel' => $nivelSeleccionado]);
    $resultado = $consultaReportes->fetchAll(PDO::FETCH_ASSOC);
}
$adminBasePath = true;
$adminControlUrlsPage = true;
require __DIR__ . '/../admon/encabezado.php';
?>

<div class="container py-5">
    <div class="card shadow border-0 p-4 card-dark-pro">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="titulo-gold m-0">Reportes de alumnos</h2>
                <?php if ($nivelSeleccionado !== '') { ?>
                <span class="text-muted">Categoría: <?= htmlspecialchars($categoriasReporte[$nivelSeleccionado], ENT_QUOTES, 'UTF-8') ?></span>
                <?php } else { ?>
                <span class="text-muted">Selecciona una categoría para consultar sus reportes.</span>
                <?php } ?>
            </div>
            <a href="/CEA2026/admon/reportes.php" class="btn btn-gold">
                <i class="fas fa-plus me-2"></i> Nuevo Reporte
            </a>
        </div>

        <?php if ($rolReporte === 'admin' && $nivelSeleccionado === '') { ?>
        <div class="row g-3">
            <?php foreach ($categoriasReporte as $claveNivel => $nombreNivel) { ?>
            <div class="col-md-4">
                <a class="card h-100 text-decoration-none text-dark shadow-sm" href="/CEA2026/admon/historial_reportes.php?nivel=<?= urlencode($claveNivel) ?>">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="h5 mb-1"><?= htmlspecialchars($nombreNivel, ENT_QUOTES, 'UTF-8') ?></h3>
                            <span class="text-muted">Ver reportes de esta categoría</span>
                        </div>
                        <span class="badge bg-dark rounded-pill fs-6"><?= $conteosReporte[$claveNivel] ?></span>
                    </div>
                </a>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <?php if ($rolReporte === 'admin') { ?>
        <a href="/CEA2026/admon/historial_reportes.php" class="btn btn-outline-dark mb-3"><i class="fas fa-arrow-left me-2" aria-hidden="true"></i>Categorías</a>
        <?php } ?>
        <div class="table-responsive">
            <table class="table table-dark table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Alumno(a)</th>
                        <th>Grado / Grupo</th>
                        <th>Fecha del Reporte</th>
                        <th>Reportó</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado): ?>
                        <?php foreach ($resultado as $row): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['nombre_alumno']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['grado'] . ' ' . $row['grupo']); ?></td>
                                <td><?php echo htmlspecialchars($row['fecha_reporte'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['quien_reporta'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <details>
                                        <summary class="text-warning" role="button">Ver reporte</summary>
                                        <p class="mt-2 mb-0 text-white" style="white-space: pre-wrap"><?= htmlspecialchars($row['conducta'], ENT_QUOTES, 'UTF-8') ?></p>
                                    </details>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Todavía no hay reportes guardados en esta categoría.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </div>
</div>

<?php
require __DIR__ . '/../admon/pie.php';
?>
