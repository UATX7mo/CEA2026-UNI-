<?php
require_once __DIR__ . '/autorizacion.php';
exigirAccesoAdmon();

try {
    include_once '../complementos/conexion_fichas.php';
    $id = $_POST['id_insc'];
    $nombre_alum = $_POST['nombre_alum'];
    $ape_pat = $_POST['ape_pat'];
    $ape_mat = $_POST['ape_mat'];
    $grado = $_POST['grado'];
    $grupo = $_POST['grupo'];
    $nivel = 'SECUNDARIA';
    $curp_alum = $_POST['curp_alum'];
    $sexo = $_POST['sexo'];
    $fech_nacimiento = $_POST['nacimiento_alum'];
    $muni_nac = $_POST['muni_alum_nac'];
    $estado_nac = $_POST['estado_alum_nac'];
    $calle = $_POST['calle_alum'];
    $escuela_proce = $_POST['escuela_proce'];
    $colonia = $_POST['colo_alum'];
    $municipio = $_POST['muni_alum_rad'];
    $estado_alum_rad = $_POST['estado_alum_rad'];
    $cp = $_POST['cp'];
    $tel_casa = $_POST['tel_casa_alum'];
    $tel_cel = $_POST['tel_cel_alum'];
    $peso = $_POST['peso'];
    $talla = $_POST['talla'];
    $sangre = $_POST['sangre'];
    $enfermedades = $_POST['enfermedades'];
    $alergias = $_POST['alergias'];
    $curp_papa = $_POST['curp_papa'] ?? '';
    $nombre_papa = $_POST['nombre_papa'] ?? '';
    $nacimiento_papa = $_POST['nacimiento_papa'] ?? '';
    $estado_papa = $_POST['estado_papa'] ?? '';
    $calle_papa = $_POST['calle_papa'] ?? '';
    $colonia_papa = $_POST['colonia_papa'] ?? '';
    $estado_civ_papa = $_POST['estado_civ_papa'] ?? '';
    $ocupacion_papa = $_POST['ocupacion_papa'] ?? '';
    $escolaridad_papa = $_POST['escolaridad_papa'] ?? '';
    $tel_casa_papa = $_POST['tel_casa_papa'] ?? '';
    $email_papa = $_POST['email_papa'] ?? '';
    $curp_mama = $_POST['curp_mama'] ?? '';
    $nombre_mama = $_POST['nombre_mama'] ?? '';
    $nacimiento_mama = $_POST['nacimiento_mama'] ?? '';
    $estado_mama = $_POST['estado_mama'] ?? '';
    $calle_mama = $_POST['calle_mama'] ?? '';
    $colo_mama = $_POST['colo_mama'] ?? '';
    $civil_mama = $_POST['civil_mama'] ?? '';
    $ocupacion_mama = $_POST['ocupacion_mama'] ?? '';
    $esco_mama = $_POST['esco_mama'] ?? '';
    $tel_casa_mama = $_POST['tel_casa_mama'] ?? '';
    $email_mama = $_POST['email_mama'] ?? '';
    $curp_tutor = $_POST['curp_tutor'] ?? '';
    $nombre_tutor = $_POST['nombre_tutor'] ?? '';
    $nacimiento_tutor = $_POST['nacimiento_tutor'] ?? '';
    $estado_tutor = $_POST['estado_tutor'] ?? '';
    $calle_tutor = $_POST['calle_tutor'] ?? '';
    $colo_tutor = $_POST['colo_tutor'] ?? '';
    $estado_civ_tutor = $_POST['estado_civ_tutor'] ?? '';
    $ocupacion_tutor = $_POST['ocupacion_tutor'] ?? '';
    $esco_tutor = $_POST['esco_tutor'] ?? '';
    $tel_casa_tutor = $_POST['tel_casa_tutor'] ?? '';
    $email_tutor = $_POST['email_tutor'] ?? '';
    $religion = $_POST['religion'];
    $bautismo = $_POST['Bautismo'];
    $eucaristia = $_POST['Eucaristia'];
    $confirmacion = $_POST['Confirmacion'];

    /* $sql = "INSERT INTO primaria(
    nombre_alum, grado, nivel, curp, sexo, nacimiento, lugar_nac, estado_nac,
    esc_proce, calle, colonia, municipio, estado, cp, tel_casa, tel_cel,
    peso, talla, grado1, grado2, grado3, tipo_sangre, enfermedades,alergias,
    curp_papa, nombre_papa, fecha_nac_papa, edo_naci_papa, calle_papa,
    colonia_papa, civil_papa, ocupacion_papa, escol_papa, tel_papa, correo_papa,
    curp_mama, nombre_mama, fecha_nac_mama, nacimiento_mama, calle_mama,
    colonia_mama, civil_mama, ocupacion_mama, escolaridad_mama, tel_mama, correo_mama,
    curp_tutor, nombre_tutor, nac_tutor, nacimiento_tutor, calle_tutor,
    colonia_tutor, civil_tutor, ocupacion_tutor, escolaridad_tutor, tel_tutor, correo_tutor,
    religion, bautismo, comunion, confirmacion
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
)";
    */

    $sql = 'UPDATE secundaria SET 
    nombre_alum = ?,
    ape_pat=?,
    ape_mat=?,
    curp = ?,
    sexo = ?,
    fech_nacimiento = ?,
    mun_nac = ?,
    estado_nac = ?,
    calle = ?,
    colonia = ?,
    municipio = ?,
    estado = ?,
    cp = ?,
    tel_casa = ?,
    tel_cel = ?,
    alergias = ?,
    tipo_sangre = ?,
    peso = ?,
    talla = ?,
    enfermedades = ?,
    grado = ?,
    grupo = ?,
    esc_proce = ?,
    nivel = ?,
    religion = ?,
    bautismo = ?,
    comunion = ?,
    confirmacion = ?,
    curp_papa = ?,
    nombre_papa = ?,
    fecha_nac_papa = ?,
    edo_naci_papa = ?,
    calle_papa = ?,
    colonia_papa = ?,
    civil_papa = ?,
    ocupacion_papa = ?,
    escol_papa = ?,
    tel_papa = ?,
    correo_papa = ?,
    curp_mama = ?,
    nombre_mama = ?,
    fecha_nac_mama = ?,
    nacimiento_mama = ?,
    calle_mama = ?,
    colonia_mama = ?,
    civil_mama = ?,
    ocupacion_mama = ?,
    escolaridad_mama = ?,
    tel_mama = ?,
    correo_mama = ?,
    curp_tutor = ?,
    nombre_tutor = ?,
    nac_tutor = ?,
    nacimiento_tutor = ?,
    calle_tutor = ?,
    colonia_tutor = ?,
    civil_tutor = ?,
    ocupacion_tutor = ?,
    escolaridad_tutor = ?,
    tel_tutor = ?,
    correo_tutor = ?
    
WHERE id_insc = ?';

    $modificar = $pdo->prepare($sql);

    $modificar->execute([
        mb_strtoupper($nombre_alum),
        mb_strtoupper($ape_pat),
        mb_strtoupper($ape_mat),
        mb_strtoupper($curp_alum),
        mb_strtoupper($sexo),
        $fech_nacimiento,
        mb_strtoupper($muni_nac),
        mb_strtoupper($estado_nac),

        mb_strtoupper($calle),
        mb_strtoupper($colonia),
        mb_strtoupper($municipio),
        mb_strtoupper($estado_alum_rad),
        $cp,
        $tel_casa,
        $tel_cel,
        mb_strtoupper($alergias),
        mb_strtoupper($sangre),
        $peso,
        $talla,
        mb_strtoupper($enfermedades),

        $grado,
        mb_strtoupper($grupo),

        mb_strtoupper($escuela_proce),
        mb_strtoupper($nivel),

        mb_strtoupper($religion),
        mb_strtoupper($bautismo),
        mb_strtoupper($eucaristia),
        mb_strtoupper($confirmacion),

        mb_strtoupper($curp_papa),
        mb_strtoupper($nombre_papa),
        mb_strtoupper($nacimiento_papa),
        mb_strtoupper($estado_papa),
        mb_strtoupper($calle_papa),
        mb_strtoupper($colonia_papa),
        mb_strtoupper($estado_civ_papa),
        mb_strtoupper($ocupacion_papa),
        mb_strtoupper($escolaridad_papa),
        $tel_casa_papa,
        $email_papa,
        mb_strtoupper($curp_mama),
        mb_strtoupper($nombre_mama),
        mb_strtoupper($nacimiento_mama),
        mb_strtoupper($estado_mama),
        mb_strtoupper($calle_mama),
        mb_strtoupper($colo_mama),
        mb_strtoupper($civil_mama),
        mb_strtoupper($ocupacion_mama),
        mb_strtoupper($esco_mama),
        $tel_casa_mama,
        $email_mama,
        mb_strtoupper($curp_tutor),
        mb_strtoupper($nombre_tutor),
        mb_strtoupper($nacimiento_tutor),
        mb_strtoupper($estado_tutor),
        mb_strtoupper($calle_tutor),
        mb_strtoupper($colo_tutor),
        mb_strtoupper($estado_civ_tutor),
        mb_strtoupper($ocupacion_tutor),
        mb_strtoupper($esco_tutor),
        $tel_casa_tutor,
        $email_tutor,

        $id,
    ]);

    echo '3';
} catch (PDOException $e) {
    echo '4';
    echo 'Error: '.$e->getMessage();
}
