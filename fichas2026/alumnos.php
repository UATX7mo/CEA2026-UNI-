<?php
require_once __DIR__ . '/../admon/autorizacion.php';

if (empty($_SESSION['admin'])) {
    header('Location: /CEA2026/login.php');
    exit;
}
if (rolAdmonActual() !== 'admin') {
    header('Location: /CEA2026/admon/' . rutaInicialAdmon(rolAdmonActual()));
    exit;
}

$adminBasePath = true;
require __DIR__ . '/../admon/encabezado.php';
?>
<main class="container py-4">
    <div class="mb-4">
        <h1>Lista de Alumnos</h1>
        <p>Selecciona el nivel educativo para consultar sus alumnos.</p>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <a class="card h-100 text-decoration-none text-dark shadow-sm" href="vista_inscripciones_prees.php">
                <div class="card-body"><i class="fa-solid fa-child-reaching fa-2x mb-3" aria-hidden="true"></i><h2 class="h5">Preescolar</h2><span>Consultar alumnos</span></div>
            </a>
        </div>
        <div class="col-md-4">
            <a class="card h-100 text-decoration-none text-dark shadow-sm" href="vista_inscripciones_prima.php">
                <div class="card-body"><i class="fa-solid fa-book-open fa-2x mb-3" aria-hidden="true"></i><h2 class="h5">Primaria</h2><span>Consultar alumnos</span></div>
            </a>
        </div>
        <div class="col-md-4">
            <a class="card h-100 text-decoration-none text-dark shadow-sm" href="vista_inscripciones_secu.php">
                <div class="card-body"><i class="fa-solid fa-graduation-cap fa-2x mb-3" aria-hidden="true"></i><h2 class="h5">Secundaria</h2><span>Consultar alumnos</span></div>
            </a>
        </div>
    </div>
</main>
<?php require __DIR__ . '/../admon/pie.php'; ?>
