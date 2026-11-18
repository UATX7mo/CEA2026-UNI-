<?php
require_once __DIR__ . '/acceso.php';



require_once __DIR__ . '/Conexion.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM incidencias WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$incidencia = $stmt->fetch();

if (!$incidencia) {
    die("La incidencia solicitada no existe.");
}

/**
 * Recorta automáticamente los márgenes en blanco/transparentes de una imagen
 * y la devuelve como PNG en base64 (data URI).
 * Así la firma queda pegada a la raya sin importar los márgenes del archivo subido.
 * Si la extensión GD no está disponible o algo falla, devuelve null.
 */
function firma_recortar($datos)
{
    if (!function_exists('imagecreatefromstring') || !function_exists('imagecropauto')) {
        return null;
    }

    $im = @imagecreatefromstring($datos);
    if (!$im) {
        return null;
    }

    // Reducir si la imagen es enorme (evita consumir demasiada memoria)
    if (imagesx($im) > 1600) {
        $esc = @imagescale($im, 1600);
        if ($esc) { imagedestroy($im); $im = $esc; }
    }

    // Convierte el fondo blanco de escaneos/fotos en transparencia gradual.
    imagealphablending($im, false);
    $ancho = imagesx($im);
    $alto = imagesy($im);
    for ($y = 0; $y < $alto; $y++) {
        for ($x = 0; $x < $ancho; $x++) {
            $color = imagecolorat($im, $x, $y);
            $rojo = ($color >> 16) & 0xFF;
            $verde = ($color >> 8) & 0xFF;
            $azul = $color & 0xFF;
            $alphaActual = ($color >> 24) & 0x7F;
            $brillo = (int) round(0.299 * $rojo + 0.587 * $verde + 0.114 * $azul);

            if ($brillo > 210) {
                $alphaFondo = $brillo >= 245 ? 127 : (int) round((($brillo - 210) / 35) * 127);
                $alpha = max($alphaActual, $alphaFondo);
                if ($alpha !== $alphaActual) {
                    imagesetpixel($im, $x, $y, imagecolorallocatealpha($im, $rojo, $verde, $azul, $alpha));
                }
            }
        }
    }

    // Recorta los márgenes que quedaron transparentes.
    $c = @imagecropauto($im, IMG_CROP_TRANSPARENT);
    if ($c !== false) { imagedestroy($im); $im = $c; }

    imagesavealpha($im, true);
    ob_start();
    imagepng($im);
    $png = ob_get_clean();
    imagedestroy($im);

    return $png ? 'data:image/png;base64,' . base64_encode($png) : null;
}

// ---- Firma (imagen o PDF) ----
$firma_img = '';   // data URI si es imagen
$firma_pdf = '';   // base64 si es PDF

if (!empty($incidencia['archivo_evidencia'])) {
    // basename evita rutas duplicadas tipo "uploads/uploads/firma.jpg"
    $archivo = basename(trim($incidencia['archivo_evidencia']));

    $candidatas = [
        __DIR__ . '/uploads/' . $archivo,
        __DIR__ . '/' . trim($incidencia['archivo_evidencia']),
    ];

    $ruta_final = '';
    foreach ($candidatas as $ruta) {
        if (is_file($ruta)) { $ruta_final = $ruta; break; }
    }

    if ($ruta_final !== '') {
        $ext   = strtolower(pathinfo($ruta_final, PATHINFO_EXTENSION));
        $datos = file_get_contents($ruta_final);

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $mimes = [
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'webp' => 'image/webp',
            ];
            // Intenta recortar márgenes; si no se puede, usa la imagen original
            $firma_img = firma_recortar($datos)
                ?: 'data:' . $mimes[$ext] . ';base64,' . base64_encode($datos);
        } elseif ($ext === 'pdf') {
            $firma_pdf = base64_encode($datos);
        }
    }
}

// Si viene con ?auto=1 (abierto desde registrar), evitamos incluir el encabezado y pie global para que no aparezca nada estorbando al imprimir
$es_auto = isset($_GET['auto']) && $_GET['auto'] == '1';

if (!$es_auto) {
    include 'encabezado.php';
}
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
        --success: #16A34A;
        --danger: #DC2626;
        --shadow-pro: 0 20px 40px -15px rgba(0, 0, 0, 0.15);
    }

    /* Reset limitado a tu contenido */
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

    <?php if (!$es_auto): ?>
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
    <?php endif; ?>

    .page .form-area {
        flex: 1;
        width: 100%;
        display: flex;
        align-items: flex-start;
        justify-content: center;
    }

    /* ===== Estilos de la ficha ===== */
    .ficha {
        --firma-ancho: 200px;
        --firma-alto: 90px;
        --firma-separacion: 4px;

        width: 100%;
        background: #fff;
        border-radius: 20px;
        box-shadow: var(--shadow-pro);
        overflow: hidden;
        border: 1px solid var(--border-light);
        padding: 36px 48px;
    }

    /* margin: 0 elimina el encabezado/pie que agrega el navegador (fecha, URL, nº de página) */
    @page {
        size: letter;
        margin: 0;
    }

    /* Membretado: solo se ve al imprimir / guardar como PDF */
    .membrete-bg {
        display: none;
    }

    /* Envoltura que reserva el espacio del membretado en CADA página al imprimir */
    .ficha table.print-wrap {
        width: 100%;
        margin: 0;
        border: 0;
    }

    .ficha table.print-wrap > thead > tr > td,
    .ficha table.print-wrap > tbody > tr > td,
    .ficha table.print-wrap > tfoot > tr > td {
        border: 0;
        padding: 0;
        background: transparent;
    }

    .ficha .sp-top,
    .ficha .sp-bottom {
        height: 0;
    }

    @media print {
        /* Ocultar TODO el sitio (encabezado, menú, pie de página, etc.) */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            height: auto !important;
        }

        body * {
            visibility: hidden !important;
        }

        nav,
        header,
        footer,
        .navbar,
        .pie,
        .footer,
        [class*="footer"],
        [id*="footer"],
        [class*="pie-"],
        [id*="pie"],
        .no-print,
        .top-menu {
            display: none !important;
        }

        /* Mostrar únicamente la ficha */
        .ficha,
        .ficha * {
            visibility: visible !important;
        }

        .page {
            display: block !important;
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            min-height: 0 !important;
        }

        .page .form-area {
            padding: 0 !important;
            display: block !important;
        }

        /* Hoja membretada de fondo (se repite en cada página) */
        .membrete-bg {
            display: block !important;
            visibility: visible !important;
            position: fixed;
            left: 0;
            top: 0;
            width: 216mm;
            height: 279mm;
            z-index: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* La ficha respeta el logo de arriba y la franja/dirección de abajo */
        .ficha {
            position: absolute;
            z-index: 1;
            left: 0;
            top: 0;
            padding: 0 20mm;
            background: transparent !important;
            box-shadow: none;
            border: none;
            border-radius: 0;
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Espacio reservado en cada página: logo arriba, franja y dirección abajo */
        .ficha .sp-top {
            height: 40mm;
        }

        .ficha .sp-bottom {
            height: 36mm;
        }

        /* La firma va justo después del contenido; si no cabe, pasa sola a la página 2 */
        .ficha .signatures {
            margin-top: 10mm !important;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .ficha .signature-img,
        .ficha .signature-canvas {
            display: block !important;
            visibility: visible !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }

    .ficha .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 3px solid #d9a928;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    .ficha .header h1 {
        font-size: 20px;
        color: #0d131a;
        margin: 0;
    }

    .ficha .header p {
        margin: 3px 0 0;
        font-size: 12px;
        color: #718096;
    }

    .ficha .badge-id {
        background: #0d131a;
        color: #d9a928;
        padding: 8px 14px;
        font-weight: bold;
        border-radius: 6px;
    }

    .ficha .section-title {
        background: #f7fafc;
        border-left: 4px solid #d9a928;
        padding: 8px 12px;
        font-size: 13px;
        font-weight: bold;
        text-transform: uppercase;
        margin: 20px 0 10px;
        color: #1a202c;
    }

    .ficha table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .ficha th,
    .ficha td {
        border: 1px solid #e2e8f0;
        padding: 10px;
        font-size: 12px;
        text-align: left;
        color: #1a202c;
    }

    .ficha th {
        background: #edf2f7;
        width: 25%;
        color: #1a202c;
    }

    .ficha .signatures {
        margin-top: 50px;
        display: flex;
        justify-content: space-between;
        gap: 30px;
        page-break-inside: avoid;
    }

    .ficha .sign-box {
        flex: 1;
        text-align: center;
        font-size: 12px;
        color: #4a5568;
    }

    .ficha .sign-box.firma {
        flex: 1.5;
    }

    .ficha .sign-area {
        height: var(--firma-alto);
        padding-bottom: var(--firma-separacion);
        display: flex;
        align-items: flex-end;
        justify-content: center;
    }

    .ficha .sign-line {
        border-top: 2px solid #718096;
        padding-top: 8px;
        line-height: 1.5;
        color: #4a5568;
    }

    .ficha .sign-line strong {
        color: #1a202c;
    }

    .ficha .signature-img,
    .ficha .signature-canvas {
        display: block;
        width: var(--firma-ancho);
        max-width: 100%;
        height: var(--firma-alto);
        object-fit: contain;
        object-position: center bottom;
        mix-blend-mode: multiply;
    }

    @media (max-width: 700px) {
        .page {
            padding: 14px 14px 20px;
            gap: 14px;
        }

        <?php if (!$es_auto): ?>
        .page .top-menu {
            justify-content: center;
        }
        <?php endif; ?>

        .ficha {
            padding: 24px 18px;
        }
    }
.page.auto-print .no-print { display: none !important; }

@media print {
    html, body {
        width: 216mm !important;
        min-height: 279.4mm !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #f5f5f2 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .page {
        width: 216mm !important;
        min-height: 279.4mm !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        background: transparent !important;
    }

    .page .form-area {
        width: 216mm !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        display: block !important;
        background: transparent !important;
    }

    .membrete-bg {
        display: block !important;
        visibility: visible !important;
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        width: 216mm !important;
        height: 279.4mm !important;
        object-fit: fill !important;
        z-index: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .ficha {
        position: relative !important;
        inset: auto !important;
        z-index: 1 !important;
        width: 216mm !important;
        max-width: none !important;
        min-height: 279.4mm !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .ficha table.print-wrap {
        width: 100% !important;
        margin: 0 !important;
        border: 0 !important;
        border-collapse: collapse !important;
        background: transparent !important;
    }

    .ficha table.print-wrap thead { display: table-header-group !important; }
    .ficha table.print-wrap tfoot { display: table-footer-group !important; }
    .ficha .sp-top { height: 34mm !important; }
    .ficha .sp-bottom { height: 25mm !important; }

    .ficha table.print-wrap > tbody > tr > td {
        padding: 0 20mm !important;
        border: 0 !important;
        background: transparent !important;
    }

    .ficha table:not(.print-wrap) {
        width: 100% !important;
        margin: 0 0 5mm !important;
        border: 0 !important;
        border-collapse: collapse !important;
        background: transparent !important;
        page-break-inside: auto !important;
        break-inside: auto !important;
    }

    .ficha table:not(.print-wrap) tr {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .ficha table:not(.print-wrap) th,
    .ficha table:not(.print-wrap) td {
        padding: 5px 7px !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        vertical-align: top !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .ficha table:not(.print-wrap) th {
        width: 23% !important;
        color: #654f17 !important;
        font-weight: 700 !important;
    }

    .ficha .section-title {
        margin: 5mm 0 2mm !important;
        padding: 3px 8px !important;
        border: 0 !important;
        border-left: 3px solid #c9a227 !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: #262116 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .ficha .header {
        margin: 0 0 5mm !important;
        padding: 0 0 3mm !important;
        border: 0 !important;
        border-bottom: 2px solid #c9a227 !important;
        background: transparent !important;
    }

    .ficha .badge-id {
        border-radius: 3px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .ficha .no-print { display: none !important; }
    .ficha .signature-img,
    .ficha .signature-canvas {
        background: transparent !important;
        mix-blend-mode: normal !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .ficha .signatures {
        margin-top: 8mm !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
}
</style>

<!-- Hoja membretada (solo aparece al imprimir / guardar como PDF) -->
<img src="/CEA2026/admon/incidencias/membret/membretado.png" alt="" class="membrete-bg">

<div class="page<?= $es_auto ? ' auto-print' : '' ?>">
    <?php if (!$es_auto): ?>
    <!-- MENÚ HORIZONTAL -->
    <div class="top-menu">
        <div class="brand-top">
            <div class="brand-name">
                <strong>Centro Escolar Aparicio</strong>
            </div>
        </div>

        <nav class="nav-menu">
            <a href="/CEA2026/admon/incidencias/Formulario.php" class="nav-link">📝 Registrar Incidencia</a>
            <a href="/CEA2026/admon/incidencias/Registros.php" class="nav-link active">📋 Lista de Registros</a>
        </nav>
    </div>
    <?php endif; ?>

    <main class="form-area">
        <div class="ficha">
          <table class="print-wrap">
            <thead><tr><td><div class="sp-top"></div></td></tr></thead>
            <tfoot><tr><td><div class="sp-bottom"></div></td></tr></tfoot>
            <tbody><tr><td>

            <div class="no-print" style="margin-bottom: 20px; text-align: right;">
                <button onclick="window.print()" style="padding: 10px 20px; background: #d9a928; border: none; font-weight: bold; cursor: pointer; border-radius: 5px;">🖨️ Imprimir / Guardar como PDF</button>
            </div>

            <div class="header">
                <div>
                    <h1>CENTRO ESCOLAR APARICIO</h1>
                    <p><strong>FICHA DE REGISTRO DE INCIDENCIA</strong></p>
                </div>
                <div class="badge-id">FOLIO #<?= $incidencia['id'] ?></div>
            </div>

            <div class="section-title">DATOS GENERALES DE LA INCIDENCIA</div>
            <table>
                <tr>
                    <th>Fecha del Evento:</th>
                    <td><?= htmlspecialchars($incidencia['fecha_incidencia']) ?></td>
                    <th>Hora:</th>
                    <td><?= htmlspecialchars($incidencia['hora_incidencia']) ?></td>
                </tr>
                <tr>
                    <th>Lugar / Área:</th>
                    <td><?= htmlspecialchars($incidencia['area_lugar']) ?></td>
                    <th>Tipo de Incidencia:</th>
                    <td><?= htmlspecialchars($incidencia['tipo_incidencia']) ?></td>
                </tr>
                <tr>
                    <th>Descripción:</th>
                    <td colspan="3"><?= nl2br(htmlspecialchars($incidencia['descripcion'])) ?></td>
                </tr>
            </table>

            <div class="section-title">PERSONA INVOLUCRADA</div>
            <table>
                <tr>
                    <th>Nombre Completo:</th>
                    <td><?= htmlspecialchars($incidencia['nombre_involucrado']) ?></td>
                    <th>Cargo o Grupo:</th>
                    <td><?= htmlspecialchars($incidencia['cargo_grupo']) ?></td>
                </tr>
            </table>

            <div class="section-title">SEGUIMIENTO Y ATENCIÓN</div>
            <table>
                <tr>
                    <th>Responsable:</th>
                    <td><?= htmlspecialchars($incidencia['responsable_atencion']) ?></td>
                    <th>Prioridad:</th>
                    <td><strong><?= htmlspecialchars($incidencia['prioridad']) ?></strong></td>
                </tr>
                <tr>
                    <th>Estado Actual:</th>
                    <td><?= htmlspecialchars($incidencia['estado']) ?></td>
                    <th>Fecha de Cierre:</th>
                    <td><?= htmlspecialchars($incidencia['fecha_estado'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <th>Acciones Tomadas:</th>
                    <td colspan="3"><?= nl2br(htmlspecialchars($incidencia['accion_realizada'] ?? 'Ninguna registrada')) ?></td>
                </tr>
                <tr>
                    <th>Observaciones:</th>
                    <td colspan="3"><?= nl2br(htmlspecialchars($incidencia['observaciones'] ?? 'Sin observaciones adicionales')) ?></td>
                </tr>
            </table>

            <div class="signatures">
                <div class="sign-box firma">
                    <div class="sign-area">
                        <?php if ($firma_img !== ''): ?>
                            <img id="firmaImagen" src="<?= $firma_img ?>" alt="Firma" class="signature-img">
                        <?php elseif ($firma_pdf !== ''): ?>
                            <canvas id="firmaCanvas" class="signature-canvas"></canvas>
                        <?php endif; ?>
                    </div>
                    <div class="sign-line">
                        Firma del Responsable<br>
                        <strong><?= htmlspecialchars($incidencia['responsable_atencion']) ?></strong>
                    </div>
                </div>
                <div class="sign-box">
                    <div class="sign-area"></div>
                    <div class="sign-line">
                        Sello Institucional / Dirección
                    </div>
                </div>
            </div>

            </td></tr></tbody>
          </table>
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

<?php if ($firma_pdf !== ''): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<?php endif; ?>

<script>
    function imprimirSiAuto() {
        if (window.location.search.includes('auto=1')) window.print();
    }

    function quitarFondoBlanco(canvas) {
        var contexto = canvas.getContext('2d', { willReadFrequently: true });
        var imagen = contexto.getImageData(0, 0, canvas.width, canvas.height);
        var pixeles = imagen.data;

        for (var i = 0; i < pixeles.length; i += 4) {
            if (pixeles[i + 3] === 0) continue;
            var brillo = 0.299 * pixeles[i] + 0.587 * pixeles[i + 1] + 0.114 * pixeles[i + 2];
            if (brillo > 210) {
                var opacidadTrazo = brillo >= 245 ? 0 : (245 - brillo) / 35;
                pixeles[i + 3] = Math.round(pixeles[i + 3] * Math.max(0, Math.min(1, opacidadTrazo)));
            }
        }

        contexto.putImageData(imagen, 0, 0);
    }

    function prepararImagenFirma() {
        var elemento = document.getElementById('firmaImagen');
        if (!elemento) return Promise.resolve();

        return new Promise(function (resolver) {
            function procesar() {
                if (!elemento.naturalWidth || !elemento.naturalHeight) return resolver();
                var canvas = document.createElement('canvas');
                canvas.width = elemento.naturalWidth;
                canvas.height = elemento.naturalHeight;
                var contexto = canvas.getContext('2d');
                contexto.drawImage(elemento, 0, 0);
                quitarFondoBlanco(canvas);
                recortarCanvas(canvas);

                elemento.onload = resolver;
                elemento.onerror = resolver;
                elemento.src = canvas.toDataURL('image/png');
            }

            if (elemento.complete) procesar();
            else {
                elemento.onload = procesar;
                elemento.onerror = resolver;
            }
        });
    }

    // Recorta los márgenes blancos del canvas para que la firma quede pegada a la raya
    function recortarCanvas(canvas) {
        var ctx = canvas.getContext('2d');
        var w = canvas.width, h = canvas.height;
        var d = ctx.getImageData(0, 0, w, h).data;
        var minX = w, minY = h, maxX = -1, maxY = -1;

        for (var y = 0; y < h; y++) {
            for (var x = 0; x < w; x++) {
                var i = (y * w + x) * 4;
                if (d[i + 3] > 10 && (d[i] < 240 || d[i + 1] < 240 || d[i + 2] < 240)) {
                    if (x < minX) minX = x;
                    if (x > maxX) maxX = x;
                    if (y < minY) minY = y;
                    if (y > maxY) maxY = y;
                }
            }
        }
        if (maxX < 0) return; // página en blanco: no se recorta

        var pad = 4;
        minX = Math.max(0, minX - pad);
        minY = Math.max(0, minY - pad);
        maxX = Math.min(w - 1, maxX + pad);
        maxY = Math.min(h - 1, maxY + pad);

        var cw = maxX - minX + 1, ch = maxY - minY + 1;
        var recorte = ctx.getImageData(minX, minY, cw, ch);
        canvas.width = cw;
        canvas.height = ch;
        ctx.putImageData(recorte, 0, 0);
    }

    window.addEventListener('load', function () {
        <?php if ($firma_pdf !== ''): ?>
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        var datos = atob('<?= $firma_pdf ?>');
        var bytes = new Uint8Array(datos.length);
        for (var i = 0; i < datos.length; i++) bytes[i] = datos.charCodeAt(i);

        pdfjsLib.getDocument({ data: bytes }).promise
            .then(function (pdf) { return pdf.getPage(1); })
            .then(function (page) {
                var viewport = page.getViewport({ scale: 3 });
                var canvas = document.getElementById('firmaCanvas');
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                return page.render({
                    canvasContext: canvas.getContext('2d'),
                    viewport: viewport
                }).promise.then(function () {
                    quitarFondoBlanco(canvas);
                    recortarCanvas(canvas);
                });
            })
            .then(imprimirSiAuto)
            .catch(function (e) {
                console.error('Error al mostrar la firma PDF:', e);
                imprimirSiAuto();
            });
        <?php elseif ($firma_img !== ''): ?>
        prepararImagenFirma().then(imprimirSiAuto);
        <?php else: ?>
        imprimirSiAuto();
        <?php endif; ?>
    });
</script>

<?php
if (!$es_auto) {
    include_once 'pie.php';
}
?>
