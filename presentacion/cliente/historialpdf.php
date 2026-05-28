<?php
ob_start();

if (!isset($_SESSION)) {
    session_start();
}

require_once(__DIR__ . "/../../fpdf/fpdf.php");
require_once(__DIR__ . "/../../logica/Cliente.php");
require_once(__DIR__ . "/../../logica/PQRS.php");


$idCliente = $_SESSION["id"] ?? null;

if (!$idCliente) {
    exit("Acceso denegado o sesión expirada.");
}

$cliente = new Cliente($idCliente);
$cliente->consultar();
$nombreCliente = $cliente->getNombre() . " " . $cliente->getApellido();

$historialCitas = $cliente->consultarHistorialCitas();
$historialPQRS = PQRS::consultarHistorialPorCliente($idCliente);


$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);

$pdf->Image("img/logo.png", 15, 10, 30);
$pdf->SetY(25);
$pdf->SetFont("Arial", "B", 20);
$pdf->SetTextColor(30, 30, 100);
$pdf->Cell(0, 15, utf8_decode("Historial Completo del Cliente"), 0, 1, "C");

$pdf->Ln(5);
$pdf->SetFont("Arial", "I", 12);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 7, utf8_decode("Cliente: ") . utf8_decode($nombreCliente), 0, 1, "L");
$pdf->Cell(0, 7, utf8_decode("Generado el: ") . date("Y-m-d H:i:s"), 0, 1, "L");

$pdf->Ln(10);


$pdf->SetFont("Arial", "B", 16);
$pdf->SetFillColor(150, 180, 255);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 10, "1. Historial de Citas", 0, 1, "L", true);
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0);

if (empty($historialCitas)) {
    $pdf->SetFont("Arial", "I", 12);
    $pdf->Cell(0, 10, utf8_decode("El cliente no tiene citas registradas en su historial."), 0, 1, "C");
} else {
    $pdf->SetFont("Arial", "B", 10);
    $pdf->SetFillColor(230, 230, 250);
    $anchoCitas = [15, 25, 20, 20, 40, 40, 20];
    
    $pdf->Cell($anchoCitas[0], 8, "ID", 1, 0, 'C', true);
    $pdf->Cell($anchoCitas[1], 8, "Fecha", 1, 0, 'C', true);
    $pdf->Cell($anchoCitas[2], 8, "Hora Ini.", 1, 0, 'C', true);
    $pdf->Cell($anchoCitas[3], 8, "Hora Fin", 1, 0, 'C', true);
    $pdf->Cell($anchoCitas[4], 8, "Servicio", 1, 0, 'C', true);
    $pdf->Cell($anchoCitas[5], 8, "Empleado", 1, 0, 'C', true);
    $pdf->Cell($anchoCitas[6], 8, "Estado", 1, 1, 'C', true);
    
    $pdf->SetFont("Arial", "", 9);
    foreach ($historialCitas as $cita) {
        $pdf->Cell($anchoCitas[0], 6, $cita['idCita'], 1, 0, 'C');
        $pdf->Cell($anchoCitas[1], 6, $cita['Fecha'], 1, 0, 'C');
        $pdf->Cell($anchoCitas[2], 6, substr($cita['HoraInicio'], 0, 5), 1, 0, 'C');
        $pdf->Cell($anchoCitas[3], 6, substr($cita['HoraFin'], 0, 5), 1, 0, 'C');
        $pdf->Cell($anchoCitas[4], 6, utf8_decode($cita['Servicio']), 1, 0, 'L');
        $pdf->Cell($anchoCitas[5], 6, utf8_decode($cita['Empleado']), 1, 0, 'L');
        $pdf->Cell($anchoCitas[6], 6, utf8_decode($cita['Estado']), 1, 1, 'C');
    }
}

$pdf->Ln(10);


$pdf->SetFont("Arial", "B", 16);
$pdf->SetFillColor(255, 150, 150);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 10, "2. Historial de PQRS", 0, 1, "L", true);
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0);

if (empty($historialPQRS)) {
    $pdf->SetFont("Arial", "I", 12);
    $pdf->Cell(0, 10, utf8_decode("El cliente no tiene PQRS registrados."), 0, 1, "C");
} else {
    $pdf->SetFont("Arial", "B", 10);
    $pdf->SetFillColor(245, 245, 245);
    
    $anchoPQRS = [15, 30, 25, 60, 40, 10];
    
    $pdf->Cell($anchoPQRS[0], 8, "ID", 1, 0, 'C', true);
    $pdf->Cell($anchoPQRS[1], 8, "Tipo", 1, 0, 'C', true);
    $pdf->Cell($anchoPQRS[2], 8, "Fecha", 1, 0, 'C', true);
    $pdf->Cell($anchoPQRS[3], 8, utf8_decode("Descripción"), 1, 0, 'C', true);
    $pdf->Cell($anchoPQRS[4], 8, "Empleado Relac.", 1, 0, 'C', true);
    
    $pdf->Cell($anchoPQRS[5], 8, "Ev.", 1, 1, 'C', true);
    
    $pdf->SetFont("Arial", "", 9);
    foreach ($historialPQRS as $pqrs) {
        $pdf->Cell($anchoPQRS[0], 6, $pqrs['idPQRS'], 1, 0, 'C');
        $pdf->Cell($anchoPQRS[1], 6, utf8_decode($pqrs['TipoPQRS']), 1, 0, 'L');
        
        $fechaSinHora = substr($pqrs['Fecha'], 0, 10);
        $pdf->Cell($anchoPQRS[2], 6, $fechaSinHora, 1, 0, 'C');
        
        $descripcionCorta = substr($pqrs['Descripcion'], 0, 25);
        if (strlen($pqrs['Descripcion']) > 25) {
            $descripcionCorta .= "...";
        }
        $pdf->Cell($anchoPQRS[3], 6, utf8_decode($descripcionCorta), 1, 0, 'L');
        
        $pdf->Cell($anchoPQRS[4], 6, utf8_decode($pqrs['Empleado']), 1, 0, 'L');
        
        $evidenciaTexto = (!empty($pqrs['Evidencia']) && $pqrs['Evidencia'] != 'N/A' && $pqrs['Evidencia'] != 'No') ? "Sí" : "No";
        $pdf->Cell($anchoPQRS[5], 6, $evidenciaTexto, 1, 1, 'C');
    }
}

ob_end_clean();
$pdf->Output("I", "Historial_" . ($nombreCliente ? str_replace(' ', '_', $nombreCliente) : "cliente") . ".pdf");
exit();
?>