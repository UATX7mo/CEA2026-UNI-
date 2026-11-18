<?php
$adminControlUrlsPage = true;
require_once 'encabezado.php';

require_once '../complementos/conexion.php';

$sql = "SELECT * FROM urls_sistema";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$urls = $stmt->fetchAll();
$categoriasUrls = [
    'preescolar' => ['nombre' => 'Preescolar', 'icono' => 'fa-child-reaching'],
    'primaria' => ['nombre' => 'Primaria', 'icono' => 'fa-book-open'],
    'secundaria' => ['nombre' => 'Secundaria', 'icono' => 'fa-graduation-cap'],
];
$urlsPorCategoria = array_fill_keys(array_keys($categoriasUrls), []);
$totalModulosVisibles = 0;
foreach ($urls as $url) {
    $categoria = strtolower(trim((string) ($url['categoria'] ?? '')));
    if (!isset($urlsPorCategoria[$categoria])) {
        continue;
    }
    $categoriaRequerida = categoriaRequeridaUrlModulo((string) $url['url']);
    if ($categoriaRequerida === 'administrador'
        || ($categoriaRequerida !== null && $categoriaRequerida !== $categoria)) {
        continue;
    }
    $url['categoria'] = $categoria;
    $urlsPorCategoria[$categoria][] = $url;
    $totalModulosVisibles++;
}

$iconoModulo = static function ($nombre, $url) {
    $texto = strtolower($nombre . ' ' . $url);
    $iconos = [
        'preescolar' => 'fa-child-reaching',
        'primaria' => 'fa-book-open',
        'secundaria' => 'fa-graduation-cap',
        'aviso' => 'fa-bullhorn',
        'inicio' => 'fa-house',
        'ficha' => 'fa-id-card',
        'buscar' => 'fa-magnifying-glass',
        'busca' => 'fa-magnifying-glass',
        'inscrip' => 'fa-file-pen',
        'alumno' => 'fa-user-graduate',
        'contacto' => 'fa-address-book',
        'correo' => 'fa-envelope',
        'pdf' => 'fa-file-pdf',
        'reporte' => 'fa-chart-column',
        'usuario' => 'fa-users',
        'admin' => 'fa-user-gear',
    ];

    foreach ($iconos as $palabra => $icono) {
        if (strpos($texto, $palabra) !== false) {
            return $icono;
        }
    }

    return 'fa-puzzle-piece';
};

?>

<style>
    .curls-admin-page {
        --curls-navy: #111111;
        --curls-background: #F4F6F9;
        --curls-text: #1f2937;
        --curls-muted: #778195;
        min-height: 60vh;
        padding: 32px 20px 60px;
        background: var(--curls-background);
        color: var(--curls-text);
        font-family: 'Segoe UI', system-ui, -apple-system, Arial, sans-serif;
    }

    .curls-admin-page *, .curls-admin-header * { box-sizing: border-box; }
    .curls-admin-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 14px 28px;
        background: #111111;
        box-shadow: 0 2px 10px rgba(0,0,0,.15);
        color: #fff;
    }
    .curls-brand { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .curls-brand img {
        width: 48px; height: 48px; object-fit: contain;
        border-radius: 50%; background: #fff;
        border: 2px solid rgba(255,255,255,.28);
    }
    .curls-brand h1 { margin: 0; color: #fff; font-size: 20px; font-weight: 700; line-height: 1.2; }
    .curls-brand p { margin: 3px 0 0; color: #b9c2d4; font-size: 12px; }
    .curls-header-actions { display: flex; align-items: center; gap: 10px; }
    .curls-welcome { margin: 0 6px; color: #e2e8f0; font-size: 13px; }
    .curls-menu-button {
        display: inline-flex; align-items: center; justify-content: center;
        width: 40px; height: 40px; border: 0; border-radius: 50%;
        background: rgba(255,255,255,.08); color: #fff; font-size: 18px;
    }
    .curls-menu-button:hover, .curls-menu-button:focus { background: rgba(255,255,255,.18); color: #fff; }
    .curls-menu-dropdown { min-width: 230px; padding: 7px; border: 0; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.2); }
    .curls-menu-dropdown .dropdown-item { padding: 9px 11px; border-radius: 7px; color: #1f2937; font-size: 14px; }
    .curls-menu-dropdown .dropdown-item:hover { background: #F4F6F9; color: #111111; }
    .curls-logout {
        display: inline-flex; align-items: center; gap: 7px; padding: 9px 12px;
        border: 1px solid rgba(255,255,255,.22); border-radius: 8px;
        background: transparent; color: #fff; font-size: 13px; text-decoration: none;
    }
    .curls-logout:hover { background: rgba(255,255,255,.12); color: #fff; }

    .curls-title-row, .curls-table-wrap { max-width: 1100px; margin-right: auto; margin-left: auto; }
    .curls-title-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 22px; }
    .curls-title-left { display: flex; align-items: center; gap: 14px; }
    .curls-title-icon { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 46px; width: 46px; height: 46px; border-radius: 12px; background: #111111; color: #fff; font-size: 20px; }
    .curls-title-left h2 { margin: 0; color: #111111; font-size: 26px; font-weight: 800; }
    .curls-title-left p { margin: 3px 0 0; color: #6b7280; font-size: 13px; }
    .curls-count { padding: 8px 12px; border: 1px solid #e2e5eb; border-radius: 999px; background: #fff; color: #4b5563; font-size: 13px; font-weight: 600; }
    .curls-title-actions { display: flex; align-items: center; gap: 10px; }
    .curls-add-button { display: inline-flex; align-items: center; gap: 8px; padding: 11px 16px; border: 0; border-radius: 10px; background: #111; color: #fff; font-size: 14px; font-weight: 600; }
    .curls-add-button:hover { background: #303030; color: #fff; }
    .curls-add-panel { max-width: 1100px; margin: 0 auto 18px; padding: 18px; border: 1px solid #e2e5eb; border-radius: 14px; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.05); }
    .curls-add-panel[hidden] { display: none; }
    .curls-add-panel h3 { margin: 0 0 14px; color: #111; font-size: 18px; font-weight: 700; }
    .curls-add-fields { display: grid; grid-template-columns: 1fr 1.4fr .9fr auto; gap: 12px; align-items: end; }
    .curls-add-fields label { display: block; margin-bottom: 5px; color: #4b5563; font-size: 13px; font-weight: 600; }
    .curls-add-fields input, .curls-add-fields select { width: 100%; min-height: 42px; padding: 9px 12px; border: 1px solid #d8dde5; border-radius: 8px; background: #fff; font-size: 14px; }
    .curls-add-note { margin: 12px 0 0; color: #778195; font-size: 12px; }
    .curls-search-wrap { max-width: 1100px; margin: 0 auto 14px; display: flex; align-items: center; gap: 10px; padding: 11px 15px; border: 1px solid #e2e5eb; border-radius: 10px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.035); }
    .curls-search-wrap > i { color: #6b7280; }
    .curls-search-wrap input { flex: 1; min-width: 0; padding: 2px 0; border: 0; outline: 0; background: transparent; color: #111; font-size: 14px; }
    .curls-search-wrap input::placeholder { color: #9aa2b1; }
    .curls-search-empty { max-width: 1100px; margin: 0 auto 14px; padding: 18px; border: 1px dashed #d8dde5; border-radius: 10px; color: #6b7280; text-align: center; }
    .curls-search-empty[hidden] { display: none; }
    .curls-table-wrap { overflow: hidden; border: 1px solid #e8ebf0; border-radius: 14px; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    table.curls-table { width: 100%; margin: 0; border-collapse: collapse; }
    .curls-table thead tr { border-bottom: 2px solid #111111; background: #F8F9FB; }
    .curls-table thead th { padding: 14px 18px; color: #111111; font-size: 13px; font-weight: 700; text-align: left; }
    .curls-table thead th i { margin-right: 6px; color: #111111; }
    .curls-table tbody tr { border-bottom: 1px solid #eef0f3; transition: background .15s; }
    .curls-table tbody tr:hover { background: #FAFBFD; }
    .curls-table tbody tr:last-child { border-bottom: 0; }
    .curls-table td { padding: 14px 18px; font-size: 14px; vertical-align: middle; }
    .curls-modulo { display: flex; align-items: center; gap: 12px; }
    .curls-icon { display: flex; flex: 0 0 38px; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 10px; background: #f0f0f0; color: #111111; font-size: 16px; }
    .curls-modulo-titulo { color: #1f2937; font-weight: 700; }
    .curls-url { color: #4b5563; font-family: Consolas, 'Courier New', monospace; font-size: 13px; overflow-wrap: anywhere; }
    .curls-url i { margin-right: 6px; color: #9aa2b1; }
    .btnCambiar.curls-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 112px; gap: 7px; padding: 7px 14px; border: 0; border-radius: 999px; font-size: 13px; font-weight: 600; transition: background .2s, transform .15s; }
    .btnCambiar.curls-badge:hover { transform: translateY(-1px); }
    .btnCambiar.curls-badge:focus-visible { outline: 3px solid #777777; outline-offset: 2px; }
    .btnCambiar.curls-badge.btn-success { background: #E4F7EA; color: #1E8E3E; }
    .btnCambiar.curls-badge.btn-success:hover { background: #d3f2dc; }
    .btnCambiar.curls-badge.btn-danger { background: #EDEFF2; color: #687386; }
    .btnCambiar.curls-badge.btn-danger:hover { background: #e2e5ea; }
    .curls-delete-button { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 8px 12px; border: 1px solid #e2caca; border-radius: 8px; background: #fff; color: #a33; font-size: 13px; font-weight: 600; }
    .curls-delete-button:hover { background: #fff3f3; border-color: #d9a7a7; }
    .curls-delete-button:disabled { opacity: .6; cursor: wait; }
    .btnCambiar.curls-badge::before { font-family: Arial, sans-serif; font-size: 15px; font-weight: 800; }
    .btnCambiar.curls-badge.btn-success::before { content: "✓"; }
    .btnCambiar.curls-badge.btn-danger::before { content: "−"; }
    .curls-empty { padding: 30px 0 !important; color: #9aa2b1; text-align: center; }
    .curls-table tr[hidden] { display: none !important; }
    .curls-category-row { background: #eceff3; }
    .curls-category-row th { padding: 0; border-bottom: 1px solid #dfe3e9; color: #111; font-size: 14px; font-weight: 800; text-align: left; }
    .curls-category-toggle { display: flex; width: 100%; align-items: center; justify-content: space-between; gap: 16px; padding: 12px 18px; border: 0; background: transparent; color: inherit; font: inherit; text-align: left; }
    .curls-category-toggle:hover, .curls-category-toggle:focus-visible { background: #e2e6ec; }
    .curls-category-label { display: inline-flex; align-items: center; gap: 9px; }
    .curls-category-label i { width: 18px; text-align: center; }
    .curls-category-meta { display: inline-flex; align-items: center; gap: 10px; }
    .curls-category-chevron { transition: transform .18s ease; }
    .curls-category-toggle[aria-expanded="true"] .curls-category-chevron { transform: rotate(180deg); }
    .curls-category-count { margin-left: 8px; padding: 3px 8px; border-radius: 999px; background: #fff; color: #626b78; font-size: 11px; font-weight: 600; }

    .admin-curls-body { margin: 0; background: var(--curls-background, #F4F6F9); }
    .admin-curls-body > br { display: none; }
    .admin-curls-body .footer { background: #111111; border-top: 2px solid #C9A227; }
    .admin-curls-body .footer :is(h5, p, a, small, span, strong) { color: #fff !important; }

    @media (max-width: 720px) {
        .curls-admin-header { padding: 12px 16px; }
        .curls-brand img { width: 42px; height: 42px; }
        .curls-brand h1 { font-size: 16px; }
        .curls-welcome { display: none; }
        .curls-logout { padding: 9px 10px; font-size: 0; }
        .curls-logout i { font-size: 15px; }
        .curls-admin-page { padding: 25px 14px 42px; }
        .curls-title-left h2 { font-size: 22px; }
        .curls-count { font-size: 12px; }
        .curls-add-fields { grid-template-columns: 1fr; }
        .curls-title-actions { flex-direction: column-reverse; align-items: flex-end; }
        .curls-table thead { display: none; }
        .curls-table, .curls-table tbody, .curls-table tr, .curls-table td { display: block; width: 100%; }
        .curls-table tbody tr.curls-category-row th { display: block; width: 100%; }
        .curls-table tbody tr { padding: 12px 16px; border-bottom: 1px solid #eef0f3; }
        .curls-table td { padding: 6px 0; }
        .curls-url::before { content: 'URL: '; color: #9aa2b1; font-family: 'Segoe UI', sans-serif; }
    }
</style>

<main class="curls-admin-page" id="control-urls">
  <div class="curls-title-row">
    <div class="curls-title-left">
      <span class="curls-title-icon" aria-hidden="true"><i class="fa-solid fa-link"></i></span>
      <div>
        <h2>Control de URLs</h2>
        <p>Gestiona y administra las URL de los módulos del sistema</p>
      </div>
    </div>
    <div class="curls-title-actions">
      <span class="curls-count" id="curls-count"><?= $totalModulosVisibles ?> <?= $totalModulosVisibles === 1 ? 'módulo' : 'módulos' ?></span>
      <button class="curls-add-button" id="mostrar-form-link" type="button" aria-controls="form-agregar-link" aria-expanded="false">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> Agregar link
      </button>
    </div>
  </div>

  <form class="curls-add-panel" id="form-agregar-link" hidden>
    <h3>Registrar nuevo link</h3>
    <div class="curls-add-fields">
      <div>
        <label for="nuevo-link-nombre">Nombre del módulo</label>
        <input id="nuevo-link-nombre" name="nombre" type="text" maxlength="120" required placeholder="Ej. Consulta de primaria">
      </div>
      <div>
        <label for="nuevo-link-url">Ruta de la página en el proyecto</label>
        <input id="nuevo-link-url" name="url" type="text" maxlength="255" required placeholder="Ej. http://localhost/CEA2026/admon/vista_inscripciones_secu">
      </div>
      <div>
        <label for="nuevo-link-categoria">Categoría</label>
        <select id="nuevo-link-categoria" name="categoria" required>
          <option value="" selected disabled>Selecciona una categoría</option>
          <option value="preescolar">Preescolar</option>
          <option value="primaria">Primaria</option>
          <option value="secundaria">Secundaria</option>
        </select>
      </div>
      <button class="curls-add-button" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Guardar link</button>
    </div>
    <p class="curls-add-note">Puedes pegar una URL local completa o una ruta del proyecto; si omites “.php”, se agregará automáticamente. Los links de Preescolar, Primaria o Secundaria quedan activos al registrarse. Los apartados del Administrador no se pueden agregar bajo ninguna categoría.</p>
  </form>

  <label class="curls-search-wrap" for="buscar-links">
    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
    <input id="buscar-links" type="search" autocomplete="off" placeholder="Buscar por nombre del apartado o URL" aria-label="Buscar links por nombre del apartado o URL">
  </label>
  <p class="curls-search-empty" id="sin-resultados-links" role="status" hidden>No se encontraron links que coincidan con la búsqueda.</p>

  <div class="curls-table-wrap">
    <table class="curls-table">
                <thead>
                    <tr>
                        <th scope="col"><i class="fa-solid fa-table-cells-large" aria-hidden="true"></i> Módulo</th>
                        <th scope="col"><i class="fa-solid fa-link" aria-hidden="true"></i> URL</th>
                        <th scope="col"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Estado</th>
                        <th scope="col"><i class="fa-solid fa-gear" aria-hidden="true"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody id="lista-links">
                    <?php foreach ($categoriasUrls as $claveCategoria => $datosCategoria) { ?>
                        <tr class="curls-category-row" data-category-header="<?= htmlspecialchars($claveCategoria, ENT_QUOTES, 'UTF-8') ?>">
                            <th colspan="4" scope="rowgroup">
                                <button type="button" class="curls-category-toggle" data-category-toggle="<?= htmlspecialchars($claveCategoria, ENT_QUOTES, 'UTF-8') ?>" aria-expanded="false">
                                    <span class="curls-category-label"><i class="fa-solid <?= $datosCategoria['icono'] ?>" aria-hidden="true"></i><?= htmlspecialchars($datosCategoria['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="curls-category-meta"><span class="curls-category-count"><?= count($urlsPorCategoria[$claveCategoria]) ?> <?= count($urlsPorCategoria[$claveCategoria]) === 1 ? 'módulo' : 'módulos' ?></span><i class="fa-solid fa-chevron-down curls-category-chevron" aria-hidden="true"></i></span>
                                </button>
                            </th>
                        </tr>
                        <?php if (count($urlsPorCategoria[$claveCategoria]) === 0) { ?>
                            <tr class="curls-empty-row" data-category="<?= htmlspecialchars($claveCategoria, ENT_QUOTES, 'UTF-8') ?>" hidden><td class="curls-empty" colspan="4">Sin módulos en esta categoría.</td></tr>
                        <?php } ?>
                        <?php foreach ($urlsPorCategoria[$claveCategoria] as $url) { ?>
                        <tr data-category="<?= htmlspecialchars($claveCategoria, ENT_QUOTES, 'UTF-8') ?>" hidden>
                            <td>
                                <div class="curls-modulo">
                                    <span class="curls-icon" aria-hidden="true"><i class="fa-solid <?= htmlspecialchars($iconoModulo($url['nombre'], $url['url']), ENT_QUOTES, 'UTF-8') ?>"></i></span>
                                    <span class="curls-modulo-titulo"><?= htmlspecialchars($url['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </td>
                            <td class="curls-url"><i class="fa-solid fa-link" aria-hidden="true"></i><?= htmlspecialchars($url['url'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <button
                                    type="button"
                                    class="btnCambiar curls-badge <?= $url['estado']==1 ? 'btn-success' : 'btn-danger' ?>"
                                    data-id="<?= $url['id'] ?>"
                                    data-estado="<?= $url['estado'] ?>">
                                    <?= $url['estado']==1 ? 'Activo' : 'Desactivado' ?>
                                </button>
                            </td>
                            <td>
                                <button type="button" class="curls-delete-button" data-eliminar-id="<?= (int) $url['id'] ?>" aria-label="Eliminar <?= htmlspecialchars($url['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fa-solid fa-trash" aria-hidden="true"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
  </div>
</main>

<script src="../JS/modulos.js?v=20261019"></script>
<script src="../JS/control_urls.js?v=20261104-categorias-accordion"></script>

<?php
require_once 'pie.php';
?>
