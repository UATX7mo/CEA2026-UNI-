<?php
declare(strict_types=1);

// Compatibilidad con los enlaces anteriores. 307 conserva POST y su cuerpo.
$pagina = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$destino = $pagina;
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: public/' . $destino . ($query !== '' ? '?' . $query : ''),
    true, ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? 307 : 302);
exit;
