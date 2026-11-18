<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function rolAdmonActual(): string
{
    // Las cuentas existentes, previas a la asignación de roles, siguen siendo administradoras.
    $rol = strtolower($_SESSION['rol'] ?? 'admin');
    return $rol === 'administrador' ? 'admin' : $rol;
}

function rutaInicialAdmon(string $rol): string
{
    if ($rol === 'primaria') {
        return 'vista_inscripciones_prima.php';
    }
    if ($rol === 'secundaria') {
        return 'vista_inscripciones_secu.php';
    }
    if ($rol === 'preescolar') {
        return 'vista_inscripciones_prees.php';
    }
    return 'index.php';
}

function autorizarPaginaAdmon(): void
{
    if (empty($_SESSION['admin'])) {
        header('Location: ../login.php');
        exit;
    }

    $rol = rolAdmonActual();
    $pagina = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $rolesPorPagina = [
        'index.php' => ['admin'],
        'avisos.php' => ['admin'],
        'actualizar_url.php' => ['admin'],
        'agregar_url.php' => ['admin'],
        'eliminar_url.php' => ['admin'],
        'reportes.php' => ['admin', 'primaria', 'secundaria', 'preescolar'],
        'historial_reportes.php' => ['admin', 'primaria', 'secundaria', 'preescolar'],
        'reporte_alumno.php' => ['admin', 'primaria', 'secundaria', 'preescolar'],
        'lista_reportes.php' => ['admin', 'primaria', 'secundaria', 'preescolar'],
        'estado_modulo.php' => ['admin', 'primaria', 'secundaria', 'preescolar'],
        'cerrar.php' => ['admin', 'primaria', 'secundaria', 'preescolar'],
        'vista_inscripciones_prees.php' => ['admin', 'preescolar'],
        'modif_pres.php' => ['admin', 'preescolar'],
        'modificar_prees.php' => ['admin', 'preescolar'],
        'eliminar_prees.php' => ['admin', 'preescolar'],
        'vista_inscripciones_prima.php' => ['admin', 'primaria'],
        'modif_prima.php' => ['admin', 'primaria'],
        'modificar_prima.php' => ['admin', 'primaria'],
        'eliminar_prima.php' => ['admin', 'primaria'],
        'vista_inscripciones_secu.php' => ['admin', 'secundaria'],
        'modif_secu.php' => ['admin', 'secundaria'],
        'modificar_secu.php' => ['admin', 'secundaria'],
        'eliminar_secu.php' => ['admin', 'secundaria'],
    ];

    // Cualquier página nueva dentro del área administrativa queda restringida al administrador.
    $rolesPermitidos = $rolesPorPagina[$pagina] ?? ['admin'];
    if (!in_array($rol, $rolesPermitidos, true)) {
        header('Location: ' . rutaInicialAdmon($rol));
        exit;
    }
}

function exigirAccesoAdmon(): void
{
    autorizarPaginaAdmon();
}

/**
 * Devuelve la categoria real de una ruta protegida del sistema.
 * Las rutas del administrador no se pueden disfrazar seleccionando otra categoria.
 */
function categoriaRequeridaUrlModulo(string $ruta): ?string
{
    $ruta = strtolower(str_replace('\\', '/', ltrim($ruta, '/')));
    $rutasPorCategoria = [
        'preescolar' => [
            'admon/vista_inscripciones_prees.php', 'admon/modif_pres.php',
            'admon/modificar_prees.php', 'admon/eliminar_prees.php',
            'fichas2026/preescolar2026.php', 'fichas2026/busca_preescolar.php',
            'fichas2026/validar_curp_prees.php', 'fichas2026/formato_preescolar.php',
            'fichas2026/insertar_ficha_preescolar.php',
        ],
        'primaria' => [
            'admon/vista_inscripciones_prima.php', 'admon/modif_prima.php',
            'admon/modificar_prima.php', 'admon/eliminar_prima.php',
            'fichas2026/primaria2026.php', 'fichas2026/busca_primaria.php',
            'fichas2026/validar_curp_primaria.php', 'fichas2026/formato_primaria.php',
            'fichas2026/insertar_ficha_primaria.php',
        ],
        'secundaria' => [
            'admon/vista_inscripciones_secu.php', 'admon/modif_secu.php',
            'admon/modificar_secu.php', 'admon/eliminar_secu.php',
            'fichas2026/secundaria2026.php', 'fichas2026/busca_secundaria.php',
            'fichas2026/validar_curp_secundaria.php', 'fichas2026/formato_secundaria.php',
            'fichas2026/insertar_ficha_secundaria.php',
        ],
    ];

    foreach ($rutasPorCategoria as $categoria => $rutas) {
        if (in_array($ruta, $rutas, true)) {
            return $categoria;
        }
    }

    foreach (['admon/', 'fichas2026/', 'escaner/'] as $prefijoAdministrativo) {
        if (str_starts_with($ruta, $prefijoAdministrativo)) {
            return 'administrador';
        }
    }

    return null;
}
