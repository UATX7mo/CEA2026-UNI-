<?php
declare(strict_types=1);

/** Presentación institucional compartida por el portal y las pantallas de gestión. */
function pims_institucional_assets(): void
{
    ?>
    <link rel="stylesheet" href="assets/css/institucional.css?v=fondo-blanco-2">
    <link rel="stylesheet" href="assets/css/admin-compartido.css?v=footer-blanco-1">
    <link rel="stylesheet" href="assets/css/clasico.css?v=1">
    <script src="assets/vendor/bootstrap/bootstrap.bundle.min.js" defer></script>
    <script src="assets/js/institucional.js?v=clasico-1" defer></script>
    <?php
}

function pims_institucional_cabecera(bool $conSesion = false): void
{
    if ($conSesion) {
        require __DIR__ . '/admin_encabezado.php';
        return;
    }
    ?>
    <header class="cea-header"><nav class="cea-nav" aria-label="Navegación principal">
        <a href="index.php" class="cea-brand" aria-label="Inicio del Centro Escolar Aparicio"><img src="assets/img/cea/logo.png" alt="Escudo del Centro Escolar Aparicio" width="70" height="70"></a>
        <div class="cea-menu"><div class="cea-links"></div><a href="login.php" class="cea-login">Login</a></div>
    </nav></header>
    <?php
}

function pims_institucional_pie(): void
{
    $institucion = pims_institucion();
    $contacto = $institucion['contacto'];
    ?>
<footer class="cea-footer">
    <div class="cea-footer-container">
        <div class="cea-footer-grid">
            <div class="cea-footer-section">
                <h2 class="cea-text-gold"><?= e($institucion['nombre']) ?></h2>
                <p><?= e($institucion['lema_corto']) ?></p>
            </div>
            <div class="cea-footer-section">
                <h2 class="cea-text-gold">Contacto</h2>
                <p>📍 <?= e(pims_direccion_linea()) ?></p>
                <?php foreach ($contacto['telefonos'] as $telefono): ?>
                    <p>📞 <a href="tel:<?= e(str_replace(' ', '', $telefono)) ?>"><?= e($telefono) ?></a></p>
                <?php endforeach; ?>
                <p>✉️ <a href="mailto:<?= e($contacto['correo']) ?>"><?= e($contacto['correo']) ?></a></p>
            </div>
            <div class="cea-footer-section">
                <h2>Síguenos</h2>
                <?php foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube'] as $red => $nombreRed): ?>
                    <a href="<?= e($institucion['redes'][$red]) ?>" target="_blank" rel="noopener noreferrer" class="cea-social cea-social-<?= e($red) ?>"><?= pims_icono($red) ?> <?= e($nombreRed) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <hr>
        <p class="cea-copyright"><small><?= e($institucion['copyright']) ?> - Todos los derechos reservados</small></p>
    </div>
</footer>
    <?php
}
