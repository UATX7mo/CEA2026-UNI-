<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

include_once 'complementos/conexion.php';

$user_login = trim($_POST['user'] ?? '');
$contra = $_POST['pass'] ?? '';

if ($user_login === '' || $contra === '') {
    http_response_code(400);
    exit('Ingresa usuario y contraseña.');
}



//verificar si el usuario existe
$sql = 'SELECT * FROM users WHERE nombre_user = ?';
$sentencia = $pdo->prepare($sql);
$sentencia->execute(array($user_login));
//fetch() solo nos muestra todos los resultados
$resultado = $sentencia->fetch();


//si el usuario no esta resgistrado matamos la operacion
//var_dump($resultado);
//ponemos el signo de exclamacion para decir que si es diferente
if(!$resultado){
     //Detenemos la operacion
    echo 'No exite el usuario';
   
    die();

}

if(password_verify($contra,$resultado['pass'])){
    session_regenerate_id(true);
    $_SESSION['admin'] = $user_login;
    $tipoUsuario = strtolower(trim($resultado['tipo_user'] ?? $resultado['rol'] ?? 'administrador'));
    $roles = [
        'administrador' => 'admin',
        'admin' => 'admin',
        'primaria' => 'primaria',
        'secundaria' => 'secundaria',
        'preescolar' => 'preescolar',
    ];
    $rol = $roles[$tipoUsuario] ?? 'admin';
    $_SESSION['rol'] = $rol;

    if ($rol === 'primaria') {
        header('Location: admon/vista_inscripciones_prima.php');
    } elseif ($rol === 'secundaria') {
        header('Location: admon/vista_inscripciones_secu.php');
    } elseif ($rol === 'preescolar') {
        header('Location: admon/vista_inscripciones_prees.php');
    } else {
        header('Location: admon/index.php');
    }
}else{
    echo 'Las contraseñas no son iguales';
    die();

}
