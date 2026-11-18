<?php
include 'encabezado.php';
include '../complementos/conexion_fichas.php';
?>
            <?php

// include_once 'conexion1.php';

// LEER
$sql_leer = 'SELECT * FROM secundaria';
$gsent = $pdo->prepare($sql_leer);
$gsent->execute();
$resultado = $gsent->fetchAll();

// var_dump($resultado);

?>

  
    <h1 class="text-center">Monitor Inscripciones Secundaria</h1>    
    
    <div class="container">
       <div class="row">
           <div class="col-lg-12">
            <table id="example" class="table table-bordered  display nowrap" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
            <th>Nombre</th>
            <th>Paterno</th>
            <th>Materno</th>
            <th>curp</th>
            <th>Editar</th>
            <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resultado as $dato) { ?>
                    <tr>
                    <td><?php echo $dato['id_insc']; ?></td>
                    <td><?php echo $dato['nombre_alum']; ?></td>
                    <td><?php echo $dato['ape_pat']; ?></td>
                    <td><?php echo $dato['ape_mat']; ?></td>

                    <td><?php echo $dato['curp']; ?></td>
                    
                    <td>
                        <a href="modificar_secu.php?id_insc=<?php echo $dato['id_insc']; ?>">
                        <button class="btn btn-warning">
                            Modificar
                        </button>
                        </a>
                    </td>
                    <td>
                         
                        <button 
                            class="btn btn-danger btnEliminar"
                            data-id="<?php echo $dato['id_insc']; ?>">
                            Eliminar
                        </button>
                       
                     </td>
                    
                       
                    </tr>
                    <?php } ?>
                </tfoot>
            </table>  
         
           </div>
       </div> 
    </div>
   
    <br><br><br>
     <script src="../JS/eliminar_secu.js"></script>
<?php
    include_once 'pie.php';

?>