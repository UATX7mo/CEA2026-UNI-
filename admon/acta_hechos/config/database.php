<?php
declare(strict_types=1);

function pims_db_config(): array
{
    $config = ['host' => pims_config('PIMS_DB_HOST', '127.0.0.1'),
        'port' => pims_config('PIMS_DB_PORT', '3306'), 'name' => pims_config('PIMS_DB_NAME', 'inscripcion2026'),
        'user' => pims_config('PIMS_DB_USER', 'root'), 'pass' => pims_config('PIMS_DB_PASS'), 'charset' => 'utf8mb4'];
    if (!preg_match('/^[A-Za-z0-9_]+$/', $config['name'])
        || !preg_match('/^[A-Za-z0-9.:-]+$/', $config['host'])
        || !ctype_digit($config['port']) || (int) $config['port'] < 1 || (int) $config['port'] > 65535) {
        throw new RuntimeException('Configuración de base de datos inválida.');
    }
    if (PIMS_PRODUCTION && ($config['user'] === 'root' || $config['pass'] === '')) {
        throw new RuntimeException('Producción requiere credenciales propias de base de datos.');
    }
    return $config;
}
