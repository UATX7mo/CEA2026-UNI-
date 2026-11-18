<?php
declare(strict_types=1);

/**
 * Consulta de actas registradas.
 *
 * Solo lectura. El folio y los datos del alumno se buscan con indices
 * o con LIKE, nunca con interpolacion: los valores viajan como
 * parametros vinculados.
 */

const PIMS_FILAS_POR_PAGINA = 20;

/**
 * Limpia los filtros que llegan del formulario de busqueda.
 *
 * @param array<string, mixed> $entrada
 *
 * @return array<string, string>
 */
function pims_limpiar_filtros(array $entrada): array
{
    $texto = static function (string $clave) use ($entrada): string {
        $valor = $entrada[$clave] ?? '';

        return is_string($valor) ? pims_texto($valor) : '';
    };

    return [
        'q' => mb_substr($texto('q'), 0, 80),
        'nivel' => mb_substr($texto('nivel'), 0, 40),
        'motivo' => mb_substr($texto('motivo'), 0, 40),
        'desde' => pims_fecha_valida($texto('desde')) ? $texto('desde') : '',
        'hasta' => pims_fecha_valida($texto('hasta')) ? $texto('hasta') : '',
    ];
}

/**
 * Construye el WHERE compartido por el conteo y por las filas.
 *
 * @param array<string, string> $filtros
 *
 * @return array{0: string, 1: array<string, string>}
 */
function pims_where_actas(array $filtros): array
{
    $condiciones = [];
    $parametros = [];

    if ($filtros['q'] !== '') {
        $condiciones[] = '(folio LIKE :q_folio OR matricula LIKE :q_matricula OR nombre_alumno LIKE :q_nombre)';
        foreach ([':q_folio', ':q_matricula', ':q_nombre'] as $campo) {
            $parametros[$campo] = '%' . $filtros['q'] . '%';
        }
    }

    if ($filtros['nivel'] !== '') {
        $condiciones[] = 'nivel = :nivel';
        $parametros[':nivel'] = $filtros['nivel'];
    }

    if ($filtros['motivo'] !== '') {
        $condiciones[] = 'motivo = :motivo';
        $parametros[':motivo'] = $filtros['motivo'];
    }

    if ($filtros['desde'] !== '') {
        $condiciones[] = 'fecha_emision >= :desde';
        $parametros[':desde'] = $filtros['desde'];
    }

    if ($filtros['hasta'] !== '') {
        $condiciones[] = 'fecha_emision <= :hasta';
        $parametros[':hasta'] = $filtros['hasta'];
    }

    return [$condiciones === [] ? '' : ' WHERE ' . implode(' AND ', $condiciones), $parametros];
}

/**
 * Busca actas y devuelve tambien el total, para el paginador.
 *
 * @param array<string, string> $filtros
 *
 * @return array{total: int, filas: array<int, array<string, mixed>>}
 */
function pims_consultar_actas(array $filtros, int $pagina = 1): array
{
    $pdo = pims_db();

    [$where, $parametros] = pims_where_actas($filtros);

    $contar = $pdo->prepare('SELECT COUNT(*) FROM actas_hechos' . $where);
    $contar->execute($parametros);

    $total = (int) $contar->fetchColumn();

    // El desplazamiento se acota al total para que un numero de pagina
    // enorme no genere una consulta absurda.
    $paginas = max(1, (int) ceil($total / PIMS_FILAS_POR_PAGINA));
    $pagina = min(max(1, $pagina), $paginas);

    $desplazamiento = ($pagina - 1) * PIMS_FILAS_POR_PAGINA;

    $buscar = $pdo->prepare(
        'SELECT id_acta, folio, matricula, nombre_alumno, fecha_emision,
                hora_evento, suscriptor, nivel, grado, grupo, motivo,
                hora_conclusion, capturado_por, creado_en
         FROM actas_hechos'
        . $where
        . ' ORDER BY fecha_emision DESC, id_acta DESC
           LIMIT ' . PIMS_FILAS_POR_PAGINA . ' OFFSET ' . $desplazamiento
    );

    $buscar->execute($parametros);

    return [
        'total' => $total,
        'pagina' => $pagina,
        'paginas' => $paginas,
        'filas' => $buscar->fetchAll(),
    ];
}

/**
 * Una acta por id, o null si no existe.
 *
 * @return array<string, mixed>|null
 */
function pims_obtener_acta(int $idActa): ?array
{
    if ($idActa < 1) {
        return null;
    }

    $pdo = pims_db();

    $buscar = $pdo->prepare(
        'SELECT id_acta, folio, matricula, nombre_alumno, fecha_emision,
                hora_evento, suscriptor, nivel, grado, grupo, motivo,
                hechos, acciones, hora_conclusion, capturado_por, creado_en
         FROM actas_hechos
         WHERE id_acta = ?'
    );

    $buscar->execute([$idActa]);

    $fila = $buscar->fetch();

    return $fila === false ? null : $fila;
}

/**
 * Convierte una fila de la base a los nombres que usa el documento.
 *
 * Es el puente inverso a pims_leer_acta(): asi la vista previa imprime
 * exactamente los mismos campos tanto si se acaba de capturar como si
 * se reimprime un acta guardada hace meses.
 *
 * @param array<string, mixed> $fila
 *
 * @return array<string, string>
 */
function pims_fila_a_campos(array $fila): array
{
    return [
        'folio' => (string) ($fila['folio'] ?? ''),
        'matricula' => (string) ($fila['matricula'] ?? ''),
        'nombre' => (string) ($fila['nombre_alumno'] ?? ''),
        'fecha' => (string) ($fila['fecha_emision'] ?? ''),
        'nivel' => (string) ($fila['nivel'] ?? ''),
        'grado' => (string) ($fila['grado'] ?? ''),
        'grupo' => (string) ($fila['grupo'] ?? ''),
        'motivo' => (string) ($fila['motivo'] ?? ''),
        'hechos' => (string) ($fila['hechos'] ?? ''),
        'acciones' => (string) ($fila['acciones'] ?? ''),
        'hora' => (string) ($fila['hora_evento'] ?? ''),
        'hora_conclusion' => (string) ($fila['hora_conclusion'] ?? ''),
        'suscriptor' => (string) ($fila['suscriptor'] ?? ''),
        'capturado_por' => (string) ($fila['capturado_por'] ?? ''),
    ];
}
