
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="IMG/logo.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="CSS/style.css">
    <title>CEA</title>
    
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">

            <img src="IMG/logo.png" alt="" class="logo img-fluid me-2" style="max-width: 70px;">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Nosotros
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <li><a class="dropdown-item" href="misionyvision.php">Mision y Vision</a></li>
                            <!-- <li><a class="dropdown-item d-none" href="fichas/index.php">Historia</a></li> -->
                            
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ofertaeducativa.php">Oferta Educativa</a>
                    </li>
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Alumnos
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                             <li><a class="dropdown-item" href="fichas2026/index.php">Inscripcion</a></li> 
                        </ul>
                    </li>

                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ofertaeducativa.php">Oferta Educativa</a>
                    </li>
                </ul>

                <a href="login.php" class="btn btn-primary">Login</a>
            </div>
        </div>
    </nav>

