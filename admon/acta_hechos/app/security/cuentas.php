<?php
declare(strict_types=1);

/** Los tipos de la tabla compartida se traducen a los permisos internos. */
function pims_rol_desde_tipo(string $tipo): string
{
    return match (mb_strtolower(trim($tipo))) {
        'administrador' => 'director',
        'prefecto' => 'prefecto',
        'usuario', 'capturista' => 'capturista',
        default => '',
    };
}

function pims_tipo_desde_rol(string $rol): string
{
    return match ($rol) {
        'director' => 'Administrador',
        'prefecto' => 'Prefecto',
        'capturista' => 'Usuario',
        default => throw new InvalidArgumentException('Rol inválido.'),
    };
}

function pims_cuenta_por_nombre(string $nombre): ?array
{
    $stmt = pims_db()->prepare('SELECT id, nombre_user, pass, tipo_user FROM `user` WHERE nombre_user = ? LIMIT 2');
    $stmt->execute([$nombre]);
    $filas = $stmt->fetchAll();
    // Una cuenta ambigua nunca debe seleccionar credenciales arbitrariamente.
    return count($filas) === 1 ? $filas[0] : null;
}

function pims_cuenta_por_id(int $id): ?array
{
    $stmt = pims_db()->prepare('SELECT id, nombre_user, pass, tipo_user FROM `user` WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
