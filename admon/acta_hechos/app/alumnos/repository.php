<?php
declare(strict_types=1);

/**
 * Busca un alumno activo por su matrícula.
 *
 * @return array<string, string>|null
 */
function pims_obtener_alumno_por_matricula(string $matricula, string $nivel): ?array
{
    $tablas = [
        'Preescolar' => 'preescolar',
        'Primaria' => 'primaria',
        'Secundaria' => 'secundaria',
    ];
    if (preg_match('/^[A-Za-z0-9]{1,5}$/', $matricula) !== 1 || !isset($tablas[$nivel])) {
        return null;
    }

    // Cada nivel tiene su propia tabla y sus propios ID consecutivos.
    // Se requiere el nivel para evitar confundir alumnos con el mismo ID.
    $tabla = $tablas[$nivel];
    $buscar = pims_db()->prepare(
        "SELECT id_insc, nombre_alum, ape_pat, ape_mat, grado, grupo
         FROM `{$tabla}` WHERE id_insc = :matricula LIMIT 1"
    );
    $buscar->execute([':matricula' => $matricula]);

    $fila = $buscar->fetch();

    return $fila === false ? null : [
        'matricula' => (string) $fila['id_insc'],
        'nombre' => trim(implode(' ', array_filter([
            (string) $fila['nombre_alum'], (string) $fila['ape_pat'], (string) $fila['ape_mat'],
        ], static fn(string $parte): bool => trim($parte) !== ''))),
        'nivel' => $nivel,
        'grado' => rtrim(trim((string) ($fila['grado'] ?? '')), '°') . '°',
        'grupo' => (string) ($fila['grupo'] ?? ''),
    ];
}
