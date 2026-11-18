<?php
include 'encabezado.php';
include '../complementos/conexion_fichas.php';

// include_once 'conexion1.php';
// $id=1;
$id = $_GET['id_insc'];
// LEER
$sql_leer = 'SELECT * FROM secundaria WHERE id_insc = ?';
$gsent = $pdo->prepare($sql_leer);
$gsent->execute([$id]);
$resultado = $gsent->fetchAll();

// var_dump($resultado);

?>


<h1 class="text-center">Monitor Inscripciones Secundaria</h1>


<?php foreach ($resultado as $dato) { ?>

<div class="container">
    <h1 class="text-center">Datos del Alumno</h1>

    <form id="actualizar" method="post">
        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">ID ALUMNO</label>
                <input type="text" class="form-control " name="id_insc" id="" value="<?php echo $dato['id_insc']; ?>">
            </div>
        </div>

        <div class=" row">
            <div class="col-md-4">
                <label for="" class="form-label">Nombre(s): </label>
                <input type="text" class="form-control" name="nombre_alum" id="nombre_alum"
                    value="<?php echo $dato['nombre_alum']; ?>">

            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Apellido Paterno: </label>
                <input type="text" class="form-control" name="ape_pat" id="ape_pat"
                    value="<?php echo $dato['ape_pat']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Apellido Materno: </label>
                <input type="text" class="form-control" name="ape_mat" id="" value="<?php echo $dato['ape_mat']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">CURP:</label>
                <input type="text" class="form-control" name="curp_alum" value="<?php echo $dato['curp']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Sexo:</label>
                <input type="text" name="sexo" id="" class="form-control" value="<?php echo $dato['sexo']; ?>">
            </div>
            <div class="col-md-4">
                <label for="">Fecha Nacimiento</label>
                <input type="date" name="nacimiento_alum" id="" class="form-control"
                    value="<?php echo $dato['fech_nacimiento']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Municipio de Nacimiento:</label>
                <input type="text" class="form-control" name="muni_alum_nac" id=""
                    value="<?php echo $dato['mun_nac']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Estado de nacimiento:</label>
                <input type="text" name="estado_alum_nac" id="" class="form-control"
                    value="<?php echo $dato['estado_nac']; ?>">
            </div>

        </div>

        <!-- Aqui terminan los datos de dpersonales -->

        <!-- Aqui inician los datos de domicilio -->
        <div class="row">
            <h3 class="text-center">Domicilio</h3>
            <div class="col-md-4">
                <label for="" class="form-label">Calle Y Numero</label>
                <input type="text" class="form-control" name="calle_alum" value="<?php echo $dato['calle']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Colonia</label>
                <input type="text" class="form-control" name="colo_alum" value="<?php echo $dato['colonia']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Municipio</label>
                <input type="text" class="form-control" name="muni_alum_rad" value="<?php echo $dato['municipio']; ?> ">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Estado</label>
                <input type="text" class="form-control" name="estado_alum_rad" value="<?php echo $dato['estado']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Codigo Postal</label>
                <input type="text" class="form-control" name="cp" value="<?php echo $dato['cp']; ?> ">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Tel. Casa</label>
                <input type="text" class="form-control" name="tel_casa_alum" value="<?php echo $dato['tel_casa']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Tel. Celular</label>
                <input type="text" class="form-control" name="tel_cel_alum" value="<?php echo $dato['tel_cel']; ?>">
            </div>
        </div>

        <div class="row">
            <h3 class="text-center">Datos de Salud</h3>
            <div class="col-md-4">
                <label for="" class="form-label">Alergias</label>
                <input type="text" class="form-control" name="alergias" value="<?php echo $dato['alergias']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Tipo de Sangre</label>
                <input type="text" class="form-control" name="sangre" value="<?php echo $dato['tipo_sangre']; ?>">

            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Peso</label>
                <input type="text" class="form-control" name="peso" value="<?php echo $dato['peso']; ?>">
            </div>
        </div>
        <div class="row">


            <div class="col-md-4">
                <label for="" class="form-label">Altura</label>
                <input type="text" class="form-control" name="talla" value="<?php echo $dato['talla']; ?>">
            </div>

            <div class="col-md-4">
                <label for="" class="form-label">Enfermedades Cronicas</label>
                <input type="text" class="form-control" name="enfermedades" value="<?php echo $dato['enfermedades']; ?>">
            </div>
        </div>




        <!-- Aqui terminan los datos de domicilio -->
        <!-- Aqui inician los datos escolares -->
        <div class="row">
            <h3 class="text-center">Datos Escolares</h3>
            <div class="row">
                <div class="col-md-4">
                    <label for="" class="form-label">Grado:</label>

                    <input type="text" value="<?php echo $dato['grado']; ?>" name="grado" id="" class="form-control">

                </div>
                <div class="col-md-4">
                    <label for="" class="form-label">Grupo:</label>
                    <input type="text" value="<?php echo $dato['grupo']; ?>" name="grupo" id="" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="" class="form-label">Escuela de procedencia</label>
                    <input type="text" name="escuela_proce" class="form-control"
                        value="<?php echo $dato['esc_proce']; ?>">
                </div>
            </div>

            <!-- Aqui terminan los datos escolares -->
            <!-- Aqui inician los datos religiosos -->
            <div class="row">
                <h3 class="text-center">Datos Religiosos</h3>
                <div class="col-md-4">
                    <label>Religion que Profesa:</label>
                    <input type="text" class="form-control" name="religion" value="<?php echo $dato['religion']; ?>">
                </div>
                <div class="col-md-4">

                    <div class="form-check form-check-inline">

                        <label class="form-check-label" for="inlineRadio1">Bautismo</label>
                        <input type="text" name="Bautismo" id="" value="<?php echo $dato['bautismo']; ?>"><br>
                    </div>
                    <div class="form-check form-check-inline">

                        <label class="form-check-label" for="inlineRadio2">Comunion</label>
                        <input type="text" name="Confirmacion" id="" value="<?php echo $dato['confirmacion']; ?>"><br>
                    </div>
                    <div class="form-check form-check-inline">

                        <label class="form-check-label" for="inlineRadio3">Confirmación</label>
                        <input type="text" name="Eucaristia" id="" value="<?php echo $dato['comunion']; ?>"><br>
                    </div>
                    <br><br><br>
                </div>
            </div>
            <!-- Aqui terminan los datos religiosos -->

        </div>
        <!-- Aqui inician los datos del padre -->
        <h3 class="text-center">Datos del padre</h3>
        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">CURP</label>
                <input type="text" class="form-control" name="curp_papa" value="<?php echo $dato['curp_papa']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Nombre del Padre: </label>
                <input type="text" class="form-control" name="nombre_papa" id=""
                    value="<?php echo $dato['nombre_papa']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Fecha Nacimiento</label>
                <input type="date" name="nacimiento_papa" id="" class="form-control"
                    value="<?php echo $dato['fecha_nac_papa']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Lugar de Nacimiento</label>
                <input type="text" name="estado_papa" id="" class="form-control"
                    value="<?php echo $dato['edo_naci_papa']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Calle Y Numero</label>
                <input type="text" class="form-control" name="calle_papa" value="<?php echo $dato['calle_papa']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Colonia</label>
                <input type="text" class="form-control" name="colonia_papa" value="<?php echo $dato['colonia_papa']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Estado Civil</label>
                <input type="text" name="estado_civ_papa" id="" class="form-control"
                    value="<?php echo $dato['civil_papa']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Ocupacion</label>
                <input type="text" class="form-control" name="ocupacion_papa"
                    value="<?php echo $dato['ocupacion_papa']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Escolaridad</label>
                <input type="text" class="form-control" name="escolaridad_papa"
                    value="<?php echo $dato['escol_papa']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Tel. del Padre</label>
                <input type="text" class="form-control" name="tel_casa_papa" value="<?php echo $dato['tel_papa']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Correo Electronico</label>
                <input type="email" name="email_papa" id="" class="form-control"
                    value="<?php echo $dato['correo_papa']; ?>">
            </div>
        </div>

        <!-- Aqui terminan los datos del padre -->

        <!-- Aqui inician los datos de la madre -->
        <div class="row">
            <h3 class="text-center">Datos de la Madre</h3>
            <div class="col-md-4">
                <label for="" class="form-label">CURP</label>
                <input type="text" class="form-control" name="curp_mama" value="<?php echo $dato['curp_mama']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Nombre de la Madre: </label>
                <input type="text" class="form-control" name="nombre_mama" id=""
                    value="<?php echo $dato['nombre_mama']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Fecha Nacimiento</label>
                <input type="date" name="nacimiento_mama" id="" class="form-control"
                    value="<?php echo $dato['fecha_nac_mama']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Lugar de Nacimiento</label>
                <input type="text" name="estado_mama" id="" class="form-control"
                    value="<?php echo $dato['nacimiento_mama']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Calle</label>
                <input type="text" class="form-control" name="calle_mama" value="<?php echo $dato['calle_mama']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Colonia</label>
                <input type="text" class="form-control" name="colo_mama" value="<?php echo $dato['colonia_mama']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Estado Civil</label>
                <input type="text" name="civil_mama" class="form-control" value="<?php echo $dato['civil_mama']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Ocupacion</label>
                <input type="text" class="form-control" name="ocupacion_mama"
                    value="<?php echo $dato['ocupacion_mama']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Escolaridad</label>
                <input type="text" class="form-control" name="esco_mama"
                    value="<?php echo $dato['escolaridad_mama']; ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Tel. Casa</label>
                <input type="text" class="form-control" name="tel_casa_mama" 
                value="<?php echo $dato['tel_mama']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Correo Electronico</label>
                <input type="email" name="email_mama" id="" class="form-control"
                    value="<?php echo $dato['correo_mama']; ?>">
            </div>
        </div>
        <!-- Aqui terminan los datos de la madre -->
        <!-- ------------------------------------------------------------------------------------------------ -->
        <!-- Aqui inician los datos del tutor -->

        <div class="row">
            <h3 class="text-center">Datos del Tutor</h3>
            <div class="col-md-4">
                <label for="" class="form-label">CURP</label>
                <input type="text" class="form-control" name="curp_tutor" value="<?php echo $dato['curp_tutor']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Nombre del Tutor: </label>
                <input type="text" class="form-control" name="nombre_tutor" id=""
                    value="<?php echo $dato['nombre_tutor']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Fecha Nacimiento</label>
                <input type="date" name="nacimiento_tutor" id="" class="form-control"
                    value="<?php echo $dato['nac_tutor']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Lugar de Nacimiento</label>

                <input type="text" name="estado_tutor" id="" class="form-control"
                    value="<?php echo $dato['nacimiento_tutor']; ?>">

            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Calle Y Numero</label>
                <input type="text" class="form-control" name="calle_tutor" value="<?php echo $dato['calle_tutor']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Colonia</label>
                <input type="text" class="form-control" name="colo_tutor" value="<?php echo $dato['colonia_tutor']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Estado Civil</label>
                <input type="text" name="estado_civ_tutor" id="" class="form-control"
                    value="<?php echo $dato['civil_tutor']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Ocupacion</label>
                <input type="text" class="form-control" name="ocupacion_tutor"
                    value="<?php echo $dato['ocupacion_tutor']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Escolaridad</label>
                <input type="text" class="form-control" name="esco_tutor"
                    value="<?php echo $dato['escolaridad_tutor']; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Tel. Casa</label>
                <input type="text" class="form-control" name="tel_casa_tutor"
                    value="<?php echo $dato['tel_tutor']; ?>">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Correo Electronico</label>
                <input type="email" name="email_tutor" id="" class="form-control"
                    value="<?php echo $dato['correo_tutor']; ?>">
            </div>
            <br><br><br><br>
            <button class="btn btn-success" id="btnguardar" type="submit">ACEPTAR</button>

        </div>

    </form>
    <script src="../JS/actualizar_secu.js"></script>
</div>
</div>
<br><br><br>

<?php
}
include_once 'pie.php';

?>