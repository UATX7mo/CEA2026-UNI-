<?php
include_once 'encabezado.php';
?>
 <!-- HERO / PORTADA -->
<div class="hero">
    
    <!-- IMAGEN DE FONDO -->
    <img src="IMG/fondoindex.jpeg" class="hero-img">

    <!-- CAPA OSCURA -->
    <div class="hero-overlay"></div>

    <!-- CONTENIDO -->
    <div class="hero-content text-center">

        <!-- LOGOS + TITULO -->
        <div class="d-flex align-items-center justify-content-center mb-3">
            <img src="IMG/logo.png" class="hero-logo me-3">

            <h1 class="hero-title">
                Centro Escolar Aparicio
            </h1>

            <img src="IMG/logo.png" class="hero-logo ms-3">
        </div>

        <!-- SUBTITULO -->
        <h4 class="hero-subtitle">
            Campus Calpulalpan
        </h4>

        <!-- FRASE -->
        <p class="hero-text">
            Camino, Verdad y Vida
        </p>

        <!-- BOTON -->
        <a href="#conocenos" class="btn btn-gold mt-3">
            Ver más
        </a>

</div>
</div>
<!-- Cierre de portada -->
<!-- ////////////////////cierre de portada

<!-- ////////////////////////////////////////////////////// -->
<!-- informavion de quienes somos -->
<!-- SECCIÓN INFO (COLLAPSE) -->
<div id="info" class="container py-5 mt-5">
<div class="col-md-12 mb-12" id="conocenos">
    <div class="card shadow border-0 h-100 text-center p-3">
    <h2 class="text-center mb-4 titulo-gold">Conócenos</h2>

    <!-- BOTÓN -->
    <button class="btn btn-gold-pro w-100 text-start d-flex justify-content-between align-items-center mb-3"
        data-bs-toggle="collapse" data-bs-target="#quienes">
        <h4>¿Quiénes somos?</h4>
        <span class="icono">+</span>
    </button>

    <!-- CONTENIDO -->
    <div id="quienes" class="collapse mb-3">
        <div class="card card-body card-dark-pro">
           Somos una institución educativa de espiritualidad católica-franciscana, 
           con más de 50 años de experiencia y de ya reciente presencia en la comunidad Calpulalpense; 
           comprometidos con la formación integral de nuestros alumnos bajo el modelo praxiológico 
           (aplicar lo que se aprende), nos hemos convertido en un referente de calidad, innovación y 
           excelencia educativa para la región norponiente del estado de Tlaxcala.
        </div>
    </div>

</div></div></div>
<!-- cierre de quienes somos -->

<!-- ////////////////////////////////TARJETAS INFORMATIVAS////////////////// --> 
<!-- SECCIÓN VER MÁS -->
<div class="container py-5">

    <div class="row">

<div class="col-md-4 mb-4">
    <div class="card shadow border-0 h-100 text-center p-3">
        <i class="fas fa-school fa-3x mb-3 text-warning"></i>
            <h4>Instalaciones</h4>
            <p>Contamos con espacios seguros para todos los niveles educativos.</p>
        </div></div>


        <div class="col-md-4 mb-4">
    <div class="card shadow border-0 h-100 text-center p-3">
        <i class="fas fa-graduate fa-3x mb-3 text-success"></i>

   
            <i class="fas fa-user-graduate fa-3x mb-3 text-success"></i>
            <h4>Educación</h4>
            <p>Actualmente ofrecemos tres niveles educativos: preescolar, 
                primaria y secundaria. Y bajo la dirección de Fray Ricardo Luciano Maldonado Castillo,
                 director ejecutivo de nuestra institución y con el compromiso de toda la planta docente,
                  administrativa y de servicios, trabajamos para ofrecerte una educación de calidad.</p>
        </div>
</div>
            <div class="col-md-4 mb-4">
    <div class="card shadow border-0 h-100 text-center p-3">
            <i class="fas fa-heart fa-3x mb-3 text-danger"></i>
            <h4>Valores</h4>
            <p>Que hacen de nuestros alumnos, personas con calidez humana, 
                respetuosas de la naturaleza, comprometidas con los más necesitados y 
                agentes de paz.</p>
        </div></div>

    </div>

</div>
<!-- ///////////////////////////////cierre ver mas////////////////

<!-- ////////////////mapa///////////////// -->
<!-- MAPA -->


<div class="container text-center my-5">
    <h3 class="mb-4 text-gold">Nuestra ubicación</h3>

    <div class="ratio ratio-16x9 shadow">
        <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d912.9704425844316!2d-98.57019620594104!3d19.589217343238133!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85d1cdd849e357eb%3A0x36f2415972f072fe!2sCentro%20Escolar%20Aparicio%2C%20campus%20Calpulalpan!5e0!3m2!1ses-419!2smx!4v1770653536000!5m2!1ses-419!2smx" 
        style="border:0;" 
        allowfullscreen="" loading="lazy">
        </iframe>
    </div>
</div>
<br><br><br>
<!-- CIERRE DE MAPA -->

<?php
include_once 'pie.php';
?>


