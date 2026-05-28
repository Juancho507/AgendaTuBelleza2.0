<?php
ob_start();

if (!isset($_SESSION)) {
    session_start();
}

require_once(__DIR__ . "/../../fpdf/fpdf.php");
require_once(__DIR__ . "/../../logica/Empleado.php");
require_once(__DIR__ . "/../../logica/Cita.php");


if ($_SESSION["rol"] != "empleado" && $_SESSION["rol"] != "gerente") {
    die("Acceso no autorizado.");
}

$idEmpleado = $_GET['idEmpleado'] ?? null;
$fechaInicio = $_GET['fechaInicio'] ?? null;
$fechaFin = $_GET['fechaFin'] ?? null;
$servicioId = $_GET['servicioId'] ?? null; 
$estadoId = $_GET['estadoId'] ?? null;     

if (!$idEmpleado || !$fechaInicio || !$fechaFin) {
    die("Parámetros de reporte (empleado o fechas) incompletos.");
}
$empleado = new Empleado($idEmpleado);
if (!$empleado->consultar()) {
    die("Empleado no encontrado.");
}
$nombreEmpleado = $empleado->getNombre() . ' ' . $empleado->getApellido();

$resultadoCitas = Cita::consultarCitasFiltradas($idEmpleado, $fechaInicio, $fechaFin, $servicioId, $estadoId);

if (!$resultadoCitas['success']) {
    die("Error al consultar las citas para el reporte: " . $resultadoCitas['error']);
}

$citas_list = $resultadoCitas['data'];
$pdf = new FPDF();
$pdf->AddPage('L'); 
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 15);


$pdf->SetY(10);
$pdf->SetFont("Arial", "B", 20);
$pdf->SetTextColor(30, 30, 100);
$pdf->Cell(0, 15, utf8_decode("Reporte de Citas"), 0, 1, "C");


$pdf->Ln(5);
$pdf->SetFont("Arial", "I", 12);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 7, utf8_decode("Generado el: ") . date("Y-m-d H:i:s"), 0, 1, "L");
$pdf->Cell(0, 7, utf8_decode("Empleado: ") . utf8_decode($nombreEmpleado) . " (ID: " . $idEmpleado . ")", 0, 1, "L");
$pdf->Cell(0, 7, utf8_decode("Rango de Fechas: ") . $fechaInicio . " a " . $fechaFin, 0, 1, "L");


$filtroServicio = empty($servicioId) ? 'Todos' : $servicioId;
$filtroEstado = empty($estadoId) ? 'Todos' : $estadoId;       
$pdf->Cell(0, 7, utf8_decode("Filtros: Servicio ID: {$filtroServicio} | Estado ID: {$filtroEstado}"), 0, 1, "L");

$pdf->Cell(0, 7, utf8_decode("Total de Citas Encontradas: ") . count($citas_list), 0, 1, "L");

$pdf->Ln(10);

$pdf->SetFont("Arial", "B", 14);
$pdf->SetFillColor(150, 180, 255);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 10, utf8_decode(" Listado Detallado de Citas"), 0, 1, "L", true);
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0);

if (empty($citas_list)) {
    $pdf->SetFont("Arial", "I", 12);
    $pdf->Cell(0, 10, utf8_decode("No se encontraron citas con los filtros aplicados en el rango de fechas seleccionado."), 0, 1, "C");
} else {
    $anchoAgenda = [25, 25, 80, 80, 47];
    
    $pdf->SetFont("Arial", "B", 10);
    $pdf->SetFillColor(230, 230, 250);
    
    $pdf->Cell($anchoAgenda[0], 8, utf8_decode("Fecha"), 1, 0, 'C', true);
    $pdf->Cell($anchoAgenda[1], 8, utf8_decode("H. Inicio"), 1, 0, 'C', true);
    $pdf->Cell($anchoAgenda[2], 8, utf8_decode("Cliente"), 1, 0, 'C', true);
    $pdf->Cell($anchoAgenda[3], 8, utf8_decode("Servicio"), 1, 0, 'C', true);
    $pdf->Cell($anchoAgenda[4], 8, utf8_decode("Estado"), 1, 1, 'C', true);
    
    $pdf->SetFont("Arial", "", 9);
    $fill = false;
    
    foreach ($citas_list as $cita) {
        $estadoTexto = utf8_decode($cita['NombreEstado'] ?? 'N/A');
        $servicioTexto = utf8_decode($cita['NombreServicio'] ?? 'N/A');
        $clienteTexto = utf8_decode($cita['NombreCliente'] ?? 'N/A');
        
        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell($anchoAgenda[0], 6, substr($cita['Fecha'], 0, 10), 1, 0, 'C', $fill);
        $pdf->Cell($anchoAgenda[1], 6, substr($cita['HoraInicio'], 0, 5), 1, 0, 'C', $fill);
        $pdf->Cell($anchoAgenda[2], 6, $clienteTexto, 1, 0, 'L', $fill);
        $pdf->Cell($anchoAgenda[3], 6, $servicioTexto, 1, 0, 'L', $fill);
        $pdf->Cell($anchoAgenda[4], 6, $estadoTexto, 1, 1, 'C', $fill);
        
        $fill = !$fill;
    }
}

ob_end_clean();
$pdf->Output("I", "Reporte_Citas_Empleado_" . $idEmpleado . "_" . date("Ymd") . ".pdf");
exit();
?>