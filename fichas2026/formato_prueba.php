<?php

include_once '../complementos/conexion_fichas.php';
use PHPMailer\PHPMailer\PHPMailer;

require '../vendor/autoload.php'; // Carga PHPMailer

require_once 'FPDF/fpdf.php';
$curp = $_GET['curp'] ?? '';
// $curp = $_POST['busca_prees'];
class PDF extends FPDF
{
    // Cabecera de página
    public function Header()
    {
        // Salto de línea
        $this->Ln(10);
    }

    // Pie de página
    public function Footer()
    {
        $this->Ln(5);
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->AliasNbPages();
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Página ').$this->PageNo().'/{nb}', 0, 0, 'C');
    }
}
$sql_leer = 'SELECT * FROM preescolar WHERE curp = ?';
$gsent = $pdo->prepare($sql_leer);
$gsent->execute([$curp]);
$resultado = $gsent->fetchAll();

foreach ($resultado as $mostrar) {
    $pdf = new FPDF('P', 'mm', [216, 356]);
    $pdf->SetFont('Arial', '', 11);
    $pdf->AddPage();
    $pdf->Ln(0);
    // encabezado
    $pdf->Image('IMG/LOGO.png', 15, 10, 28, 23, 'png');
    $pdf->SetFont('Arial', 'B', 20);
    // Movernos a la derecha
    $pdf->Cell(80);
    // Título
    $pdf->Cell(30, 10, 'Centro Escolar Aparicio', 0, 0, 'C');

    // Salto de línea
    $pdf->Ln(5);
    // Arial bold 15
    $pdf->SetFont('Arial', 'B', 11);
    // Movernos a la derecha
    $pdf->Cell(80);
    // Título
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Incorporados a la Secretaría de Educación Pública'), 0, 0, 'C');
    // Salto de línea
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(80);
    $pdf->Cell(30, 10, 'Preescolar 29PJN0014T  Primaria 29PPR0010N  Secundaria 29PES0020N', 0, 0, 'C');
    // Salto de línea
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->Cell(80);
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'FICHA DE INSCRIPCIÓN CICLO ESCOLAR 2026-2027'), 0, 0, 'C');
    // Salto de línea
    $pdf->Ln(15);

    // /////////////////////////////////////////
    $inicioX = 20;
    $inicioY = $pdf->GetY(); // Guarda posición de inicio
    // //////////////////////////////////////////////

    function txt($t)
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $t);
    }

    function seccion($pdf, $titulo)
    {
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 5, txt($titulo), 0, 1, 'L', true);
        $pdf->Ln(2);
    }

    function linea($pdf)
    {
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(6);
    }

    // Contenido

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(5);
    seccion($pdf, 'DATOS GENERALES DEL ALUMNO(A)');
    // ///////////////FILA 1////////////////////
    $pdf->Cell(15);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(25, 3, 'NOMBRE DEL ALUMNO: ', 0, 0, 'C', 0);
    $pdf->Cell(2);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(110, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['nombre_alum'].'  '.$mostrar['ape_pat'].'  '.$mostrar['ape_mat']), 0, 0, 'C', 0);
    $pdf->Cell(2);
    // ////////////FILA 2//////////////////
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(6);
    $pdf->Cell(15, 3, 'CURP: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(28, 3, $mostrar['curp']);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20);
    $pdf->Cell(15, 3, 'SEXO: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(28, 3, $mostrar['sexo']);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(5);
    $pdf->Cell(45, 3, 'FECHA DE NACIMIENTO: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 3, $mostrar['fech_nacimiento']);
    $pdf->SetFont('Arial', '', 10);
    // ///////////////FILA 3///////////////////
    $pdf->Ln(5);
    $pdf->Cell(6);
    $pdf->Cell(15, 3, 'LUGAR DE NACIMIENTO:');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(29);
    $pdf->Cell(30, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['mun_nac'].' , '.$mostrar['estado_nac'].' '), '');
    $pdf->Cell(5);
    // $pdf->Cell(20,10,);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(10);
    $pdf->Cell(25, 3, 'TEL. CASA: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 3, $mostrar['tel_casa']);
    // $pdf->Cell(12);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(30, 3, 'TEL. CELULAR: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 3, $mostrar['tel_cel']);
    // //////////////////////

    // ////////////DOMICILIO//////////////

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Ln(5);
    $pdf->Cell(5);
    seccion($pdf, 'DOMICILIO DEL ALUMNO(A)');
    // ///////////////FILA 1////////////////////

    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(40, 3, 'DOMICILIO DEL ALUMNO: ');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(22, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['calle']).', COLONIA '.iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['colonia']));
    $pdf->Cell(28, 3);
    // $pdf->Ln(5);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(5);
    $pdf->Cell(22, 3, 'MUNICIPIO: ');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(25, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['municipio'].' , '.$mostrar['estado']));

    $pdf->Cell(25);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(32, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'CÓDIGO POSTAL: '));
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 3, $mostrar['cp']);
    // ////////////DATOS ESCOLARES//////////////

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Ln(5);
    $pdf->Cell(5);
    seccion($pdf, 'DATOS ESCOLARES DEL ALUMNO(A)');
    // ///////////////FILA 1////////////////////

$pdf->Ln(5);
$pdf->SetFont('Arial','',10);
$pdf->Cell(5);
$pdf->Cell(35,3,'PARA CURSAR EL: ');
$pdf->SetFont('Arial','B',11);
$pdf->Cell(10,3,$mostrar['grado']);
$pdf->Cell(6);
$pdf->SetFont('Arial','',10);
$pdf->Cell(15,3,'GRADO,    GRUPO');
$pdf->Cell(20);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(15,3,$mostrar['grupo']);
$pdf->Cell(5);
$pdf->SetFont('Arial','',10);
$pdf->Cell(25,3,'DEL NIVEL: ');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(5,3,$mostrar['nivel']);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(5);
    //////////////////////////////////////////////
    $pdf->Cell(7);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(5);
    $pdf->Cell(60, 3, 'ESCUELA DE PROCEDENCIA: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(28, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['esc_proce']));

    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(90, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'GRADOS DE PREESCOLAR QUE CURSÓ EL ALUMNO: '));
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(5);
    $pdf->Cell(25, 3, '1ERO: '.$mostrar['grado1']);
    $pdf->Cell(20, 3, '2DO: '.$mostrar['grado2']);
    $pdf->Cell(10, 3, '3ERO: '.$mostrar['grado3']);
    // /////////////////////////////////////////
    // ///////////////DATOS DE SALUD/////////////////////

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Ln(5);
    $pdf->Cell(5);
    seccion($pdf, 'DATOS DE SALUD DEL ALUMNO(A)');
    // ///////////////FILA 1////////////////////

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(5);
    $pdf->Cell(15, 3, 'PESO: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(15, 3, $mostrar['peso']);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(30, 3, 'ALTURA: ');
    $pdf->Cell(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(15, 3, $mostrar['talla']);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(35, 3, 'TIPO DE SANGRE:');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 3, $mostrar['tipo_sangre']);
    // //////////////

    // //////////////

    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20, 3, 'ALERGIAS: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['alergias']));
    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(52, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'ENFERMEDADES CRÓNICAS: '));
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['enfermedades']));

    // ///////////////DATOS DE SALUD/////////////////////

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Ln(5);
    $pdf->Cell(5);
    seccion($pdf, 'RELIGION DEL ALUMNO(A)');
    // ///////////////RELIGION DEL ALUMNO////////////////////

    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(70, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'RELIGIÓN QUE PROFESA EL ALUMNO: '));
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(22, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['religion']));
    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(55, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'SACRAMENTOS DEL NIÑO (A): '));
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'BAUTIZMO: '));
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(12, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['bautismo']));
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(2);
    $pdf->Cell(25, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'EUCARISTÍA: '));
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(15, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['comunion']));
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(30, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'CONFIRMACIÓN: '));
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(22, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['confirmacion']));

    // /////////DATOS DEL PADRE////////////
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Ln(5);
    $pdf->Cell(5);
    seccion($pdf, 'DATOS DEL PADRE');
    // //////////////////////////////////////////
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(5);
    $pdf->Cell(30, 3, 'CURP: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(28, 3, $mostrar['curp_papa']);
    $pdf->Ln(5);
    $pdf->Cell(13);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(25, 3, 'NOMBRE DEL PADRE: ', 0, 0, 'C', 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(125, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['nombre_papa']), 0, 0, 'C', 0);
    // ////////////////
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(5);
    $pdf->Cell(45, 3, 'FECHA DE NACIMIENTO: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 3, $mostrar['fecha_nac_papa']);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(50, 3, 'LUGAR DE NACIMIENTO:');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['edo_naci_papa']));
    $pdf->SetFont('Arial', 'B', 10);
    // /////////////
    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 3, 'DOMICILIO DEL PADRE: ');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(5);
    $pdf->Cell(22, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['calle_papa']).', COLONIA '.iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['colonia_papa']));

    // ///////////////////////
    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(28, 3, 'ESTADO CIVIL: ');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(20, 3, $mostrar['civil_papa']);

    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(22, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'OCUPACIÓN: '));
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(35, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['ocupacion_papa']));
    $pdf->Cell(12);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(25, 3, 'ESCOLARIDAD: ');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(22, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['escol_papa']));
    // /////////
    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(30, 3, 'TEL. CELULAR: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(22, 3, $mostrar['tel_papa']);
    $pdf->Cell(7);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(45, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'CORREO ELECTRÓNICO: '));
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(22, 3, $mostrar['correo_papa']);

    // ////////////////////////////////

    // ///////////////////////////

    // /////////DATOS DE LA MADRE////////////
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Ln(5);
    $pdf->Cell(5);
    seccion($pdf, 'DATOS DE LA MADRE');
    // //////////////////////////////////////////

    // /////////////////////////////////////////

    // //////////////////////////////////////////////

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(5);
    $pdf->Cell(30, 3, 'CURP: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(28, 3, $mostrar['curp_mama']);
    $pdf->Ln(5);
    $pdf->Cell(15);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(25, 3, 'NOMBRE DE LA MADRE: ', 0, 0, 'C', 0);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(125, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['nombre_mama']), 0, 0, 'C', 0);
    // ////////////////
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(5);
    $pdf->Cell(45, 3, 'FECHA DE NACIMIENTO: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 3, $mostrar['fecha_nac_mama']);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(50, 3, 'LUGAR DE NACIMIENTO:');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['nacimiento_mama']));
    $pdf->SetFont('Arial', 'B', 10);

    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(50, 3, 'DOMICILIO DE LA MADRE: ');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(22, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['calle_mama']).', COLONIA '.iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['colonia_mama']));

    // ///////////////////////
    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(28, 3, 'ESTADO CIVIL: ');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(20, 3, $mostrar['civil_mama']);

    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(25, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'OCUPACIÓN: '));
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(35, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['ocupacion_mama']));

    $pdf->Cell(12);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(30, 3, 'ESCOLARIDAD: ');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(22, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['escolaridad_mama']));
    // /////////
    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(30, 3, 'TEL. CELULAR: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(22, 3, $mostrar['tel_mama']);
    $pdf->Cell(7);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(45, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'CORREO ELECTRÓNICO: '));
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(22, 3, $mostrar['correo_mama']);

    // ////////////////////////////////

    // ///////////////////////////
    // /////////DATOS DEL TUTOR////////////
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Ln(5);
    $pdf->Cell(5);
    seccion($pdf, 'DATOS DEL TUTOR');
    // //////////////////////////////////////////
    // /////////////////////////////////////////

    // //////////////////////////////////////////////

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(5);
    $pdf->Cell(30, 3, 'CURP: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(28, 3, $mostrar['curp_tutor']);
    $pdf->Ln(5);
    $pdf->Cell(13);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(25, 3, 'NOMBRE DEL TUTOR: ', 0, 0, 'C', 0);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(125, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['nombre_tutor']), 0, 0, 'C', 0);
    // ////////////////
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(5);
    $pdf->Cell(45, 3, 'FECHA DE NACIMIENTO: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 3, $mostrar['nac_tutor']);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(50, 3, 'LUGAR DE NACIMIENTO:');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['nacimiento_tutor']));
    $pdf->SetFont('Arial', 'B', 10);
    // /////////////
    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(45, 3, 'DOMICILIO DEL TUTOR: ');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(22, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['calle_tutor']).', COLONIA '.iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['colonia_tutor']));

    // ///////////////////////////////////

    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(28, 3, 'ESTADO CIVIL: ');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(20, 3, $mostrar['civil_tutor']);

    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(25, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'OCUPACIÓN: '));
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(35, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['ocupacion_tutor']));
    // $pdf->Ln(5);
    $pdf->Cell(12);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(30, 3, 'ESCOLARIDAD: ');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(22, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['escolaridad_tutor']));
    // /////////
    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(30, 3, 'TEL. CELULAR: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(22, 3, $mostrar['tel_tutor']);
    $pdf->Cell(7);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(45, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'CORREO ELECTRÓNICO: '));
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(22, 3, $mostrar['correo_tutor']);

    // ///////////RESPONSIVA////////////
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Ln(5);
    $pdf->Cell(5);
    seccion($pdf, 'RESPONSIVA');

    // /////////////////////////////////

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(5);
    $pdf->Cell(30, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '* Los recargos por atraso en las colegiaturas serán del 10% acumulativo. '));
    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->Cell(30, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '* Cualquier desperfecto que cause el alumno a la institución, mobiliario, material, equipo, etc., deberá pagarlo íntegramente.'));
    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->Cell(30, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '* Los abajo firmantes nos comprometemos a respetar los reglamentos y disposiciones de la institución'));
    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->Cell(30, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'y a colaborar con el personal docente y administrativo para lograr el máximo aprovechamiento escolar de nuestro (a) hijo (a).'));
    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->Cell(30, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '* En caso de que la inscripción sea realizada por una persona distinta a los padres de familia, se solicita pasar a control escolar'));
    $pdf->Ln(5);
    $pdf->Cell(5);
    $pdf->Cell(30, 3, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' por el formato: DECLARATORIA DE REPRESENTACIÓN.'));

    // ////////////////////////////////

    // ///////////////////////////

    $pdf->Ln(7);
    // Posición: a 1,5 cm del final
    $pdf->SetFont('Arial', 'B', 10);

    $pdf->Cell(62);
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' NOMBRE Y FIRMA DE CONFORMIDAD'));
    $pdf->Ln(7);
    $pdf->Cell(62);
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' Calpulalpan, Tlaxcala; Agosto de 2025'));

    $pdf->Ln(10);
    $pdf->Cell(50);
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' ______________________________________________'));
    $pdf->Ln(7);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(74);
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'MADRE, PADRE O TUTOR'));
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(67);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Cell(5);
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'El llenado de la presente ficha NO garantiza un lugar en nuestra institución, hasta no haber finalizado con el proceso correspondiente ante el departamento de control escolar.'));

    // ///////////////////////////credencial/////////////////
    // Pagina nueva//
    $pdf->AddPage('P', 'Letter');
    $pdf->SetFont('Arial', 'B', 16);
    // Movernos a la derecha
    $pdf->Ln(9);
    $pdf->Cell(80);

    $inicioX = 10;
    $inicioY = $pdf->GetY(); // Guarda posición de inicio
    function txt1($t1)
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $t);
    }

    function seccion1($pdf, $titulo1)
    {
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 10, txt($titulo1), 0, 1, 'L', true);
        $pdf->Ln(2);
    }

    function linea1($pdf)
    {
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(6);
    }

    // Título
    $pdf->Cell(30, 10, 'PROCESO DE CREDENCIALIZACION 2026', 0, 0, 'C');

    // Salto de línea
    $pdf->Ln(7);
    // Arial bold 15
    $pdf->SetFont('Arial', 'B', 12);
    // Movernos a la derecha
    $pdf->Cell(80);
    // Título
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'SOLICITUD DE CREDENCIAL DE ESTUDIANTE'), 0, 0, 'C');
    // Salto de línea
    // Salto de línea
    $pdf->Ln(7);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Ln(5);
    $pdf->Cell(5);
    seccion1($pdf, 'DATOS GENERALES DEL ALUMNO(A)');

    $pdf->Cell(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(25, 10, 'NOMBRE DEL ALUMNO(A): ', 0, 0, 'C', 0);
    $pdf->Cell(2);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['nombre_alum'].' '.$mostrar['ape_pat'].' '.$mostrar['ape_mat']), 0, 0, 'C', 0);
    $pdf->Cell(2);
    $pdf->Ln(7);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(9);
    $pdf->Cell(35, 10, 'PARA CURSAR EL: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(10, 10, $mostrar['grado'].' '.$mostrar['grupo']);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(25, 10, ' DEL NIVEL: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(5, 10, $mostrar['nivel']);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(7);
    // ////////////////////////////
    $pdf->Ln(7);
    $pdf->Cell(18);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(2, 10, 'IMPORTE: ', 0, 0, 'C', 0);
    $pdf->Cell(10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 10, '$85 PESOS', 0, 0, 'C', 0);
    $pdf->Cell(2);
    $pdf->Ln(12);
    $pdf->Cell(70);
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' _____________________________________________________'));
    $pdf->Ln(7);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(8);
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' FECHA DE SOLICITUD: '));
    $pdf->Cell(30);
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' FIRMA DEL ENCARGADO DEL AREA DE CREDENCIALIZACIÓN'));
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(18);
    $pdf->Cell(2, 10, $date = date('d/m/Y'), 0, 0, 'C', 0);
    $pdf->Cell(133);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(2, 10, 'COPIA PARA EL ESTUDIANTE', 0, 0, 'C', 0);
    $pdf->Ln(15);
    $pdf->Cell(10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(2, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Favor de conservar su recibo para cualquier aclaración y posterior entrega de la credencial.'));
    $pdf->Ln(7);
    $pdf->Cell(10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(2, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'La entrega de credenciales se realizará previo aviso de sus docentes de grupo.'));
    $pdf->Ln(22);
    // ///////////////////////////////////////////

    // //////////////////////MITAD DE LA PAGINA/////////////////////
    $pdf->Ln(5);                                // Salto de línea vertical
    $pdf->Cell(-20);                             // Margen izquierdo (espacio)
    $pdf->SetFont('Arial', 'B', 10);              // Fuente
    $pdf->Cell(-20, 10, '_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ ');    // Línea horizontal (probablemente fuera de los límites)

    // //////////////////////MITAD DE LA PAGINA/////////////////////
    $pdf->Ln(15);
    $inicioX = 10;
    $inicioY = $pdf->GetY(); // Guarda posición de inicio

    // ///////////PARTE 2//////////////////////////////////

    $pdf->SetFont('Arial', 'B', 16);
    // Movernos a la derecha
    $pdf->Cell(80);
    // Título
    $pdf->Cell(30, 10, 'PROCESO DE CREDENCIALIZACION 2026', 0, 0, 'C');

    // Salto de línea
    $pdf->Ln(7);
    // Arial bold 15
    $pdf->SetFont('Arial', 'B', 12);
    // Movernos a la derecha
    $pdf->Cell(80);
    // Título
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'SOLICITUD DE CREDENCIAL DE ESTUDIANTE'), 0, 0, 'C');
    // Salto de línea
    // Salto de línea

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Ln(5);
    $pdf->Cell(5);
    seccion1($pdf, 'DATOS GENERALES DEL ALUMNO(A)');

    $pdf->Cell(18);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(25, 10, 'NOMBRE DEL ALUMNO(A): ', 0, 0, 'C', 0);
    $pdf->Cell(2);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(100, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['nombre_alum'].' '.$mostrar['ape_pat'].' '.$mostrar['ape_mat']), 0, 0, 'C', 0);
    $pdf->Cell(2);
    $pdf->Ln(7);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(7);
    $pdf->Cell(35, 10, 'PARA CURSAR EL: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(10, 10, $mostrar['grado'].' '.$mostrar['grupo']);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(25, 10, ' DEL NIVEL: ');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(5, 10, $mostrar['nivel']);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(7);
    // ////////////////////////////
    $pdf->Ln(7);
    $pdf->Cell(16);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(2, 10, 'IMPORTE: ', 0, 0, 'C', 0);
    $pdf->Cell(10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 10, '$85 PESOS', 0, 0, 'C', 0);
    $pdf->Cell(2);
    $pdf->Ln(10);
    $pdf->Cell(70);
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' _______________________________________________________'));
    $pdf->Ln(7);
    $pdf->Cell(70);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(10, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' FIRMA DEL ENCARGADO DEL AREA DE CREDENCIALIZACIÓN'));
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(7);
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' FOLIO INTERNO: '));
    $pdf->Cell(30);
    $pdf->Ln(7);

    $pdf->Cell(7);
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' ESPECIFICACIONES: '));
    $pdf->Cell(10);
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' __________________________________________________________________ '));
    $pdf->Ln(7);
    $pdf->Cell(10);
    $pdf->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ' _____________________________________________________________________________________ '));

    $pdf->Ln(10);
    $pdf->Cell(157);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(2, 10, 'COPIA PARA LA INSTITUCION', 0, 0, 'C', 0);
    $pdf->Ln(10);
    // ////////////////////////////////

    // ///////////////////////////
    $pdf->Ln(15);

    // =============================
    // GENERAR NOMBRE DEL ARCHIVO
    // =============================

    // Genera nombre dinámico del PDF usando el nombre del alumno
    $nombreArchivo = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['nombre_alum']).'.pdf';

    // Ruta donde se guardará temporalmente
    $rutaPDF = 'temp/'.$nombreArchivo;

    // Guarda el PDF en el servidor en lugar de descargarlo
    $pdf->Output('F', $rutaPDF);

    // =============================
    // ENVIAR CORREO AUTOMÁTICO
    // =============================

    // Crear nueva instancia de PHPMailer
    $mail = new PHPMailer(true);

    try {
        // ================= SMTP =================

        $mail->isSMTP(); // Activa envío por SMTP
        $mail->Host = 'smtp.gmail.com'; // Servidor SMTP de Gmail
        $mail->SMTPAuth = true; // Activar autenticación
        $mail->Username = 'jorgedominguez2191@gmail.com'; // Tu correo
        $mail->Password = 'Pollito1@'; // Contraseña de aplicación
        $mail->SMTPSecure = 'tls'; // Seguridad TLS
        $mail->Port = 587; // Puerto SMTP Gmail

        // ================= REMITENTE =================

        $mail->setFrom('tucorreo@gmail.com', 'Centro Escolar Aparicio');

        // ================= DESTINATARIOS =================

        // Enviar al papá si existe correo
        if (!empty($mostrar['correo_papa'])) {
            $mail->addAddress($mostrar['correo_papa']);
        }

        // Enviar a la mamá si existe correo
        if (!empty($mostrar['correo_mama'])) {
            $mail->addAddress($mostrar['correo_mama']);
        }

        // Enviar al tutor si existe correo
        if (!empty($mostrar['correo_tutor'])) {
            $mail->addAddress($mostrar['correo_tutor']);
        }

        // ================= CONTENIDO =================

        $mail->isHTML(true); // Permitir HTML
        $mail->Subject = 'Ficha de inscripción Ciclo 2025-2026';

        // Cuerpo del mensaje
        $mail->Body = "
        Estimado padre/madre/tutor:<br><br>
        Se adjunta la ficha de inscripción del alumno:<br><br>
        <b>{$mostrar['nombre_alum']}</b><br>
        CURP: {$mostrar['curp']}<br>
        Grado: {$mostrar['grado']} {$mostrar['nivel']}<br><br>
        Gracias por confiar en Centro Escolar Aparicio.<br><br>
        Saludos cordiales.
    ";

        // ================= ADJUNTAR PDF =================

        $mail->addAttachment($rutaPDF); // Adjunta el archivo generado

        // ================= ENVIAR =================

        $mail->send(); // Envía el correo

        // ================= ELIMINAR PDF =================

        unlink($rutaPDF); // Borra el archivo temporal

        echo '✅ PDF generado y enviado correctamente.';
    } catch (Exception $e) {
        echo "❌ Error al enviar correo: {$mail->ErrorInfo}";
    }
    // $pdf->Output('D', iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $mostrar['nombre_alum']).'.pdf');
}
