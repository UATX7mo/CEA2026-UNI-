<?php
require_once __DIR__ . '/autorizacion.php';
exigirAccesoAdmon();

include '../complementos/conexion_fichas.php';

if(isset($_POST['id_insc'])){

    $id = $_POST['id_insc'];

    $sql = "DELETE FROM secundaria WHERE id_insc = :id";

    $stmt = $pdo->prepare($sql);

    if($stmt->execute([':id' => $id])){
        echo "1";
    }else{
        echo "0";
    }

}else{
    echo "0";
}
