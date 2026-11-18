<?php
include 'encabezado.php';

?>
        <div class="container">
            <h3>Modificar Aviso</h3>
            <form action="modificar_aviso.php" method="post" enctype="multipart/form-data">
                
            <textarea name="area" id="area" class="form-control">

            </textarea>
            <br><br>
            <input type="file" src="" alt="" class="form-control" name="image">
            <br><br>
            <button class="btn btn-primary" type="submit">Aceptar</button>
            </form>
        </div>
<?php
include 'pie.php';

?>