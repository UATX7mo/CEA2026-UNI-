<?php
declare(strict_types=1);

// El complemento comparte la sesión autenticada del área administrativa de CEA.
function pims_iniciar_sesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!empty($_SESSION['admin'])) {
        $_SESSION['pims_usuario'] = (string) $_SESSION['admin'];
        $_SESSION['pims_nombre'] = (string) $_SESSION['admin'];
        $_SESSION['pims_rol'] = 'director';
        $_SESSION['pims_usuario_id'] = 'admin:' . (string) $_SESSION['admin'];
    }
}

function pims_usuario_actual(): ?string
{
    pims_iniciar_sesion();
    return !empty($_SESSION['admin']) ? (string) $_SESSION['admin'] : null;
}

function pims_esta_logueado(): bool
{
    pims_iniciar_sesion();
    return !empty($_SESSION['admin']);
}

function pims_exigir_login(bool $permitirMantenimiento = false): void
{
    if (!pims_esta_logueado()) {
        http_response_code(403);
        exit('Acceso exclusivo para administración.');
    }
    $_SESSION['pims_actividad'] = time();
}

function pims_csrf_token(): string
{
    pims_iniciar_sesion();
    $_SESSION['pims_csrf'] ??= bin2hex(random_bytes(32));
    return $_SESSION['pims_csrf'];
}

function pims_csrf_valido(mixed $recibido): bool
{
    pims_iniciar_sesion();
    $esperado = $_SESSION['pims_csrf'] ?? '';
    return is_string($recibido) && $recibido !== '' && $esperado !== '' && hash_equals($esperado, $recibido);
}

function pims_cerrar_sesion(): void
{
    // El complemento no debe cerrar la sesión global del administrador.
    unset($_SESSION['pims_csrf'], $_SESSION['pims_actividad']);
}
