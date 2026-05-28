<?php

ob_start();

if (!isset($_SESSION)) {
    session_start();
}

require_once(__DIR__ . "/../../fpdf/fpdf.php");
require_once(__DIR__ . "/../../logica/Empleado.php");
require_once(__DIR__ . "/../../logica/PQRS.php"); 
if ($_SESSION["rol"] != "empleado" && $_SESSION["rol"] != "gerente") {
    die("Acceso no autorizado.");
}

if (!isset($_GET['empleado']) || empty($_GET['empleado'])) {
    die("ID de empleado no especificado.");
}
$idEmpleado = (int)$_GET['empleado'];

$empleado = new Empleado($idEmpleado);
if (!$empleado->consultar()) {
    die("Empleado no encontrado.");
}

$pqrs_list = $empleado->consultarPQRS();
$nombreEmpleado = $empleado->getNombre() . ' ' . $empleado->getApellido();

if (isset($pqrs_list['error'])) {
    die("Error al consultar los PQRS para el reporte: " . $pqrs_list['error']);
}


$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 15);

$pdf->SetY(10);
$pdf->SetFont("Arial", "B", 20);
$pdf->SetTextColor(30, 30, 100);
$pdf->Cell(0, 15, utf8_decode("Reporte Consolidado de Retroalimentación"), 0, 1, "C");

$pdf->Ln(5);
$pdf->SetFont("Arial", "I", 12);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 7, utf8_decode("Generado el: ") . date("Y-m-d H:i:s"), 0, 1, "L");
$pdf->Cell(0, 7, utf8_decode("Empleado: ") . utf8_decode($nombreEmpleado) . " (ID: " . $idEmpleado . ")", 0, 1, "L");
$pdf->Cell(0, 7, utf8_decode("Total de PQRS: ") . count($pqrs_list), 0, 1, "L");
$pdf->Cell(0, 7, utf8_decode("Promedio General: N/A (No hay calificación directa)"), 0, 1, "L");


$pdf->Ln(10);

$pdf->SetFont("Arial", "B", 14);
$pdf->SetFillColor(150, 180, 255); 
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 10, utf8_decode(" Listado Detallado de Retroalimentación"), 0, 1, "L", true);
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0);

if (empty($pqrs_list)) {
    $pdf->SetFont("Arial", "I", 12);
    $pdf->Cell(0, 10, utf8_decode("No hay PQRS registrados para este empleado."), 0, 1, "C");
} else {
    $anchoPQRS = [15, 30, 35, 25, 85];
    
    $pdf->SetFont("Arial", "B", 9);
    $pdf->SetFillColor(230, 230, 250);
    
    $pdf->Cell($anchoPQRS[0], 8, "ID", 1, 0, 'C', true);
    $pdf->Cell($anchoPQRS[1], 8, utf8_decode("Tipo"), 1, 0, 'C', true);
    $pdf->Cell($anchoPQRS[2], 8, utf8_decode("Cliente"), 1, 0, 'C', true);
    $pdf->Cell($anchoPQRS[3], 8, utf8_decode("Fecha"), 1, 0, 'C', true);
    $pdf->Cell($anchoPQRS[4], 8, utf8_decode("Descripción (y Evidencia)"), 1, 1, 'C', true); 
    
    $pdf->SetFont("Arial", "", 8);
    $fill = false;
    
    foreach ($pqrs_list as $p) {
        $evidenciaTexto = !empty($p['Evidencia']) ? " (Evidencia: Sí)" : "";
        $descripcionCompleta = utf8_decode(substr($p['Descripcion'], 0, 65)); 
        if (strlen($p['Descripcion']) > 65) { 
            $descripcionCompleta .= "...";
        }
        $descripcionCompleta .= utf8_decode($evidenciaTexto);
        
        $pdf->SetFillColor(245, 245, 245);
        
        $altura_descripcion = $pdf->GetStringWidth($descripcionCompleta) > ($anchoPQRS[4] - 2) ? 8 : 6;
        $h = 6; 
        $pdf->Cell($anchoPQRS[0], $h, $p['idPQRS'], 1, 0, 'C', $fill);
        $pdf->Cell($anchoPQRS[1], $h, utf8_decode($p['TipoPQRSNombre']), 1, 0, 'L', $fill);
        $pdf->Cell($anchoPQRS[2], $h, utf8_decode($p['ClienteNombre']), 1, 0, 'L', $fill);
        $pdf->Cell($anchoPQRS[3], $h, substr($p['Fecha'], 0, 10), 1, 0, 'C', $fill); 

        $pdf->Cell($anchoPQRS[4], $h, $descripcionCompleta, 1, 1, 'L', $fill);
        
        $fill = !$fill;
    }
}

ob_end_clean();
$pdf->Output("I", "Reporte_PQRS_Empleado_" . $idEmpleado . "_" . date("Ymd") . ".pdf");
exit();
?>