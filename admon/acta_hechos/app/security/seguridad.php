<?php
declare(strict_types=1);

function pims_https(): bool
{
    // No confiar en cabeceras Forwarded enviadas por cualquier cliente.
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}

function pims_input(array $datos, string $clave, string $defecto = ''): string
{
    return isset($datos[$clave]) && is_string($datos[$clave]) ? $datos[$clave] : $defecto;
}

function pims_password_errores(string $password): array
{
    $errores = [];
    if (mb_strlen($password) < 10) {
        $errores[] = 'La contraseña debe tener al menos 10 caracteres.';
    }
    if (strlen($password) > 72) {
        $errores[] = 'La contraseña debe ocupar como máximo 72 bytes.';
    }
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errores[] = 'Debe incluir mayúscula, minúscula y número.';
    }
    return $errores;
}

/** Archivo compartido por todos los procesos; lectura y escritura bajo el mismo bloqueo. */
function pims_estado(string $archivo, callable $operacion): mixed
{
    $carpeta = PIMS_STORAGE . '/security';
    if (!is_dir($carpeta) && !mkdir($carpeta, 0700, true) && !is_dir($carpeta)) {
        throw new RuntimeException('Almacenamiento de seguridad no disponible.');
    }
    $handle = fopen($carpeta . '/' . $archivo . '.json', 'c+');
    if ($handle === false) {
        throw new RuntimeException('No se pudo abrir el estado de seguridad.');
    }
    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('No se pudo bloquear el estado de seguridad.');
        }
        $contenido = stream_get_contents($handle);
        $estado = $contenido === '' ? [] : json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($estado)) {
            throw new RuntimeException('Estado de seguridad inválido.');
        }
        $resultado = $operacion($estado);
        $json = json_encode($estado, JSON_THROW_ON_ERROR);
        rewind($handle);
        if (!ftruncate($handle, 0) || fwrite($handle, $json) !== strlen($json) || !fflush($handle)) {
            throw new RuntimeException('No se pudo persistir el estado de seguridad.');
        }
        return $resultado;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function pims_reservar_intento(string $usuario, string $ip): bool
{
    return pims_estado('login-limits', static function (array &$estado) use ($usuario, $ip): bool {
        $ahora = time();
        foreach ($estado as $clave => $entrada) {
            if ($entrada['hasta'] <= $ahora) {
                unset($estado[$clave]);
            }
        }
        $limites = [hash('sha256', 'ip:' . $ip) => 30, hash('sha256', $ip . ':' . mb_strtolower($usuario)) => 5];
        foreach ($limites as $clave => $limite) {
            if (($estado[$clave]['total'] ?? 0) >= $limite) {
                return false;
            }
        }
        if (count($estado) > 10000) {
            return false;
        }
        foreach ($limites as $clave => $limite) {
            $estado[$clave] ??= ['total' => 0, 'hasta' => $ahora + 900];
            $estado[$clave]['total']++;
        }
        return true;
    });
}

function pims_mantenimiento(?bool $nuevoEstado = null): bool
{
    return pims_estado('maintenance', static function (array &$estado) use ($nuevoEstado): bool {
        if ($nuevoEstado !== null) {
            $estado = ['bloqueado' => $nuevoEstado];
        }
        return ($estado['bloqueado'] ?? false) === true;
    });
}

function pims_auditar(string $evento, ?string $objetivo = null): void
{
    $ruta = PIMS_STORAGE . '/logs';
    if (!is_dir($ruta)) {
        mkdir($ruta, 0700, true);
    }
    $linea = json_encode(['fecha' => date(DATE_ATOM), 'evento' => $evento,
        'usuario_id' => $_SESSION['pims_usuario_id'] ?? null, 'objetivo' => $objetivo], JSON_THROW_ON_ERROR);
    if (file_put_contents($ruta . '/audit.log', $linea . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
        throw new RuntimeException('No se pudo escribir la bitácora.');
    }
}

function pims_log_error(Throwable $error): void
{
    $ruta = PIMS_STORAGE . '/logs';
    if (!is_dir($ruta)) {
        @mkdir($ruta, 0700, true);
    }
    // Sin contenido de actas, contraseñas, SQL ni credenciales en la bitácora.
    $detalle = sprintf("%s %s code=%s file=%s line=%d\n", date(DATE_ATOM), get_class($error),
        $error->getCode(), basename($error->getFile()), $error->getLine());
    if (@file_put_contents($ruta . '/errors.log', $detalle, FILE_APPEND | LOCK_EX) === false) {
        error_log($detalle);
    }
}
