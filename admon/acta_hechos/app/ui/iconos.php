<?php
declare(strict_types=1);

/** Iconos SVG locales: nombres permitidos y contenido fijo, sin dependencias externas. */
function pims_icono(string $nombre): string
{
    static $iconos = [
        'asistencia' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2M7 17l1.5 1.5L12 15"/>',
        'reporte' => '<path d="M6 3h9l4 4v14H6zM14 3v5h5M9 17v-3M12 17v-5M15 17v-7"/>',
        'alumnos' => '<path d="m3 7 9-4 9 4-9 4zM6 9v5c4 3 8 3 12 0V9M21 7v7M7 21c0-3 2-5 5-5s5 2 5 5"/>',
        'incidencias' => '<path d="M12 3 2.5 20h19zM12 9v5M12 17h.01"/>',
        'acta' => '<path d="M14 3H5v18h14v-9M8 8h3M8 12h2M8 16h4m1-5 6-6 2 2-6 6-3 1z"/>',
        'listado' => '<rect x="4" y="3" width="16" height="18" rx="3"/><path d="M8 8h.01M11 8h5M8 12h.01M11 12h5M8 16h.01M11 16h5"/>',
        'cuentas' => '<circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 9-5m5-3 4 2v4c0 3-4 5-4 5s-4-2-4-5v-4z"/>',
        'documento' => '<path d="M14 3H5v18h14V8zM14 3v5h5M8 12h8M8 16h5"/>',
        'bloquear' => '<rect x="5" y="10" width="14" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/>',
        'desbloquear' => '<rect x="5" y="10" width="14" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 7.5-2M12 14v3"/>',
        'salir' => '<path d="M10 4H5v16h5M10 12h11m-4-4 4 4-4 4"/>',
        'entrar' => '<path d="M14 4h5v16h-5M3 12h12m-4-4 4 4-4 4"/>',
        'guardar' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h12l4 4v12a2 2 0 0 1-2 2M7 3v6h9V3M7 21v-7h10v7"/>',
        'vista' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12"/><circle cx="12" cy="12" r="3"/>',
        'imprimir' => '<path d="M7 8V3h10v5M7 17H4V9h16v8h-3M7 14h10v7H7zM17 11h.01"/>',
        'limpiar' => '<path d="M3 10a9 9 0 1 1 1 8M3 4v6h6M12 7v5l3 2"/>',
        'volver' => '<path d="m11 5-7 7 7 7M4 12h16"/>',
        'siguiente' => '<path d="m13 5 7 7-7 7M20 12H4"/>',
        'buscar' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'usuario-mas' => '<circle cx="9" cy="8" r="3"/><path d="M3 21v-2a6 6 0 0 1 12 0v2M19 8v6M16 11h6"/>',
        'usuario-menos' => '<circle cx="9" cy="8" r="3"/><path d="M3 21v-2a6 6 0 0 1 12 0v2M16 11h6"/>',
        'clave' => '<circle cx="8" cy="15" r="5"/><path d="m11.5 11.5 9-9M17 6l3 3M14 9l3 3"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
        'facebook' => '<path d="M14 22v-9h3l.5-4H14V7c0-1 .5-2 2-2h2V1h-3c-3 0-5 2-5 5v3H7v4h3v9z" fill="currentColor" stroke="none"/>',
        'youtube' => '<rect x="2" y="5" width="20" height="14" rx="4"/><path d="m10 9 5 3-5 3z"/>',
    ];
    if (!isset($iconos[$nombre])) {
        throw new InvalidArgumentException('Icono desconocido.');
    }
    return '<svg class="ui-icon" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
        . $iconos[$nombre] . '</svg>';
}