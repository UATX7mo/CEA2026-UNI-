<?php
declare(strict_types=1);

function pims_registrar_acta(PDO $pdo, array $datos): array
{
    $statement = $pdo->prepare(
        "INSERT INTO actas_hechos (
            folio,
            matricula,
            nombre_alumno,
            fecha_emision,
            hora_evento,
            suscriptor,
            nivel,
            grado,
            grupo,
            motivo,
            hechos,
            acciones,
            hora_conclusion,
            capturado_por
        ) VALUES (
            :folio,
            :matricula,
            :nombre_alumno,
            :fecha_emision,
            :hora_evento,
            :suscriptor,
            :nivel,
            :grado,
            :grupo,
            :motivo,
            :hechos,
            :acciones,
            :hora_conclusion,
            :capturado_por
        )"
    );

    $statement->execute([
        ':folio' => 'TMP-' . bin2hex(random_bytes(8)),
        ':matricula' => $datos['matricula'],
        ':nombre_alumno' => $datos['nombre_alumno'],
        ':fecha_emision' => $datos['fecha_emision'],
        ':hora_evento' => $datos['hora_evento'],
        ':suscriptor' => $datos['suscriptor'],
        ':nivel' => $datos['nivel'],
        ':grado' => $datos['grado'],
        ':grupo' => $datos['grupo'],
        ':motivo' => $datos['motivo'],
        ':hechos' => $datos['hechos'],
        ':acciones' => $datos['acciones'],
        ':hora_conclusion' => $datos['hora_conclusion'],
        ':capturado_por' => $datos['capturado_por'],
    ]);

    $id = (int) $pdo->lastInsertId();
    $folio = 'AH-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);

    $pdo->prepare(
        'UPDATE actas_hechos SET folio = ? WHERE id_acta = ?'
    )->execute([$folio, $id]);

    return ['id' => $id, 'folio' => $folio];
}

function pims_actualizar_acta(PDO $pdo, int $idActa, array $datos): void
{
    $statement = $pdo->prepare(
        'UPDATE actas_hechos
         SET matricula = :matricula,
             nombre_alumno = :nombre_alumno,
             fecha_emision = :fecha_emision,
             hora_evento = :hora_evento,
             suscriptor = :suscriptor,
             nivel = :nivel,
             grado = :grado,
             grupo = :grupo,
             motivo = :motivo,
             hechos = :hechos,
             acciones = :acciones,
             hora_conclusion = :hora_conclusion
         WHERE id_acta = :id_acta'
    );
    $statement->execute([
        ':id_acta' => $idActa,
        ':matricula' => $datos['matricula'],
        ':nombre_alumno' => $datos['nombre_alumno'],
        ':fecha_emision' => $datos['fecha_emision'],
        ':hora_evento' => $datos['hora_evento'],
        ':suscriptor' => $datos['suscriptor'],
        ':nivel' => $datos['nivel'],
        ':grado' => $datos['grado'],
        ':grupo' => $datos['grupo'],
        ':motivo' => $datos['motivo'],
        ':hechos' => $datos['hechos'],
        ':acciones' => $datos['acciones'],
        ':hora_conclusion' => $datos['hora_conclusion'],
    ]);
}

function pims_eliminar_acta(PDO $pdo, int $idActa): bool
{
    $statement = $pdo->prepare('DELETE FROM actas_hechos WHERE id_acta = ?');
    $statement->execute([$idActa]);

    return $statement->rowCount() === 1;
}