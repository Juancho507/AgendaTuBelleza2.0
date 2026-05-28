<?php
session_start();
require_once("../../../logica/Agenda.php");

header('Content-Type: application/json');

$idEmpleado = $_SESSION["id"] ?? null;
$action = $_POST['action'] ?? null;

if (!$idEmpleado || $idEmpleado != $_POST['idEmpleado'] || $action !== 'create') {
    echo json_encode(['success' => false, 'error' => 'Acción o permisos no válidos.']);
    exit();
}

$startDateTime = new DateTime($_POST['start']);
$endDateTime = new DateTime($_POST['end']);

$fecha = $startDateTime->format('Y-m-d');
$horaInicio = $startDateTime->format('H:i:s');
$horaFin = $endDateTime->format('H:i:s');
$servicioId = $_POST['servicioId'];


try {
    $agenda = new Agenda(
        fecha: $fecha,
        horaInicio: $horaInicio,
        horaFin: $horaFin,
        empleadoId: $idEmpleado,
        servicioId: $servicioId
        );
    
    $resultado = $agenda->registrar(); 
    
    if ($resultado) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo registrar en la BD.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error de servidor: ' . $e->getMessage()]);
}

?>