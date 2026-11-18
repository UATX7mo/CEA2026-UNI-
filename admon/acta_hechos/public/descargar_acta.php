<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

pims_exigir_login();
pims_exigir_permiso('acta.ver_documento');

$id = filter_var(
    pims_input($_GET, 'id'),
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($id === false) {
    http_response_code(400);
    exit('Identificador de acta inválido.');
}

$fila = pims_obtener_acta($id);
if ($fila === null) {
    http_response_code(404);
    exit('No se encontró el acta solicitada.');
}

$datos = pims_fila_a_campos($fila);
$errores = pims_validar_acta($datos);
if ($errores !== []) {
    http_response_code(422);
    exit('El acta no contiene datos suficientes para generar el PDF.');
}

$rutaFpdf = PIMS_ROOT . '/app/vendor/fpdf/fpdf.php';
if (!is_file($rutaFpdf)) {
    http_response_code(503);
    exit('No está disponible el generador PDF.');
}

require_once $rutaFpdf;

function pdf_texto(string $texto): string
{
    $convertido = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $texto);
    return $convertido === false ? '' : $convertido;
}

function pdf_linea(FPDF $pdf, string $texto, float $alto = 6.2): void
{
    $pdf->MultiCell(0, $alto, pdf_texto($texto), 0, 'J');
}

$institucion = pims_institucion();
$contacto = $institucion['contacto'];
$membrete = $institucion['membretes'][$datos['nivel']] ?? '';
$rutaMembrete = PIMS_ROOT . '/public/' . ltrim($membrete, '/');

class ActaPdf extends FPDF
{
    public string $membrete = '';
    public string $folio = '';
    public string $matricula = '';

    public function Header(): void
    {
        if ($this->membrete !== '' && is_file($this->membrete)) {
            $this->Image($this->membrete, 0, 0, 216, 279.4);
        }

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(51, 51, 51);
        $this->SetXY(22, 32);
        $this->Cell(82, 4, pdf_texto('Folio: ' . $this->folio), 0, 0, 'L');
        $this->Cell(90, 4, pdf_texto('Matrícula: ' . ($this->matricula !== '' ? $this->matricula : '—')), 0, 1, 'R');
        $this->SetTextColor(0, 0, 0);
    }

    public function Footer(): void
    {
        $this->SetY(-29);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(51, 51, 51);
        $this->Cell(0, 4, pdf_texto('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
        $this->SetTextColor(0, 0, 0);
    }
}

$pdf = new ActaPdf('P', 'mm', 'Letter');
$pdf->membrete = $rutaMembrete;
$pdf->folio = $datos['folio'];
$pdf->matricula = $datos['matricula'];
$pdf->AliasNbPages();
$pdf->SetMargins(22, 40, 22);
$pdf->SetAutoPageBreak(true, 35);
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, pdf_texto('ACTA DE HECHOS'), 0, 1, 'C');
$pdf->Ln(5);

$fecha = pims_fecha_texto($datos['fecha']);
$hora = pims_hora_parte($datos['hora'], 'hora');
$minutos = pims_hora_parte($datos['hora'], 'minutos');
$horaConclusion = $datos['hora_conclusion'] !== '' ? $datos['hora_conclusion'] : '—';

$pdf->SetFont('Arial', '', 12);
pdf_linea(
    $pdf,
    'En el municipio de ' . $contacto['ciudad'] . ', ' . $contacto['estado'] .
    ', siendo las ' . ($hora !== '' ? $hora : '—') . ' horas con ' .
    ($minutos !== '' ? $minutos : '—') . ' minutos del día ' . ($fecha !== '' ? $fecha : '—') .
    ', reunidos en el ' . $institucion['nombre'] . ', ubicado en ' . $contacto['direccion'] .
    ', el (la) suscrito(a) ' . ($datos['suscriptor'] !== '' ? $datos['suscriptor'] : '—') .
    ', en calidad de director(a) del centro educativo antes mencionado, nivel ' .
    ($datos['nivel'] !== '' ? $datos['nivel'] : '—') . ', actuando como responsable del nivel.'
);
$pdf->Ln(6);

pdf_linea(
    $pdf,
    'En este momento se hace constar la presencia de la alumna o alumno: ' .
    ($datos['nombre'] !== '' ? $datos['nombre'] : '—') . ', del grado ' .
    ($datos['grado'] !== '' ? $datos['grado'] : '—') . ' y grupo ' .
    ($datos['grupo'] !== '' ? $datos['grupo'] : '—') .
    ' de este centro educativo, quien se encuentra presente en la dirección de este plantel, ' .
    'en virtud de que:'
);
$pdf->Ln(2);
pdf_linea($pdf, pims_motivo_texto($datos['motivo']) ?: '—');
$pdf->Ln(6);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 6, pdf_texto('De acuerdo con los siguientes hechos:'), 0, 1, 'L');
$pdf->SetFont('Arial', '', 12);
pdf_linea($pdf, $datos['hechos'] !== '' ? $datos['hechos'] : '—');
$pdf->Ln(6);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 6, pdf_texto('En virtud de lo anterior, se procede a:'), 0, 1, 'L');
$pdf->SetFont('Arial', '', 12);
pdf_linea($pdf, $datos['acciones'] !== '' ? $datos['acciones'] : '—');
$pdf->Ln(3);

pdf_linea(
    $pdf,
    'La presente acta se da por concluida el día ' . ($fecha !== '' ? $fecha : '—') .
    ' a las ' . $horaConclusion . ' horas. Firmando al calce los que en ella intervinieron ' .
    'y libremente lo hicieron:'
);
$pdf->Ln(22);
$anchoFirma = 75.0;
$xFirma = (216.0 - $anchoFirma) / 2.0;
$pdf->SetX($xFirma);
$pdf->Cell($anchoFirma, 0, '', 'T', 1, 'C');
$pdf->SetX($xFirma);
$pdf->Cell($anchoFirma, 6, pdf_texto('Firma'), 0, 1, 'C');

$folio = preg_replace('/[^A-Za-z0-9_-]+/', '-', $datos['folio']) ?: 'acta';
$nombreArchivo = 'Acta de hechos - ' . $folio . '.pdf';

pims_auditar('acta.descargada', $datos['folio']);
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Cache-Control: private, no-store, max-age=0');
header('Pragma: no-cache');

$pdf->Output('D', pdf_texto($nombreArchivo));
