<?php
ob_start();

if (!isset($_SESSION)) {
    session_start();
}

require_once(__DIR__ . "/../../fpdf/fpdf.php");
require_once(__DIR__ . "/../../logica/Servicio.php");

$servicios = Servicio::consultarServiciosCompletos();


$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 15);

$pdf->SetY(10);
$pdf->SetFont("Arial", "B", 20);
$pdf->SetTextColor(30, 30, 100);
$pdf->Cell(0, 15, utf8_decode("Historial de Servicios Registrados"), 0, 1, "C");

$pdf->Ln(5);
$pdf->SetFont("Arial", "I", 12);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 7, utf8_decode("Generado por Gerencia el: ") . date("Y-m-d H:i:s"), 0, 1, "L");
$pdf->Cell(0, 7, utf8_decode("Total de Servicios: ") . count($servicios), 0, 1, "L");

$pdf->Ln(10);


$pdf->SetFont("Arial", "B", 14);
$pdf->SetFillColor(150, 180, 255);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 10, utf8_decode(" Listado Detallado de Servicios"), 0, 1, "L", true);
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0);

if (empty($servicios)) {
    $pdf->SetFont("Arial", "I", 12);
    $pdf->Cell(0, 10, utf8_decode("No hay servicios registrados para generar el reporte."), 0, 1, "C");
} else {
    $pdf->SetFont("Arial", "B", 9);
    $pdf->SetFillColor(230, 230, 250);
    
    $anchoServicios = [15, 55, 25, 80, 15]; 
    $pdf->Cell($anchoServicios[0], 8, "ID", 1, 0, 'C', true);
    $pdf->Cell($anchoServicios[1], 8, utf8_decode("Nombre"), 1, 0, 'C', true);
    $pdf->Cell($anchoServicios[2], 8, utf8_decode("Precio"), 1, 0, 'C', true);
    $pdf->Cell($anchoServicios[3], 8, utf8_decode("Descripción Breve"), 1, 0, 'C', true);
    $pdf->Cell($anchoServicios[4], 8, utf8_decode("Estado"), 1, 1, 'C', true); 
    
    $pdf->SetFont("Arial", "", 8);
    $fill = false;
    
    foreach ($servicios as $s) {
        $estadoTexto = ($s['Estado'] == 1) ? 'Activo' : 'Inactivo';
        $precioFormateado = '$' . number_format($s['Precio'], 0, ',', '.');
        
        $descripcionCorta = substr($s['DescripcionBreve'], 0, 50); 
        if (strlen($s['DescripcionBreve']) > 50) { 
            $descripcionCorta .= "...";
        }
        
        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell($anchoServicios[0], 6, $s['idServicio'], 1, 0, 'C', $fill);
        $pdf->Cell($anchoServicios[1], 6, utf8_decode($s['Nombre']), 1, 0, 'L', $fill);
        $pdf->Cell($anchoServicios[2], 6, $precioFormateado, 1, 0, 'R', $fill);
        $pdf->Cell($anchoServicios[3], 6, utf8_decode($descripcionCorta), 1, 0, 'L', $fill);
        $pdf->Cell($anchoServicios[4], 6, utf8_decode($estadoTexto), 1, 1, 'C', $fill); 
        
        $fill = !$fill;
    }
}

ob_end_clean();
$pdf->Output("I", "Reporte_Servicios_" . date("Ymd") . ".pdf");
exit();
?>