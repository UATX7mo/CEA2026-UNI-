<?php 
include_once '../admon/validar_modulo.php';
include_once 'encabezado.php';
?>
<br>
<div class="container">
    <h3>FORMATO DE INSCRIPCIÓN AGOSTO 2026</h3>
    <br>
    <h5>INGRESE EL CURP DEL ESTUDIANTE</h5>
    <div class="row">
        <div class="col-4">
            <p> </p>
        </div>
    </div>
    <div class="row">
        <div class="col-4 align-items-center">
            <form id="buscar">
                <input id="curp" class="form-control" type="text" placeholder="Buscar" name="busca_prees">
                <br>
                <button type="submit" class="btn btn-primary">Aceptar</button>
            </form>
            <br>
        </div>
    </div>
    <div class="row">
        <div class="col-4">
            <p> </p>
        </div>
    </div>
</div>

<script src="../JS/encontrar_prees.js"></script>
<?php 
include('pie.php');
?>