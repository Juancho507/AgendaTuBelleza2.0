<?php

if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "empleado") {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado.']);
    exit();
}

require_once(__DIR__ . "/../../../logica/Cita.php");

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'error' => ''];

$action = $_POST['action'] ?? null;
$idCita = $_POST['idCita'] ?? null;

if (!$action || !$idCita) {
    $response['error'] = 'Parámetros incompletos (Acción o ID de Cita).';
    echo json_encode($response);
    exit;
}

try {
    $idCita = intval($idCita);
    
    switch ($action) {
        
        case 'aceptar':
            $idEmpleado = $_POST['idEmpleado'] ?? null;
            $fecha = $_POST['fecha'] ?? null;
            $horaInicio = $_POST['horaInicio'] ?? null;
            $horaFin = $_POST['horaFin'] ?? null;
            
            if (!$idEmpleado || !$fecha || !$horaInicio || !$horaFin) {
                throw new Exception("Datos de aceptación incompletos.");
            }
            
            $result = Cita::aceptarCita($idCita, intval($idEmpleado), $fecha, $horaInicio, $horaFin);
            $response = $result;
            break;
            
        case 'rechazar':
            $result = Cita::rechazarCita($idCita);
            $response = $result;
            break;
            
        case 'finalizar':
            $result = Cita::finalizarCita($idCita);
            $response = $result;
            break;
            
        case 'noAsistio':
            $result = Cita::noAsistioCita($idCita);
            $response = $result;
            break;
            
        default:
            $response['error'] = 'Acción no válida o desconocida.';
            break;
    }
    
} catch (Exception $e) {
    $response['error'] = "Error en la lógica de Cita: " . $e->getMessage();
}

echo json_encode($response);
?>