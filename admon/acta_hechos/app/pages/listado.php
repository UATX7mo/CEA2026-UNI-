<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/layout.php';

/**
 * Listado de actas: filtro, paginador y vista previa/reimpresion.
 */




pims_exigir_login();

// Cualquier rol con acceso al sistema puede consultar. El permiso
// acta.consultar se cumple para los tres roles de la tabla.
pims_exigir_permiso('acta.consultar');

$filtros = pims_limpiar_filtros($_GET);

$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$resultado = pims_consultar_actas($filtros, $pagina);

$institucion = pims_institucion();
$motivos = pims_motivos();
$aviso = $_SESSION['pims_aviso'] ?? '';
unset($_SESSION['pims_aviso']);
?>
<?php pims_app_cabecera('Actas registradas', 'listado'); ?>

        <span class="save-status">
            Consulta de actas
        </span>

        <h1>
            Actas de hechos registradas
        </h1>

        <p class="login-subtitle">
            <?= $resultado['total'] ?> acta(s) encontrada(s).
        </p>
        <?php if ($aviso !== ''): ?><p class="form-status" data-tipo="success" role="status"><?= e($aviso) ?></p><?php endif; ?>



        <!-- ==========
             FILTROS
        =========== -->

        <form
            method="get"
            action="listado.php"
            class="filters-form"
        >

            <div class="field-group">
                <label for="q">
                    Buscar
                </label>
                <input
                    type="search"
                    id="q"
                    name="q"
                    value="<?= e($filtros['q']) ?>"
                    placeholder="Folio, matricula o nombre"
                    autocomplete="off"
                >
            </div>


            <div class="field-group">
                <label for="nivel">
                    Nivel
                </label>
                <select id="nivel" name="nivel">
                    <option value="">Todos</option>
                    <?php foreach ($institucion['niveles'] as $nivel): ?>
                        <option
                            value="<?= e($nivel) ?>"
                            <?= $filtros['nivel'] === $nivel ? 'selected' : '' ?>
                        >
                            <?= e($nivel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div class="field-group">
                <label for="motivo">
                    Motivo
                </label>
                <select id="motivo" name="motivo">
                    <option value="">Todos</option>
                    <?php foreach ($motivos as $clave => $etiqueta): ?>
                        <option
                            value="<?= e($clave) ?>"
                            <?= $filtros['motivo'] === $clave ? 'selected' : '' ?>
                        >
                            <?= e($etiqueta) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div class="field-group">
                <label for="desde">
                    Desde
                </label>
                <input
                    type="date"
                    id="desde"
                    name="desde"
                    value="<?= e($filtros['desde']) ?>"
                >
            </div>


            <div class="field-group">
                <label for="hasta">
                    Hasta
                </label>
                <input
                    type="date"
                    id="hasta"
                    name="hasta"
                    value="<?= e($filtros['hasta']) ?>"
                >
            </div>


            <div class="filters-actions">
                <button
                    class="btn btn-secondary"
                    type="submit"
                >
                    <?= pims_icono('buscar') ?> Buscar
                </button>
                <a
                    class="btn btn-secondary"
                    href="listado.php"
                >
                    <?= pims_icono('limpiar') ?> Limpiar
                </a>
            </div>

        </form>



        <!-- ==========
             TABLA
        =========== -->

        <div class="table-scroll actas-table-container" role="region" aria-label="Actas registradas" tabindex="0">
            <table class="users-table actas-table">
                <caption class="actas-caption">Actas registradas: folio, alumno, grupo escolar y acciones disponibles.</caption>
                <thead>
                    <tr>
                        <th scope="col">Folio / Fecha</th>
                        <th scope="col">Alumno</th>
                        <th scope="col">Nivel / Grupo</th>
                        <th scope="col">Motivo</th>
                        <th scope="col">Capturado por</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado['filas'] === []): ?>
                        <tr class="actas-empty-row">
                            <td colspan="6">
                                <div class="actas-empty">
                                    <?= pims_icono('buscar') ?>
                                    <strong>No se encontraron actas</strong>
                                    <span>Prueba con otro folio, matrícula o rango de fechas.</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($resultado['filas'] as $fila): ?>
                        <tr>
                            <td class="actas-record" data-label="Folio / Fecha">
                                <strong class="actas-folio"><?= e($fila['folio']) ?></strong>
                                <time class="actas-secondary" datetime="<?= e($fila['fecha_emision']) ?>"><?= e(date('d/m/Y', strtotime($fila['fecha_emision']))) ?></time>
                            </td>
                            <td class="actas-student" data-label="Alumno">
                                <strong class="actas-student-name"><?= e($fila['nombre_alumno']) ?></strong>
                                <span class="actas-secondary">Matrícula <span class="actas-enrollment"><?= e($fila['matricula']) ?></span></span>
                            </td>
                            <td data-label="Nivel / Grupo">
                                <span class="actas-level"><?= e($fila['nivel'] ?? '—') ?></span>
                                <span class="actas-secondary">Grado <?= e($fila['grado'] ?? '—') ?> · Grupo <?= e($fila['grupo'] ?? '—') ?></span>
                            </td>
                            <td data-label="Motivo"><span class="actas-reason"><?= e(pims_motivo_texto($fila['motivo'])) ?></span></td>
                            <td class="actas-author" data-label="Capturado por"><?= e($fila['capturado_por'] ?? '—') ?></td>
                            <td class="actas-action-cell" data-label="Acciones">
                                <div class="actas-actions">
                                    <form class="preview-acta-form">
                                        <input type="hidden" name="id" value="<?= (int) $fila['id_acta'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(pims_csrf_token()) ?>">
                                        <button class="btn btn-secondary btn-mini actas-preview" type="submit" data-preview-id="<?= (int) $fila['id_acta'] ?>" aria-label="Vista previa del acta <?= e($fila['folio']) ?>">
                                            <?= pims_icono('vista') ?> Vista previa
                                        </button>
                                    </form>
                                    <?php if (pims_puede('acta.editar')): ?>
                                        <a class="btn btn-secondary btn-mini" href="editar_acta.php?id=<?= (int) $fila['id_acta'] ?>" aria-label="Editar acta <?= e($fila['folio']) ?>">Editar</a>
                                    <?php endif; ?>
                                    <?php if (pims_puede('acta.eliminar')): ?>
                                        <form method="post" action="eliminar_acta.php" class="inline-delete-form">
                                            <?php pims_token_input(); ?>
                                            <input type="hidden" name="id" value="<?= (int) $fila['id_acta'] ?>">
                                            <button class="btn btn-danger btn-mini actas-delete" type="submit" data-delete-confirm aria-label="Eliminar acta <?= e($fila['folio']) ?>">Eliminar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ==========
             PAGINADOR
        =========== -->

        <?php if ($resultado['paginas'] > 1): ?>

            <?php
            $consultaFiltros = function (int $p) use ($filtros): string {
                $parametros = array_filter($filtros, static function ($v) {
                    return $v !== '';
                });

                $parametros['pagina'] = $p;

                return http_build_query($parametros);
            };
            ?>

            <nav class="paginador">

                <a
                    class="btn btn-secondary btn-mini <?= $resultado['pagina'] <= 1 ? 'is-disabled' : '' ?>"
                    href="listado.php?<?= $consultaFiltros($resultado['pagina'] - 1) ?>"
                    aria-disabled="<?= $resultado['pagina'] <= 1 ? 'true' : 'false' ?>"
                >
                    <?= pims_icono('volver') ?> Anterior
                </a>

                <span class="paginador-texto">
                    Pagina <?= $resultado['pagina'] ?> de <?= $resultado['paginas'] ?>
                </span>

                <a
                    class="btn btn-secondary btn-mini <?= $resultado['pagina'] >= $resultado['paginas'] ? 'is-disabled' : '' ?>"
                    href="listado.php?<?= $consultaFiltros($resultado['pagina'] + 1) ?>"
                    aria-disabled="<?= $resultado['pagina'] >= $resultado['paginas'] ? 'true' : 'false' ?>"
                >
                    <?= pims_icono('siguiente') ?> Siguiente
                </a>

            </nav>

        <?php endif; ?>


<div id="previewModal" class="preview-modal" hidden>
    <div class="preview-modal-backdrop" data-close-preview></div>
    <section class="preview-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="previewModalTitle">
        <header class="preview-modal-header">
            <h2 id="previewModalTitle">Vista previa del acta</h2>
            <button type="button" class="preview-modal-close" data-close-preview aria-label="Cerrar vista previa">×</button>
        </header>
        <iframe
            id="previewFrame"
            name="listadoPreviewFrame"
            title="Vista previa del acta"
            loading="eager"
        ></iframe>
    </section>
</div>

<script src="assets/js/listado.js?v=preview-4"></script>
<?php pims_app_pie(); ?>
