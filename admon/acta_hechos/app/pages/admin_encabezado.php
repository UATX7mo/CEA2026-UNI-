<?php
declare(strict_types=1);
?>
<header class="cea-admin-header">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a href="../../index.php" aria-label="Regresar al administrador"><img src="assets/img/cea/logo.png" alt="Escudo del Centro Escolar Aparicio" class="logo me-2"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Mostrar menú"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="../../index.php">Administrador</a></li>
                    <li class="nav-item"><a class="nav-link" href="sistema.php">Nueva acta</a></li>
                    <li class="nav-item"><a class="nav-link" href="listado.php">Actas registradas</a></li>
                </ul>
                <div class="mx-auto text-center"><span class="text-white fw-bold">Bienvenido <?= e((string) ($_SESSION['admin'] ?? '')) ?></span></div>
                <a class="btn btn-danger ms-auto" href="../../index.php">Volver al administrador</a>
            </div>
        </div>
    </nav>
</header>
