<?php
require_once __DIR__ . '/../autorizacion.php';
autorizarPaginaAdmon();

if (rolAdmonActual() !== 'admin') {
    header('Location: /CEA2026/admon/' . rutaInicialAdmon(rolAdmonActual()));
    exit;
}
