<?php
require_once __DIR__ . '/acceso.php';
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/Conexion.php';

$mensaje = "";
$tipo_mensaje = "";
$ultimo_id_registrado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_incidencia     = $_POST['fecha_incidencia'] ?? '';
    $hora_incidencia      = $_POST['hora_incidencia'] ?? '';
    $area_lugar           = trim($_POST['area_lugar'] ?? '');
    $tipo_incidencia      = trim($_POST['tipo_incidencia'] ?? '');
    $descripcion          = trim($_POST['descripcion'] ?? '');
    $nombre_involucrado   = trim($_POST['nombre_involucrado'] ?? '');
    $cargo_grupo          = trim($_POST['cargo_grupo'] ?? '');
    $responsable_atencion = trim($_POST['responsable_atencion'] ?? '');
    $prioridad            = $_POST['prioridad'] ?? '';
    $accion_realizada     = trim($_POST['accion_realizada'] ?? '');
    $estado               = $_POST['estado'] ?? 'Pendiente';
    $fecha_estado         = !empty($_POST['fecha_estado']) ? $_POST['fecha_estado'] : NULL;
    $observaciones        = trim($_POST['observaciones'] ?? '');

    $nombre_archivo_final = NULL;

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['archivo']['tmp_name'];
        $file_name = $_FILES['archivo']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $extensiones_permitidas = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $extensiones_permitidas)) {
            $directorio_destino = __DIR__ . '/uploads/';
            if (!is_dir($directorio_destino)) {
                mkdir($directorio_destino, 0777, true);
            }
            $nombre_archivo_final = uniqid('firma_') . '.' . $file_ext;
            move_uploaded_file($file_tmp, $directorio_destino . $nombre_archivo_final);
        } else {
            $mensaje = "Formato de archivo no permitido. Solo PDF o imágenes.";
            $tipo_mensaje = "error";
        }
    }

    if (empty($mensaje)) {
        try {
            $sql = "INSERT INTO incidencias 
                    (fecha_incidencia, hora_incidencia, area_lugar, tipo_incidencia, descripcion, nombre_involucrado, cargo_grupo, responsable_atencion, prioridad, accion_realizada, estado, fecha_estado, observaciones, archivo_evidencia) 
                    VALUES 
                    (:fecha_incidencia, :hora_incidencia, :area_lugar, :tipo_incidencia, :descripcion, :nombre_involucrado, :cargo_grupo, :responsable_atencion, :prioridad, :accion_realizada, :estado, :fecha_estado, :observaciones, :archivo_evidencia)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':fecha_incidencia'     => $fecha_incidencia,
                ':hora_incidencia'      => $hora_incidencia,
                ':area_lugar'           => $area_lugar,
                ':tipo_incidencia'      => $tipo_incidencia,
                ':descripcion'          => $descripcion,
                ':nombre_involucrado'   => $nombre_involucrado,
                ':cargo_grupo'          => $cargo_grupo,
                ':responsable_atencion' => $responsable_atencion,
                ':prioridad'            => $prioridad,
                ':accion_realizada'     => $accion_realizada,
                ':estado'               => $estado,
                ':fecha_estado'         => $fecha_estado,
                ':observaciones'        => $observaciones,
                ':archivo_evidencia'    => $nombre_archivo_final
            ]);

            $ultimo_id_registrado = $pdo->lastInsertId();
            $mensaje = "¡Incidencia registrada con éxito!";
            $tipo_mensaje = "exito";
        } catch (PDOException $e) {
            $mensaje = "Error al guardar el registro: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}

include 'encabezado.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
        /* 
          Regla de diseño 60-30-10:
          - 60% Fondos claros (#F8FAFC / blanco)
          - 30% Negro institucional (linear-gradient(90deg, #000000, #1a1a1a))
          - 10% Dorado institucional (#C9A227 / #FFD700)
        */
        .page {
            --color-60-bg: #F8FAFC;
            --color-60-card: #FFFFFF;
            --color-30-dark: linear-gradient(90deg, #000000, #1a1a1a);
            --color-30-solid: #0d0d0d;
            --color-10-gold: #C9A227;
            --color-10-gold-hover: #FFD700;
            --text-dark: #1E293B;
            --text-muted: #64748B;
            --border-light: #E2E8F0;
            --success-bg: #DCFCE7;
            --success-text: #16A34A;
            --danger-bg: #FEF2F2;
            --danger-text: #DC2626;
            --shadow-pro: 0 20px 40px -15px rgba(0, 0, 0, 0.2);
        }

        :where(.page, .page *) {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Ocupa todo el ancho y alto disponible entre encabezado y pie */
        .page {
            width: 100%;
            min-height: calc(100vh - 140px);
            display: flex;
            flex-direction: column;
            color: var(--text-dark);
            background: var(--color-60-bg);
            padding: 20px 28px 28px;
            gap: 20px;
        }

        /* ===== MENÚ HORIZONTAL ===== */
        .page .top-menu {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            width: 100%;
            padding: 10px 18px;
            background: var(--color-30-dark);
            border: 1px solid var(--color-10-gold);
            border-radius: 14px;
            box-shadow: 0 8px 20px -10px rgba(0, 0, 0, 0.5);
        }

        .page .brand-top {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page .hero-logo {
            width: 34px;
            height: 34px;
            object-fit: contain;
        }

        .page .brand-name strong {
            font-size: 14px;
            color: var(--color-10-gold);
            letter-spacing: -0.2px;
            font-weight: 800;
        }

        .page .nav-menu {
            display: flex;
            flex-direction: row;
            gap: 8px;
        }

        .page .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(201, 162, 39, 0.3);
            border-radius: 999px;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            transition: all 0.3s ease;
        }

        .page .nav-link:hover {
            color: var(--color-10-gold-hover) !important;
            border-color: var(--color-10-gold);
            background: rgba(201, 162, 39, 0.1);
        }

        .page .nav-link.active {
            border-color: var(--color-10-gold);
            background: linear-gradient(45deg, #C9A227, #FFD700);
            color: #000000 !important;
        }

        /* ===== ÁREA DEL FORMULARIO (ancho completo) ===== */
        .page .form-area {
            flex: 1;
            width: 100%;
            display: flex;
        }

        .page .card {
            width: 100%;
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--color-60-card);
            border-radius: 20px;
            box-shadow: var(--shadow-pro);
            overflow: hidden;
            border: 1px solid var(--border-light);
        }

        .page .card header {
            padding: 26px 40px 20px;
            background: var(--color-30-dark);
            color: #ffffff;
            border-bottom: 2px solid var(--color-10-gold);
        }

        .page .card header h1 {
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
        }

        .page .card header p {
            margin-top: 4px;
            color: #dddddd;
            font-size: 13px;
        }

        .page #formulario {
            flex: 1;
            padding: 32px 40px 40px;
        }

        .page .form-section {
            margin: 0 0 20px;
            padding: 22px 24px 4px;
            border: 1px solid var(--border-light);
            border-radius: 16px;
            background: linear-gradient(145deg, #ffffff, #fbfcfe);
            box-shadow: 0 5px 16px rgba(15, 23, 42, 0.045);
        }

        .page .titulo-gold {
            color: var(--color-10-gold);
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page .titulo-gold::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--border-light);
        }

        /* Rejilla de 12 columnas para aprovechar todo el ancho */
        .page .grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 16px 20px;
            margin-bottom: 20px;
        }

        .page .col-3  { grid-column: span 3; }
        .page .col-4  { grid-column: span 4; }
        .page .col-6  { grid-column: span 6; }
        .page .col-8  { grid-column: span 8; }
        .page .col-12 { grid-column: span 12; }

        .page .form-group {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .page label {
            margin-bottom: 7px;
            color: var(--text-dark);
            font-size: 12px;
            font-weight: 700;
        }

        .page input[type="text"],
        .page input[type="date"],
        .page input[type="time"],
        .page select,
        .page textarea {
            width: 100%;
            padding: 13px 16px;
            background: #F8FAFC;
            border: 1.5px solid var(--border-light);
            border-radius: 10px;
            color: var(--text-dark);
            font-size: 13px;
            outline: none;
            transition: all 0.25s ease;
        }

        .page input:focus,
        .page select:focus,
        .page textarea:focus {
            border-color: var(--color-10-gold);
            background: var(--color-60-card);
            box-shadow: 0 0 0 4px rgba(201, 162, 39, 0.15);
        }

        .page textarea {
            resize: vertical;
            min-height: 120px;
            line-height: 1.6;
        }

        .page .options-group {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .page .option-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 0;
            padding: 13px 10px;
            background: #F8FAFC;
            border: 1.5px solid var(--border-light);
            border-radius: 10px;
            cursor: pointer;
            color: var(--text-dark);
            font-size: 12px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .page .option-item:hover {
            border-color: var(--color-10-gold);
            background: #FFFBEB;
        }

        .page .option-item input {
            accent-color: var(--color-10-gold);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .page .file-upload-box {
            position: relative;
            min-height: 76px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 18px;
            border: 2px dashed var(--color-10-gold);
            background: #FFFBEB;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.25s ease;
            overflow: hidden;
        }

        .page .file-upload-box:hover {
            background: #FEF3C7;
            border-color: #a78119;
        }

        .page .file-upload-box input[type="file"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }

        .page .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            color: #594500;
            font-size: 14px;
            font-weight: 700;
            margin: 0;
            text-align: center;
            pointer-events: none;
        }

        .page .file-upload-label::before {
            content: "\f093";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 18px;
            color: #8c6d33;
        }

        .page .file-upload-box:focus-within {
            outline: 3px solid rgba(201, 162, 39, 0.35);
            outline-offset: 2px;
        }

        .page .btn-gold-pro {
            width: 100%;
            padding: 17px 20px;
            margin-top: 6px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(45deg, #C9A227, #FFD700);
            color: black;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(201, 162, 39, 0.3);
            transition: all 0.25s ease;
        }

        .page .btn-gold-pro:hover {
            transform: scale(1.01);
            box-shadow: 0 8px 22px rgba(201, 162, 39, 0.5);
        }

        .page .btn-pdf {
            display: block;
            width: 100%;
            text-align: center;
            padding: 14px;
            margin: 0 0 20px;
            background: var(--color-30-dark);
            border: 1px solid var(--color-10-gold);
            color: var(--color-10-gold);
            text-decoration: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.25s ease;
        }

        .page .btn-pdf:hover {
            background: var(--color-10-gold);
            color: black;
        }

        .page .alerta {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 14px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
        }

        .page .alerta.exito {
            background: var(--success-bg);
            color: var(--success-text);
            border: 1px solid #86EFAC;
        }

        .page .alerta.error {
            background: var(--danger-bg);
            color: var(--danger-text);
            border: 1px solid #FCA5A5;
        }

        /* Pantallas medianas */
        @media (max-width: 920px) {
            .page .col-3 { grid-column: span 6; }
            .page .col-4 { grid-column: span 6; }
        }

        /* Celular */
        @media (max-width: 700px) {
            .page {
                padding: 14px 14px 20px;
                gap: 14px;
            }

            .page .top-menu {
                justify-content: center;
            }

            .page .card header {
                padding: 22px 20px 18px;
            }

            .page #formulario {
                padding: 24px 20px 30px;
            }

            .page .form-section {
                margin-bottom: 14px;
                padding: 18px 16px 2px;
            }

            .page .col-3,
            .page .col-4,
            .page .col-6,
            .page .col-8 { grid-column: span 12; }

            .page .options-group {
                grid-template-columns: 1fr 1fr;
            }
        }
</style>

<div class="page">

    <!-- MENÚ HORIZONTAL -->
    <div class="top-menu">
        <div class="brand-top">
            <div class="brand-name">
                <strong>Centro Escolar Aparicio</strong>
            </div>
        </div>

        <nav class="nav-menu">
            <a href="/CEA2026/admon/incidencias/Formulario.php" class="nav-link active">📝 Registrar Incidencia</a>
            <a href="/CEA2026/admon/incidencias/Registros.php" class="nav-link">📋 Lista de Registros</a>
        </nav>
    </div>

    <main class="form-area">
        <div class="card">
            <header>
                <h1>Formulario de Registro de Incidencia</h1>
                <p>Complete la información correspondiente para registrar el reporte.</p>
            </header>

            <form id="formulario" method="POST" action="/CEA2026/admon/incidencias/Formulario.php" enctype="multipart/form-data">

                <?php if (!empty($mensaje)): ?>
                    <div class="alerta <?= $tipo_mensaje ?>"><?= htmlspecialchars($mensaje) ?></div>

                    <?php if ($tipo_mensaje === 'exito' && $ultimo_id_registrado): ?>
                        <a href="/CEA2026/admon/incidencias/Generar_pdf.php?id=<?= $ultimo_id_registrado ?>&amp;auto=1" target="_blank" class="btn-pdf">📄 Abrir documento listo para imprimir</a>
                    <?php endif; ?>
                <?php endif; ?>

                <section class="form-section" aria-labelledby="titulo-datos-incidencia">
                <h2 class="titulo-gold" id="titulo-datos-incidencia">Datos de la Incidencia</h2>
                <div class="grid">
                    <div class="form-group col-6">
                        <label for="fecha-incidencia">Fecha:</label>
                        <input type="date" id="fecha-incidencia" name="fecha_incidencia" required>
                    </div>
                    <div class="form-group col-6">
                        <label for="hora-incidencia">Hora:</label>
                        <input type="time" id="hora-incidencia" name="hora_incidencia" required>
                    </div>
                    <div class="form-group col-6">
                        <label for="area-lugar">Área y Lugar:</label>
                        <input type="text" id="area-lugar" name="area_lugar" placeholder="Ej. Patio escolar, Salón 3°A, Pasillo principal" required>
                    </div>

                    <div class="form-group col-6">
                        <label for="tipo-incidencia">Tipo de Incidencia:</label>
                        <input type="text" id="tipo-incidencia" name="tipo_incidencia" placeholder="Ej. Conducta, Médica, Infraestructura, Modales" required>
                    </div>
                    <div class="form-group col-12">
                        <label for="descripcion">Descripción:</label>
                        <textarea id="descripcion" name="descripcion" placeholder="Describa detalladamente los hechos ocurridos..." required></textarea>
                    </div>
                </div>

                </section>
                <section class="form-section" aria-labelledby="titulo-persona-involucrada">
                <h2 class="titulo-gold" id="titulo-persona-involucrada">Persona Involucrada</h2>
                <div class="grid">
                    <div class="form-group col-8">
                        <label for="nombre-involucrado">Nombre:</label>
                        <input type="text" id="nombre-involucrado" name="nombre_involucrado" placeholder="Nombre completo" required>
                    </div>
                    <div class="form-group col-4">
                        <label for="cargo-grupo">Cargo o Grupo:</label>
                        <input type="text" id="cargo-grupo" name="cargo_grupo" placeholder="Ej. Alumno 4°B, Docente, Visitante">
                    </div>
                </div>

                </section>
                <section class="form-section" aria-labelledby="titulo-atencion-incidencia">
                <h2 class="titulo-gold" id="titulo-atencion-incidencia">Atención de la Incidencia</h2>
                <div class="grid">
                    <div class="form-group col-6">
                        <label for="responsable">Responsable de Atenderla:</label>
                        <input type="text" id="responsable" name="responsable_atencion" placeholder="Nombre del personal a cargo" required>
                    </div>
                    <div class="form-group col-6">
                        <label for="fecha-estado">Fecha de Actualización/Cierre:</label>
                        <input type="date" id="fecha-estado" name="fecha_estado">
                    </div>

                    <div class="form-group col-6">
                        <label>Prioridad:</label>
                        <div class="options-group">
                            <label class="option-item"><input type="radio" name="prioridad" value="Baja" required> Baja</label>
                            <label class="option-item"><input type="radio" name="prioridad" value="Media"> Media</label>
                            <label class="option-item"><input type="radio" name="prioridad" value="Alta"> Alta</label>
                            <label class="option-item"><input type="radio" name="prioridad" value="Crítica"> Crítica</label>
                        </div>
                    </div>
                    <div class="form-group col-6">
                        <label>Estado:</label>
                        <div class="options-group">
                            <label class="option-item"><input type="radio" name="estado" value="Pendiente" required checked> Pendiente</label>
                            <label class="option-item"><input type="radio" name="estado" value="En proceso"> En proceso</label>
                            <label class="option-item"><input type="radio" name="estado" value="Resuelto"> Resuelto</label>
                            <label class="option-item"><input type="radio" name="estado" value="Cerrada"> Cerrada</label>
                        </div>
                    </div>

                    <div class="form-group col-6">
                        <label for="accion-realizada">Acción Realizada:</label>
                        <textarea id="accion-realizada" name="accion_realizada" placeholder="Detalle las medidas tomadas inmediatamente..."></textarea>
                    </div>
                    <div class="form-group col-6">
                        <label for="observaciones">Observaciones:</label>
                        <textarea id="observaciones" name="observaciones" placeholder="Comentarios adicionales..."></textarea>
                    </div>
                </div>

                </section>
                <section class="form-section" aria-labelledby="titulo-cierre-firma">
                <h2 class="titulo-gold" id="titulo-cierre-firma">Cierre y Firma</h2>
                <div class="grid">
                    <div class="form-group col-12">
                        <label>Adjuntar Firma (Imagen / PDF):</label>
                        <div class="file-upload-box">
                            <label for="archivo" class="file-upload-label" id="label-file">Selecciona un archivo PDF o una imagen</label>
                            <input type="file" id="archivo" name="archivo" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp" aria-describedby="label-file" onchange="document.getElementById('label-file').innerText = this.files[0] ? 'Archivo seleccionado: ' + this.files[0].name : 'Selecciona un archivo PDF o una imagen'">
                        </div>
                    </div>
                </div>

                </section>
                <button type="submit" class="btn-gold-pro"><i class="fa-solid fa-floppy-disk me-2" aria-hidden="true"></i>Registrar Incidencia</button>
            </form>
        </div>
    </main>
</div>

<script>
(function () {
    function ajustarPagina() {
        var nav = document.querySelector('nav.navbar');
        var page = document.querySelector('.page');
        if (!nav || !page) return;

        page.style.marginTop = '0px';

        var esFija = window.getComputedStyle(nav).position === 'fixed';
        var scroll = window.pageYOffset || 0;
        var finNav = nav.getBoundingClientRect().bottom + (esFija ? 0 : scroll);
        var inicioPage = page.getBoundingClientRect().top + scroll;

        page.style.marginTop = (finNav - inicioPage) + 'px';
    }

    window.addEventListener('load', ajustarPagina);
    window.addEventListener('resize', ajustarPagina);
})();
</script>

<?php
include_once 'pie.php';
?>
