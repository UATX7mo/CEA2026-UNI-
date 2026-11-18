<?php
declare(strict_types=1);
require_once __DIR__ . '/institucional.php';

function pims_cabecera(string $titulo, bool $ancha = false): void
{
    $institucion = pims_institucion();
    ?>
<!doctype html><html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo) ?> | <?= e($institucion['nombre']) ?></title>
<link rel="icon" href="assets/img/logo.png">
<link rel="stylesheet" href="assets/css/tokens.css?v=iconos-1">
    <link rel="stylesheet" href="assets/css/sistema.css?v=fondo-blanco-1">
    <link rel="stylesheet" href="assets/css/iconos.css?v=iconos-1">
<?php pims_institucional_assets(); ?>
</head><body class="cea-shell">
<?php pims_institucional_cabecera(isset($_SESSION['pims_usuario_id'])); ?>
<main class="save-page"><section class="save-card <?= $ancha ? 'management-card' : 'login-card' ?>">
<img class="login-logo" src="assets/img/logo.png" alt="Logo institucional" width="76" height="76">
<span class="save-status"><?= e($institucion['campus']) ?></span>
<h1 class="login-title"><?= e($titulo) ?></h1>
<?php
}

function pims_pie(): void
{
    ?><p class="login-footnote"><?= e(pims_institucion()['nombre']) ?> · <?= e(pims_institucion()['lema']) ?></p></section></main>
    <?php pims_institucional_pie(); ?>
    </body></html><?php
}

function pims_token_input(): void
{
    ?><input type="hidden" name="csrf_token" value="<?= e(pims_csrf_token()) ?>"><?php
}

function pims_app_cabecera(string $titulo, string $activo = ''): void
{
    $institucion = pims_institucion();
    ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> | <?= e($institucion['nombre']) ?></title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="stylesheet" href="assets/css/tokens.css?v=iconos-1">
    <link rel="stylesheet" href="assets/css/sistema.css?v=fondo-blanco-1">
    <link rel="stylesheet" href="assets/css/iconos.css?v=iconos-1">
    <?php pims_institucional_assets(); ?>
</head>
<body class="cea-shell">
<div class="app">
    <?php pims_institucional_cabecera(true); ?>
    <div class="layout layout-sin-sidebar">
        <main class="main">
            <div class="breadcrumb">Inicio / <?= e($titulo) ?></div>
            <div class="page-header"><div><h2><?= e($titulo) ?></h2><p>Centro Escolar Aparicio · <?= e($institucion['campus']) ?></p></div></div>
<?php
}

function pims_app_pie(): void
{
    ?>
        </main>
    </div>
    <?php pims_institucional_pie(); ?>
</div>
</body>
</html>
<?php
}
