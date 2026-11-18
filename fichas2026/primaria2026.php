<?php

include_once '../admon/validar_modulo.php';
include_once '../complementos/conexion_datosgen.php';
include_once 'encabezado.php';



if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}





    $sql1 = "SELECT * FROM estados";
    $stmt = $pdo->prepare($sql1);
    $stmt->execute();
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql2 = "SELECT * FROM civil";
    $stmt1 = $pdo->prepare($sql2);
    $stmt1->execute();
    $civil = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    $sql3 = "SELECT * FROM tipo_sangre";
    $stmt2 = $pdo->prepare($sql3);
    $stmt2->execute();
    $sangre = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Aqui inician los datos de domicilio -->
<div class="container">
    <h1 class="text-center">Fichas Nivel Primaria</h1>
    <h3 class="text-center">Datos del Alumno</h3>
    <form id="formdatos" method="post" name="formdatos">
        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">

        <div class=" row">
            <div class="col-md-4">
                <label for="" class="form-label">Nombre(s): </label>
                <input type="text" class="form-control" name="nombre_alum" id="nombre_alum" placeholder="Nombre(s):">

            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Apellido Paterno: </label>
                <input type="text" class="form-control" name="ape_pat" id="ape_pat" placeholder="Apellido Paterno:">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Apellido Materno: </label>
                <input type="text" class="form-control" name="ape_mat" id="" placeholder="Apellido Materno:">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">CURP:</label>
                <input type="text" class="form-control" name="curp_alum" placeholder="CURP del Alumno">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Sexo:</label>
                <select name="sexo" id="" class="form-control">
                    <option value="">Selecciona sexo</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="">Fecha Nacimiento</label>
                <input type="date" name="nacimiento_alum" id="" class="form-control">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Municipio de Nacimiento:</label>
                <input type="text" class="form-control" name="muni_alum_nac" id=""
                    placeholder="Municipio de Nacimiento">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Estado de nacimiento:</label>
                <select name="estado_alum_nac" id="" class="form-control">
                    <option value="">Seleccione un estado</option>
                    <?php    
                                foreach($estados as $estados1):
                                    echo '<option value="'.$estados1['estado'].'">'.$estados1['estado'].'</option>';
                                    endforeach
                                    ?>
                </select>
            </div>

        </div>

        <!-- Aqui terminan los datos de dpersonales -->

        <!-- Aqui inician los datos de domicilio -->
        <div class="row">
            <h3 class="text-center">Domicilio</h3>
            <div class="col-md-4">
                <label for="" class="form-label">Calle Y Numero</label>
                <input type="text" class="form-control" name="calle_alum" placeholder="Calle">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Colonia</label>
                <input type="text" class="form-control" name="colo_alum" placeholder="Colonia">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Municipio</label>
                <input type="text" class="form-control" name="muni_alum_rad" placeholder="Municipio">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Estado</label>
                <select name="estado_alum_rad" id="" class="form-control">
                    <option value="">Seleccione un estado de radicacion</option>
                    <?php    
                                foreach($estados as $estados1):
                                    echo '<option value="'.$estados1['estado'].'">'.$estados1['estado'].'</option>';
                                    endforeach
                                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Codigo Postal</label>
                <input type="text" class="form-control" name="cp" placeholder="Codigo Postal">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Tel. Casa</label>
                <input type="text" class="form-control" name="tel_casa_alum" placeholder="Telefono de Casa">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Tel. Celular</label>
                <input type="text" class="form-control" name="tel_cel_alum" placeholder="Telefono Celular">
            </div>
        </div>

        <div class="row">
            <h3 class="text-center">Datos de Salud</h3>
            <div class="col-md-4">
                <label for="" class="form-label">Alergias</label>
                <input type="text" class="form-control" name="alergias" placeholder="Alergias">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Tipo de Sangre</label>
                <select name="sangre" id="" class="form-control">
                    <option value="">Seleccione un Tipo de Sangre</option>
                    <?php    
                                foreach($sangre as $sangre1):
                                    echo '<option value="'.$sangre1['tipo_sanguineo'].'">'.$sangre1['tipo_sanguineo'].'</option>';
                                    endforeach
                                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Peso</label>
                <input type="text" class="form-control" name="peso" placeholder="Peso">
            </div>
        </div>
        <div class="row">


            <div class="col-md-4">
                <label for="" class="form-label">Altura</label>
                <input type="text" class="form-control" name="talla" placeholder="Altura">
            </div>

            <div class="col-md-4">
                <label for="" class="form-label">Enfermedades Cronicas</label>
                <input type="text" class="form-control" name="enfermedades" placeholder="Enfermedades Cronicas">
            </div>
        </div>




        <!-- Aqui terminan los datos de domicilio -->
        <!-- Aqui inician los datos escolares -->
        <div class="row">
            <h3 class="text-center">Datos Escolares</h3>
            <div class="row">
                <div class="col-md-4">
                    <label for="" class="form-label">Grado:</label>
                    <select name="grado" id="" class="form-control">
                        <option value="">Selecciona el grado</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="" class="form-label">Grupo:</label>
                    <select name="grupo" id="" class="form-control">
                        <option value="">Selecciona el grado</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="" class="form-label">Escuela de procedencia</label>
                    <input type="text" name="escuela_proce" class="form-control" placeholder="Escuela de procedencia">
                </div>
            </div>
 <h5>Grados de preescolar que curso el alumno</h5>
 <div class="col-md-4">
    <div class="form-check form-check-inline">
        <input type="checkbox" class="preescolar"
         name="r1" id="r1" value="SI">
        <label class="form-check-label" for="r1">1</label>
    </div>

    <div class="form-check form-check-inline">
        <input type="checkbox" class="preescolar" 
        name="r2" id="r2" value="SI">
        <label class="form-check-label" for="r2">2</label>
    </div>

    <div class="form-check form-check-inline">
        <input type="checkbox" class="preescolar" 
        name="r3" id="r3" value="SI">
        <label class="form-check-label" for="r3">3</label>
    </div>
</div>
           
            <!-- Aqui terminan los datos escolares -->
            <!-- Aqui inician los datos religiosos -->
            <div class="row">
                <h3 class="text-center">Datos Religiosos</h3>
                <div class="col-md-4">
                    <label>Religion que Profesa:</label>
                    <input type="text" class="form-control" name="religion" placeholder="Religion que profesa">
                </div>
                <div class="col-md-4">

                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="Bautismo" id="" value="SI">
                        <label class="form-check-label" for="inlineRadio1">Bautismo</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="Comunion" id="" value="SI">
                        <label class="form-check-label" for="inlineRadio2">Comunion</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="Confirmacion" id="" value="SI">
                        <label class="form-check-label" for="inlineRadio3">Confirmacion</label>
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
                <input type="text" class="form-control" placeholder="CURP" name="curp_papa">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Nombre del Padre: </label>
                <input type="text" class="form-control" name="nombre_papa" id="" placeholder="Nombre Completo">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Fecha Nacimiento</label>
                <input type="date" name="nacimiento_papa" id="" class="form-control">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Lugar de Nacimiento</label>
                <select name="estado_papa" id="" class="form-control">
                    <option value="">Seleccione un estado</option>
                    <?php    
                                foreach($estados as $estados1):
                                    echo '<option value="'.$estados1['estado'].'">'.$estados1['estado'].'</option>';
                                    endforeach
                                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Calle Y Numero</label>
                <input type="text" class="form-control" placeholder="Calle" name="calle_papa">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Colonia</label>
                <input type="text" class="form-control" name="colonia_papa" placeholder="Colonia">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Estado Civil</label>
                <select name="estado_civ_papa" id="" class="form-control">
                    <option value="">Seleccione su estado civil</option>
                    <?php    
                                foreach($civil as $civil1):
                                    echo '<option value="'.$civil1['civil'].'">'.$civil1['civil'].'</option>';
                                    endforeach
                                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Ocupacion</label>
                <input type="text" class="form-control" name="ocupacion_papa" placeholder="Ocupacion">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Escolaridad</label>
                <input type="text" class="form-control" name="escolaridad_papa" placeholder="Escolaridad">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Tel. del Padre</label>
                <input type="text" class="form-control" name="tel_casa_papa" placeholder="Telefono celular">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Correo Electronico</label>
                <input type="email" name="email_papa" id="" class="form-control" placeholder="Correo">
            </div>
        </div>

        <!-- Aqui terminan los datos del padre -->

        <!-- Aqui inician los datos de la madre -->
        <div class="row">
            <h3 class="text-center">Datos de la Madre</h3>
            <div class="col-md-4">
                <label for="" class="form-label">CURP</label>
                <input type="text" class="form-control" name="curp_mama" placeholder="CURP">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Nombre de la Madre: </label>
                <input type="text" class="form-control" name="nombre_mama" id="" placeholder="Nombre Completo">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Fecha Nacimiento</label>
                <input type="date" name="nacimiento_mama" id="" class="form-control">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Lugar de Nacimiento</label>
                <select name="estado_mama" id="" class="form-control">
                    <option value="">Seleccione un estado</option>
                    <?php    
                                foreach($estados as $estados1):
                                    echo '<option value="'.$estados1['estado'].'">'.$estados1['estado'].'</option>';
                                    endforeach
                                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Calle</label>
                <input type="text" class="form-control" name="calle_mama" placeholder="Calle">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Colonia</label>
                <input type="text" class="form-control" name="colo_mama" placeholder="Colonia">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Estado Civil</label>
                <select name="civil_mama" class="form-control">
                    <option value="">Seleccione su estado civil</option>
                    <?php    
                                foreach($civil as $civil1):
                                    echo '<option value="'.$civil1['civil'].'">'.$civil1['civil'].'</option>';
                                    endforeach
                                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Ocupacion</label>
                <input type="text" class="form-control" name="ocupacion_mama" placeholder="Ocupacion">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Escolaridad</label>
                <input type="text" class="form-control" name="esco_mama" placeholder="Escolaridad">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Tel. Casa</label>
                <input type="text" class="form-control" name="tel_casa_mama" placeholder="Telefono Celular">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Correo Electronico</label>
                <input type="email" name="email_mama" id="" class="form-control" placeholder="Correo">
            </div>
        </div>
        <!-- Aqui terminan los datos de la madre -->
        <!-- ------------------------------------------------------------------------------------------------ -->
        <!-- Aqui inician los datos del tutor -->

        <div class="row">
            <h3 class="text-center">Datos del Tutor</h3>
            <div class="col-md-4">
                <label for="" class="form-label">CURP</label>
                <input type="text" class="form-control" name="curp_tutor" placeholder="CURP">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Nombre del Tutor: </label>
                <input type="text" class="form-control" name="nombre_tutor" id="" placeholder="Nombre Completo">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Fecha Nacimiento</label>
                <input type="date" name="nacimiento_tutor" id="" class="form-control">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Lugar de Nacimiento</label>
                <select name="estado_tutor" id="" class="form-control">
                    <option value="">Seleccione un estado</option>
                    <?php    
                                foreach($estados as $estados1):
                                    echo '<option value="'.$estados1['estado'].'">'.$estados1['estado'].'</option>';
                                    endforeach
                                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Calle Y Numero</label>
                <input type="text" class="form-control" name="calle_tutor" placeholder="Calle">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Colonia</label>
                <input type="text" class="form-control" name="colo_tutor" placeholder="Colonia">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Estado Civil</label>
                <select name="estado_civ_tutor" id="" class="form-control">
                    <option value="">Seleccione su estado civil</option>
                    <?php    
                                foreach($civil as $civil1):
                                    echo '<option value="'.$civil1['civil'].'">'.$civil1['civil'].'</option>';
                                    endforeach
                                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Ocupacion</label>
                <input type="text" class="form-control" name="ocupacion_tutor" placeholder="Ocupacion">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Escolaridad</label>
                <input type="text" class="form-control" name="esco_tutor" placeholder="Escolaridad">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <label for="" class="form-label">Tel. Casa</label>
                <input type="text" class="form-control" name="tel_casa_tutor" placeholder="Telefono Celular">
            </div>
            <div class="col-md-4">
                <label for="" class="form-label">Correo Electronico</label>
                <input type="email" name="email_tutor" id="" class="form-control" placeholder="Correo">
            </div>
            <br><br><br><br>
            <button class="btn btn-success" id="btnguardar" type="submit">ACEPTAR</button>

        </div>

    </form>
</div>
<br><br><br>
<script src="../JS/primaria.js"></script>


<?php
include_once '../pie.php';
?>
