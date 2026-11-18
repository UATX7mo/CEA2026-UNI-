<?php

$link = 'mysql:host=localhost;dbname=inscripcion2026;charset=utf8mb4';
$usuario='root';
$pass='';
try {
    $pdo = new PDO($link, $usuario, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
  // echo 'Conectado';
} catch (PDOException $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
}