<?php
declare(strict_types=1);

/**
 * Lectura, validacion y formato de los datos del acta de hechos.
 *
 * Todo el flujo (formulario, vista previa y guardado) usa estas funciones,
 * para que las reglas no esten duplicadas en tres archivos distintos.
 */

function pims_motivos(): array
{
    return [
        'expulsion' => 'Expulsión',
        'suspension' => 'Suspensión',
        'salida' => 'Salida extraordinaria',
        'permiso' => 'Permiso',
    ];
}

function pims_motivo_texto(string $valor): string
{
    return pims_motivos()[$valor] ?? $valor;
}

/**
 * Lee exclusivamente el cuerpo POST de un borrador.
 *
 * Los documentos registrados se consultan por identificador en la base;
 * el borrador viaja por POST: los datos de un menor no
 * deben viajar en la URL.
 */
function pims_leer_acta(): array
{
    $origen = $_POST;

    $dato = static function (string $clave) use ($origen): string {
        return pims_texto(pims_input($origen, $clave));
    };

    $fecha = $dato('fecha');

    return [
        'folio' => $dato('folio'),
        'matricula' => $dato('matricula'),
        'nombre' => $dato('nombre'),
        'fecha' => $fecha,
        'nivel' => $dato('nivel'),
        'grado' => $dato('grado'),
        'grupo' => $dato('grupo'),
        'motivo' => $dato('motivo'),
        'hechos' => $dato('hechos'),
        'acciones' => $dato('acciones'),
        'hora' => $dato('hora'),
        'hora_conclusion' => $dato('hora_conclusion'),
        'suscriptor' => $dato('suscriptor'),
        'capturado_por' => $dato('capturado_por'),
    ];
}

/**
 * Revisa el acta y devuelve la lista de errores.
 *
 * @return array<string, string>
 */
function pims_validar_acta(array $datos): array
{
    $errores = [];

    $obligatorios = [
        'matricula' => 'La matricula del alumno es obligatoria.',
        'nombre' => 'El nombre del alumno es obligatorio.',
        'fecha' => 'La fecha de emision es obligatoria.',
        'hechos' => 'El relato de los hechos es obligatorio.',
        'suscriptor' => 'El nombre de quien suscribe es obligatorio.',
    ];

    foreach ($obligatorios as $campo => $mensaje) {
        if ($datos[$campo] === '') {
            $errores[] = $mensaje;
        }
    }

    if ($datos['fecha'] !== '' && !pims_fecha_valida($datos['fecha'])) {
        $errores[] = 'La fecha de emision no tiene un formato valido.';
    }

    if ($datos['matricula'] !== '' && preg_match('/^[A-Za-z0-9]{1,5}$/', $datos['matricula']) !== 1) {
        $errores[] = 'La matrícula debe contener de 1 a 5 letras o números.';
    }

    if ($datos['motivo'] === '') {
        $errores[] = 'Debes seleccionar el motivo del acta.';
    } elseif (!array_key_exists($datos['motivo'], pims_motivos())) {
        $errores[] = 'El motivo del acta no es valido.';
    }

    $niveles = pims_institucion()['niveles'];

    if ($datos['nivel'] !== '' && !in_array($datos['nivel'], $niveles, true)) {
        $errores[] = 'El nivel educativo no es valido.';
    }

    foreach (['hora', 'hora_conclusion'] as $campo) {
        if ($datos[$campo] !== '' && preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $datos[$campo]) !== 1) {
            $errores[] = 'La hora no tiene un formato valido.';
        }
    }

    $limites = ['matricula' => 5, 'nombre' => 255, 'suscriptor' => 255, 'nivel' => 80,
        'grado' => 40, 'grupo' => 40, 'motivo' => 60];
    foreach ($limites as $campo => $maximo) {
        if (mb_strlen(in_array($campo, ['matricula', 'nombre', 'suscriptor'], true) ? mb_strtoupper($datos[$campo]) : $datos[$campo]) > $maximo) {
            $errores[] = "El campo {$campo} admite hasta {$maximo} caracteres.";
        }
    }
    foreach (['hechos', 'acciones'] as $campo) {
        if (mb_strlen($datos[$campo]) > 10000 || strlen($datos[$campo]) > 60000) {
            $errores[] = "El campo {$campo} excede el tamaño permitido.";
        }
    }
    $grados = ['Preescolar' => ['1°', '2°', '3°'],
        'Primaria' => ['1°', '2°', '3°', '4°', '5°', '6°'], 'Secundaria' => ['1°', '2°', '3°']];
    if ($datos['grado'] !== '' && !in_array($datos['grado'], $grados[$datos['nivel']] ?? [], true)) {
        $errores[] = 'El grado no corresponde al nivel seleccionado.';
    }
    if ($datos['grupo'] !== '' && preg_match('/^[A-Z]$/u', $datos['grupo']) !== 1) {
        $errores[] = 'El grupo debe ser una letra mayúscula.';
    }
    foreach ($datos as $valor) {
        if (!mb_check_encoding($valor, 'UTF-8') || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $valor)) {
            $errores[] = 'El formulario contiene caracteres no válidos.';
            break;
        }
    }
    return $errores;
}

function pims_fecha_valida(string $fecha): bool
{
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha, $partes) !== 1) {
        return false;
    }

    return (int) $partes[1] >= 1000 && checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1]);
}


/* =========================================
   FORMATO PARA EL DOCUMENTO
   ========================================= */

/**
 * 2026-09-08 -> 8 de septiembre de 2026
 */
function pims_fecha_texto(string $fecha): string
{
    if (!pims_fecha_valida($fecha)) {
        return '';
    }

    $partes = explode('-', $fecha);

    $meses = [
        1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
    ];

    return (int) $partes[2] . ' de ' . $meses[(int) $partes[1]] . ' de ' . $partes[0];
}

/**
 * 2026-09-08 -> dia 08, anio 2026 (as usados en el formato oficial).
 */
function pims_fecha_parte(string $fecha, string $parte): string
{
    if (!pims_fecha_valida($fecha)) {
        return '';
    }

    $partes = explode('-', $fecha);

    return $parte === 'dia' ? $partes[2] : $partes[0];
}

/**
 * 09:35 -> 09 | 35. El formato oficial separa hora de minutos.
 */
function pims_hora_parte(string $hora, string $parte): string
{
    if (preg_match('/^(\d{2}):(\d{2})$/', $hora, $partes) !== 1) {
        return '';
    }

    return $parte === 'hora' ? $partes[1] : $partes[2];
}

/**
 * Quita saltos de linea y espacios doubles para el documento.
 */
function pims_texto_documento(string $valor): string
{
    $valor = preg_replace('/\R+/', ' ', $valor) ?? $valor;

    return trim(preg_replace('/[ \t]{2,}/', ' ', $valor) ?? $valor);
}
