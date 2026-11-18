<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

pims_exigir_login();
pims_exigir_permiso('acta.ver_documento');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !pims_csrf_valido(pims_input($_POST, 'csrf_token'))) {
    http_response_code(403);
    exit('La sesión expiró. Abre de nuevo el formulario.');
}

$institucion = pims_institucion();
$contacto = $institucion['contacto'];
$entrada = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? $_POST : $_GET;
$id = filter_var(pims_input($entrada, 'id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$guardada = array_key_exists('id', $entrada);
if ($guardada) {
    $fila = $id === false ? null : pims_obtener_acta($id);
    if ($fila === null) {
        http_response_code(404);
        exit('No se encontró el acta solicitada.');
    }
    $datos = pims_fila_a_campos($fila);
    pims_auditar('acta.consultada', $datos['folio']);
} else {
    $datos = pims_leer_acta();
    $datos['folio'] = '';
    $datos['capturado_por'] = pims_usuario_actual() ?? '';
}

$errores = pims_validar_acta($datos);

/** @param array<string, string> $datos */
function campo(array $datos, string $clave, string $alternativa = '—'): string
{
    $valor = pims_texto($datos[$clave] ?? '');
    return e($valor !== '' ? $valor : $alternativa);
}

$folio = $datos['folio'] !== '' ? $datos['folio'] : 'Pendiente de asignación';
$fecha = pims_fecha_texto($datos['fecha']);
$hora = pims_hora_parte($datos['hora'], 'hora');
$minutos = pims_hora_parte($datos['hora'], 'minutos');
$membrete = $institucion['membretes'][$datos['nivel']] ?? '';
$imprimirAutomaticamente = pims_input($_GET, 'imprimir') === '1';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acta de hechos - <?= e($guardada ? $folio : 'Borrador') ?></title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="stylesheet" href="assets/css/tokens.css?v=iconos-1">
    <link rel="stylesheet" href="assets/css/vista-pdf.css?v=bold-headings-1">
    <link rel="stylesheet" href="assets/css/iconos.css?v=iconos-1">
</head>
<body data-guardada="<?= $guardada ? 'true' : 'false' ?>"
      data-imprimir="<?= $imprimirAutomaticamente ? 'true' : 'false' ?>"
      data-folio="<?= e($folio) ?>"
      data-acta-id="<?= $guardada && $id !== false ? (int) $id : '' ?>"
      data-nivel="<?= e($datos['nivel']) ?>"
      data-membretes="<?= e(json_encode($institucion['membretes'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) ?>"
      data-motivos="<?= e(json_encode(pims_motivos(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) ?>">

<header class="preview-header">
    <div>
        <span class="preview-label">Vista previa</span>
        <h1>Acta de hechos</h1>
        <p>Tamaño carta. Para descargar el archivo, elige «Guardar como PDF» en la ventana de impresión.</p>
    </div>
    <div class="preview-actions">
        <div class="preview-zoom" role="group" aria-label="Controles de zoom">
            <button class="preview-tool" type="button" id="zoomOut" aria-label="Reducir zoom">−</button>
            <output id="zoomValue" class="preview-zoom-value">100%</output>
            <button class="preview-tool" type="button" id="zoomIn" aria-label="Aumentar zoom">+</button>
        </div>
        <button class="btn btn-secondary" type="button" id="downloadDocument" <?= $guardada ? '' : 'disabled' ?>>
            <?= pims_icono('documento') ?> Descargar
        </button>
        <button class="btn btn-primary" type="button" id="printDocument" disabled>
            <?= pims_icono('imprimir') ?> Imprimir
        </button>
    </div>
</header>

<?php if (!$guardada): ?>
<div class="preview-warnings"><strong>Borrador · Acta sin registrar</strong><p>El folio se asignará al guardar.</p></div>
<?php endif; ?>
<?php if ($errores !== []): ?>
<div class="preview-warnings" data-preview-warnings>
    <strong>Faltan datos para generar el documento</strong>
    <ul><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>
<p class="preview-warnings" id="levelWarning" <?= $membrete !== '' ? 'hidden' : '' ?>>
    No hay un nivel educativo seleccionado. El documento se muestra sin membrete de nivel.
</p>
<p class="preview-warnings" id="printError" role="alert" hidden></p>
<noscript><p class="preview-warnings">Activa JavaScript para distribuir el acta en páginas y generar el PDF.</p></noscript>

<main class="document-area" aria-label="Documento imprimible">
    <!-- Fuente única de contenido. Las páginas se componen sin modificar los datos guardados. -->
    <article class="document-source" id="documentSource">
        <h1 class="document-title" data-document-block>ACTA DE HECHOS</h1>

        <p class="document-paragraph" data-document-block>
            En el municipio de <?= e($contacto['ciudad']) ?>, <?= e($contacto['estado']) ?>,
            siendo las <span data-preview-field="hora_hora"><?= e($hora !== '' ? $hora : '—') ?></span>
            horas con <span data-preview-field="hora_minutos"><?= e($minutos !== '' ? $minutos : '—') ?></span>
            minutos del día <span data-preview-field="fecha"><?= e($fecha !== '' ? $fecha : '—') ?></span>,
            reunidos en el <?= e($institucion['nombre']) ?>, ubicado en <?= e($contacto['direccion']) ?>,
            el (la) suscrito(a) <span data-preview-field="suscriptor"><?= campo($datos, 'suscriptor') ?></span>,
            en calidad de director(a) del centro educativo antes mencionado, nivel
            <span data-preview-field="nivel"><?= campo($datos, 'nivel') ?></span>, actuando como responsable del nivel.
        </p>

        <section class="document-block" data-document-block>
            <p>En este momento se hace constar la presencia de la alumna o alumno:
                <span data-preview-field="nombre"><?= campo($datos, 'nombre') ?></span>,
                del grado <span data-preview-field="grado"><?= campo($datos, 'grado') ?></span>
                y grupo <span data-preview-field="grupo"><?= campo($datos, 'grupo') ?></span>
                de este centro educativo, quien se encuentra presente en la dirección de este plantel,
                en virtud de que:</p>
            <p class="reason" data-preview-field="motivo"><?= e(pims_motivo_texto($datos['motivo']) ?: '—') ?></p>
        </section>

        <section class="document-block narrative-block" data-document-block data-split-field="hechos">
            <h2>De acuerdo con los siguientes hechos:</h2>
            <p class="narrative" data-preview-field="hechos"><?= campo($datos, 'hechos') ?></p>
        </section>

        <section class="document-block narrative-block" data-document-block data-split-field="acciones">
            <h2>En virtud de lo anterior, se procede a:</h2>
            <p class="narrative" data-preview-field="acciones"><?= campo($datos, 'acciones') ?></p>
        </section>

        <section class="document-block closing-block" data-document-block>
            <p>La presente acta se da por concluida el día
                <span data-preview-field="fecha"><?= e($fecha !== '' ? $fecha : '—') ?></span>
                a las <span data-preview-field="hora_conclusion"><?= campo($datos, 'hora_conclusion') ?></span> horas.
                Firmando al calce los que en ella intervinieron y libremente lo hicieron:</p>
            <div class="signature"><span>Firma</span></div>
        </section>
    </article>
    <div id="documentPages" hidden></div>
</main>

<template id="pageTemplate">
    <article class="pdf-page">
        <img class="letterhead" alt="" aria-hidden="true" <?= $membrete !== '' ? 'src="' . e($membrete) . '"' : 'hidden' ?>>
        <div class="plain-letterhead" <?= $membrete !== '' ? 'hidden' : '' ?>><?= e($institucion['nombre']) ?></div>
        <div class="page-metadata">
            <span>Folio: <?= e($folio) ?></span>
            <span>Matrícula: <span data-page-matricula><?= campo($datos, 'matricula') ?></span></span>
        </div>
        <div class="document-content"></div>
        <?php if (!$guardada): ?><div class="draft-mark" aria-hidden="true">BORRADOR · SIN REGISTRAR</div><?php endif; ?>
        <footer class="page-number"></footer>
    </article>
</template>

<script src="assets/js/vista-previa.js?v=flow-1"></script>
</body>
</html>
