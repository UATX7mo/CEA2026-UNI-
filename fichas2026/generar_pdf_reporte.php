<?php
require_once __DIR__ . '/../admon/autorizacion.php';
if (empty($_SESSION['admin']) || !in_array(rolAdmonActual(), ['admin', 'primaria', 'secundaria', 'preescolar'], true)) {
    if (empty($_SESSION['admin'])) {
        header('Location: /CEA2026/login.php');
    } else {
        header('Location: /CEA2026/admon/' . rutaInicialAdmon(rolAdmonActual()));
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /CEA2026/admon/reportes.php');
    exit;
}

require_once __DIR__ . '/../complementos/conexion_fichas.php';

// 2. Incluir la librería FPDF
require('fpdf/fpdf.php');

// 3. Recibir los datos enviados por POST desde el formulario (reporte_alumno.php)
$nombre_alumno = isset($_POST['nombre_alumno']) ? $_POST['nombre_alumno'] : '';
$grado         = isset($_POST['grado']) ? $_POST['grado'] : '';
$grupo         = isset($_POST['grupo']) ? $_POST['grupo'] : '';
$dia           = isset($_POST['dia']) ? $_POST['dia'] : '';
$mes           = isset($_POST['mes']) ? $_POST['mes'] : '';
$anio          = isset($_POST['anio']) ? $_POST['anio'] : '';
$conducta      = isset($_POST['conducta']) ? $_POST['conducta'] : '';
$quien_reporta = isset($_POST['quien_reporta']) ? $_POST['quien_reporta'] : '';
$rolReporte = rolAdmonActual();
$nivelesValidos = ['preescolar', 'primaria', 'secundaria'];
$nivel = $rolReporte === 'admin' ? strtolower(trim((string) ($_POST['nivel'] ?? ''))) : $rolReporte;
$gradosValidosPorNivel = [
    'preescolar' => ['1°', '2°', '3°'],
    'primaria' => ['1°', '2°', '3°', '4°', '5°', '6°'],
    'secundaria' => ['1°', '2°', '3°'],
];
$mesesValidos = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$nombre_alumno = trim((string) $nombre_alumno);
$grado = trim((string) $grado);
$grupo = strtoupper(trim((string) $grupo));
$dia = trim((string) $dia);
$mes = trim((string) $mes);
$anio = trim((string) $anio);
$conducta = trim((string) $conducta);
$quien_reporta = trim((string) $quien_reporta);

if (!in_array($nivel, $nivelesValidos, true)
    || !in_array($grado, $gradosValidosPorNivel[$nivel] ?? [], true)
    || !in_array($grupo, ['A', 'B', 'C'], true)
    || !in_array($mes, $mesesValidos, true)
    || !preg_match('/^\d{1,2}$/', $dia)
    || !preg_match('/^\d{2}$/', $anio)
    || !checkdate((int) (array_search($mes, $mesesValidos, true) + 1), (int) $dia, 2000 + (int) $anio)
    || $nombre_alumno === '' || strlen($nombre_alumno) > 150
    || $conducta === '' || $quien_reporta === '' || strlen($quien_reporta) > 150
    || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u', $conducta)
    || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u', $quien_reporta)) {
    http_response_code(422);
    exit('No se pudo generar el reporte. Revisa los datos capturados e inténtalo de nuevo.');
}

$fecha_completa_pdf = "Calpulalpan Tlaxcala, A " . ($dia ? $dia : '   ') . " del " . ($mes ? $mes : '       ') . " del 20" . ($anio ? $anio : ' ');

// Guardar primero: no se entrega un PDF si el reporte no quedó registrado.
try {
    $sql = "INSERT INTO reportes_alumnos (nivel, nombre_alumno, grado, grupo, fecha_reporte, conducta, quien_reporta)
            VALUES (:nivel, :nombre, :grado, :grupo, :fecha, :conducta, :quien_reporta)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nivel' => $nivel,
        ':nombre' => $nombre_alumno,
        ':grado' => $grado,
        ':grupo' => $grupo,
        ':fecha' => $fecha_completa_pdf,
        ':conducta' => $conducta,
        ':quien_reporta' => $quien_reporta
    ]);
} catch (Throwable $error) {
    error_log('No se pudo guardar reporte de alumno: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo guardar el reporte. No se generó el PDF; inténtalo de nuevo o contacta al administrador.');
}

$nivelPDF = ucfirst($nivel);

class PDF_Reporte extends FPDF {
    function Header() {
        global $nivelPDF;
        $ruta_1 = 'IMG/Logo_reporte.jpg';
        $ruta_2 = '../IMG/Logo_reporte.jpg';
        $ruta_3 = 'IMG/logo.png';
        $ruta_4 = '../IMG/logo.png';

        // Imagen cubriendo toda la hoja tamaño carta (215.9 x 279.4 mm)
        if(file_exists($ruta_1)) {
            $this->Image($ruta_1, 0, 0, 215.9, 279.4);
        } elseif(file_exists($ruta_2)) {
            $this->Image($ruta_2, 0, 0, 215.9, 279.4);
        } elseif(file_exists($ruta_3)) {
            $this->Image($ruta_3, 0, 0, 215.9, 279.4);
        } elseif(file_exists($ruta_4)) {
            $this->Image($ruta_4, 0, 0, 215.9, 279.4);
        }
    }

    function Footer() {
        // Vacío para respetar la estructura limpia de la plantilla
    }
}

// Inicializar el documento PDF en formato Carta (Letter) vertical
$pdf = new PDF_Reporte('P', 'mm', 'Letter');
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);

// Ancho útil de la página (215.9 - 30 de márgenes = 185.9 mm)
$ancho_util = 185;

// --- ESPACIO INICIAL PARA BAJAR EL CONTENIDO ---
$pdf->Ln(20); 

// --- TÍTULO PRINCIPAL ---
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(0, 0, 0); // Letras Negras
$pdf->Cell($ancho_util, 8, utf8_decode('HOJA DE REPORTE'), 0, 1, 'C');
$pdf->Ln(6);

// --- INSTITUCIÓN ---
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($ancho_util, 6, utf8_decode('Centro Escolar Aparicio, Campus Calpulalpan.'), 0, 1, 'L');
$pdf->Ln(2);

// --- SECCIÓN DE FECHA ---
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($ancho_util, 7, utf8_decode($fecha_completa_pdf), 0, 1, 'L');
$pdf->Ln(3);

// --- TEXTO INTRODUCTORIO Y DATOS DEL ALUMNO ---
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(78, 6, utf8_decode('Se levanta el siguiente REPORTE al alumno(a):'), 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(85, 6, utf8_decode($nombre_alumno), 'B', 0, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(22, 6, utf8_decode(' del'), 0, 1, 'L');
$pdf->Ln(3);

// Grado y Grupo integrados en el párrafo de la oración
$pdf->Cell(12, 6, utf8_decode('grado'), 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(25, 6, utf8_decode($grado), 'B', 0, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(15, 6, utf8_decode(' grupo'), 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(25, 6, utf8_decode($grupo), 'B', 0, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(108, 6, utf8_decode(', por la siguiente conducta.'), 0, 1, 'L');
$pdf->Ln(5);

// --- CAJA DE CONDUCTA ---
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell($ancho_util, 6, utf8_decode($conducta), 1, 'L');
$pdf->Ln(6);

// --- NOTA AL EXPEDIENTE ---
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($ancho_util, 6, utf8_decode('La cual se anexa a su expediente.'), 0, 1, 'L');
$pdf->Ln(25); // Espacio antes de la sección de firmas para equilibrar la hoja

// --- SECCIÓN DE FIRMAS DISTRIBUIDAS (2x2) ---
$pdf->SetFont('Arial', '', 9);

// Fila 1: Izquierda (Quien reporta) | Derecha (Dirección)
$pdf->Cell(85, 6, utf8_decode($quien_reporta), 'T', 0, 'C');
$pdf->Cell(15, 6, '', 0, 0); // Espaciador central
$pdf->Cell(85, 6, utf8_decode('Directora Mtra. Benita Rosete Sánchez'), 'T', 1, 'C');

$pdf->Cell(85, 5, utf8_decode('Nombre y firma de quien reporta'), 0, 0, 'C');
$pdf->Cell(15, 5, '', 0, 0);
        $pdf->Cell(85, 5, utf8_decode('Dirección del Nivel ' . $nivelPDF), 0, 1, 'C');

$pdf->Ln(20); // Espacio vertical entre filas de firmas

// Fila 2: Izquierda (Prefectura) | Derecha (Padre o Tutor)
$pdf->Cell(85, 6, utf8_decode('Prefecta Martha Peña Zavala'), 'T', 0, 'C');
$pdf->Cell(15, 6, '', 0, 0);
$pdf->Cell(85, 6, utf8_decode(''), 'T', 1, 'C');

$pdf->Cell(85, 5, utf8_decode('Prefectura'), 0, 0, 'C');
$pdf->Cell(15, 5, '', 0, 0);
$pdf->Cell(85, 5, utf8_decode('Nombre y firma del padre o tutor'), 0, 1, 'C');

// Salida del documento PDF en el navegador
$pdf->Output('I', 'Reporte_' . urlencode($nombre_alumno) . '.pdf');
?>
