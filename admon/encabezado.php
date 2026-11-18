<?php
require_once __DIR__ . '/autorizacion.php';
autorizarPaginaAdmon();
$rolAdminUI = rolAdmonActual();
?>
<!DOCTYPE html>
<html lang="<?= !empty($adminControlUrlsPage) ? 'es' : 'en' ?>">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<?php if (!empty($adminEscanerPage) || !empty($adminBasePath)) { ?><base href="/CEA2026/admon/"><?php } ?>
<?php if (!empty($adminEscanerPage)) { ?><link rel="stylesheet" href="/CEA2026/CSS/style_escaner.css"><?php } ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
<link rel="stylesheet" href="../CSS/style.css">
    <!--  Datatables  -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.20/datatables.min.css" />
    <link rel="shortcut icon" href="../IMG/logo.png" type="image/x-icon">

  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>CEA</title>
</head>

<body class="<?= !empty($adminControlUrlsPage) ? 'admin-curls-body' : '' ?>">
   <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container-fluid">

        <!-- LOGO -->
        <img src="../IMG/logo.png" alt="Escudo del Centro Escolar Aparicio" class="logo me-2" style="max-width: 70px;">

        <!-- BOTÓN HAMBURGUESA -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Mostrar menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- CONTENIDO -->
        <div class="collapse navbar-collapse" id="navbarMenu">

            <!-- MENÚ IZQUIERDA -->
            <ul class="navbar-nav me-auto">
                <?php if ($rolAdminUI === 'admin') { ?>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="avisos.php">Avisos</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Inscripciones</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="vista_inscripciones_prees.php">Preescolar</a></li>
                        <li><a class="dropdown-item" href="vista_inscripciones_prima.php">Primaria</a></li>
                        <li><a class="dropdown-item" href="vista_inscripciones_secu.php">Secundaria</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Alumnos</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../fichas2026/alumnos.php"><i class="fa-solid fa-users me-2" aria-hidden="true"></i>Lista de Alumnos</a></li>
                        <li><a class="dropdown-item" href="/CEA2026/admon/reportes.php"><i class="fa-solid fa-file-alt me-2" aria-hidden="true"></i>Reportes de alumnos</a></li>
                        <li><a class="dropdown-item" href="/CEA2026/admon/historial_reportes.php"><i class="fa-solid fa-folder-open me-2" aria-hidden="true"></i>Ver reportes por categoría</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="acta_hechos/public/sistema.php">Acta de hechos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../escaner/index/Escaner.php"><i class="fa-solid fa-qrcode me-1" aria-hidden="true"></i>Escáner</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="asistencias.php"><i class="fa-solid fa-clipboard-check me-1" aria-hidden="true"></i>Mostrar asistencias</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="incidencias/Formulario.php"><i class="fa-solid fa-triangle-exclamation me-1" aria-hidden="true"></i>Incidencias</a>
                </li>
                <?php } elseif ($rolAdminUI === 'primaria') { ?>
                <li class="nav-item"><a class="nav-link active" href="vista_inscripciones_prima.php">Inscripciones Primaria</a></li>
                <li class="nav-item"><a class="nav-link" href="/CEA2026/admon/reportes.php"><i class="fa-solid fa-file-alt me-1" aria-hidden="true"></i>Reportes de alumnos</a></li>
                <li class="nav-item"><a class="nav-link" href="/CEA2026/admon/historial_reportes.php"><i class="fa-solid fa-folder-open me-1" aria-hidden="true"></i>Ver reportes</a></li>
                <?php } elseif ($rolAdminUI === 'secundaria') { ?>
                <li class="nav-item"><a class="nav-link active" href="vista_inscripciones_secu.php">Inscripciones Secundaria</a></li>
                <li class="nav-item"><a class="nav-link" href="/CEA2026/admon/reportes.php"><i class="fa-solid fa-file-alt me-1" aria-hidden="true"></i>Reportes de alumnos</a></li>
                <li class="nav-item"><a class="nav-link" href="/CEA2026/admon/historial_reportes.php"><i class="fa-solid fa-folder-open me-1" aria-hidden="true"></i>Ver reportes</a></li>
                <?php } elseif ($rolAdminUI === 'preescolar') { ?>
                <li class="nav-item"><a class="nav-link active" href="vista_inscripciones_prees.php">Inscripciones Preescolar</a></li>
                <li class="nav-item"><a class="nav-link" href="/CEA2026/admon/reportes.php"><i class="fa-solid fa-file-alt me-1" aria-hidden="true"></i>Reportes de alumnos</a></li>
                <li class="nav-item"><a class="nav-link" href="/CEA2026/admon/historial_reportes.php"><i class="fa-solid fa-folder-open me-1" aria-hidden="true"></i>Ver reportes</a></li>
                <?php } ?>
            </ul>

            <!-- USUARIO CENTRADO -->
            <div class="mx-auto text-center">
                <span class="text-white fw-bold">Bienvenido <?= htmlspecialchars($_SESSION['admin'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <!-- BOTÓN DERECHA -->
            <form action="/CEA2026/admon/cerrar.php" method="post" class="ms-auto">
                <button type="submit" class="btn btn-danger">Cerrar Sesión</button>
            </form>

        </div>
    </div>
</nav>
    <br><br><br>
