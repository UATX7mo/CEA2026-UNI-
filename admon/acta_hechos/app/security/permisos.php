<?php
declare(strict_types=1);

/**
 * Permisos por rol.
 *
 * Fuente unica de verdad. Las pantallas consultan pims_puede() y el
 * servidor la vuelve a exigir con pims_exigir_permiso(): ocultar un
 * boton no es proteger una accion.
 *
 *   capturista / prefecto: consulta y descarga de actas e inscripciones.
 *   director: administrador; crea, edita y elimina en ambos apartados.
 */

const PIMS_ROLES = ['capturista', 'prefecto', 'director'];

/**
 * Permiso => roles que lo tienen.
 *
 * @return array<string, array<int, string>>
 */
function pims_permisos_por_rol(): array
{
    return [
        'acta.registrar' => ['director'],
        'acta.ver_documento' => ['capturista', 'prefecto', 'director'],
        'acta.consultar' => ['capturista', 'prefecto', 'director'],
        'acta.editar' => ['director'],
        'acta.eliminar' => ['director'],
        'inscripcion.crear' => ['director'],
        'inscripcion.editar' => ['director'],
        'inscripcion.eliminar' => ['director'],
        'avisos.publicar' => ['prefecto', 'director'],
        'bloqueo.manejar' => ['prefecto', 'director'],
        'usuarios.manejar' => ['director'],
    ];
}

/**
 * Rol de la sesión activa. Si no hay sesión o el rol no existe en el
 * catálogo, se deniega todo permiso.
 */
function pims_rol_actual(): string
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        $rol = $_SESSION['pims_rol'] ?? '';

        if (is_string($rol) && in_array($rol, PIMS_ROLES, true)) {
            return $rol;
        }
    }

    return '';
}

/**
 * Nombre legible del rol, como se muestra en la interfaz.
 */
function pims_rol_texto(string $rol): string
{
    return [
        'capturista' => 'Capturista',
        'prefecto' => 'Prefecto',
        'director' => 'Director',
    ][$rol] ?? 'Capturista';
}

/**
 * Indica si el rol de la sesion tiene el permiso pedido.
 */
function pims_puede(string $permiso): bool
{
    $roles = pims_permisos_por_rol()[$permiso] ?? [];

    return !empty($_SESSION['admin']) && in_array(pims_rol_actual(), $roles, true);
}

/**
 * Corta el flujo si el rol no tiene el permiso. Pensado para acciones
 * que modifican datos, no para la interfaz.
 */
function pims_exigir_permiso(string $permiso): void
{
    if (pims_puede($permiso) === true) {
        return;
    }

    http_response_code(403);

    exit('No tienes permiso para realizar esta acción.');
}
