<?php
require_once("../../../logica/Agenda.php");

if (!isset($_SESSION)) {
    session_start();
}

$idEmpleado = $_SESSION["id"] ?? null;
$fechaInicio = $_GET['start'] ?? null;
$fechaFin = $_GET['end'] ?? null;

if (!$idEmpleado || !$fechaInicio || !$fechaFin) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Parámetros de empleado o fecha faltantes.']);
    exit();
}

try {
   
    $datosAgenda = Agenda::consultarAgendaPorEmpleado($idEmpleado, $fechaInicio, $fechaFin);
 
    header('Content-Type: application/json');
    echo json_encode($datosAgenda);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error en el servidor o la base de datos: ' . $e->getMessage()]);
}

?>