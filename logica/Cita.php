<?php
require_once(__DIR__ . "/../persistencia/CitaDAO.php");
require_once(__DIR__ . "/../persistencia/Conexion.php");

class Cita {
    private $idCita;
    private $estadoCitaId;
    private $agendaId;
    private $empleadoId;
    private $clienteId;
    private $servicioId;
    private $comentarios;
    
    public function __construct(
        $idCita = "",
        $estadoCitaId = "",
        $agendaId = "",
        $empleadoId = "",
        $clienteId = "",
        $servicioId = "",
        $comentarios = ""
        ) {
            $this->idCita = $idCita;
            $this->estadoCitaId = $estadoCitaId;
            $this->agendaId = $agendaId;
            $this->empleadoId = $empleadoId;
            $this->clienteId = $clienteId;
            $this->servicioId = $servicioId;
            $this->comentarios = $comentarios;
    }
    
    public function getIdCita() { return $this->idCita; }
    public function getEstadoCitaId() { return $this->estadoCitaId; }
    public function getAgendaId() { return $this->agendaId; }
    public function getEmpleadoId() { return $this->empleadoId; }
    public function getClienteId() { return $this->clienteId; }
    public function getServicioId() { return $this->servicioId; }
    public function getComentarios() { return $this->comentarios; }
    
    
    public function registrar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $citaDAO = new CitaDAO(
            $this->estadoCitaId,
            $this->agendaId,
            $this->empleadoId,
            $this->clienteId,
            $this->servicioId,
            $this->comentarios
            );
        $conexion->ejecutar($citaDAO->registrar());
        $conexion->cerrar();
        return $conexion->getResultado();
    }
    
    public function consultar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $citaDAO = new CitaDAO($this->idCita);
        $conexion->ejecutar($citaDAO->consultar());
        $registro = $conexion->registro();
        $conexion->cerrar();
        
        if ($registro) {
            $this->estadoCitaId = $registro['EstadoCita_idEstadoCita'];
            $this->agendaId = $registro['Agenda_idAgenda'];
            $this->empleadoId = $registro['Empleado_idEmpleado'];
            $this->clienteId = $registro['Cliente_idCliente'];
            $this->servicioId = $registro['Servicio_idServicio'];
            $this->comentarios = $registro['comentarios'];
            return true;
        }
        return false;
    }
    
    
    public static function consultarTodos() {
        $conexion = new Conexion();
        $conexion->abrir();
        $citaDAO = new CitaDAO();
        $conexion->ejecutar($citaDAO->consultarTodos());
        $datos = $conexion->getResultado();
        $conexion->cerrar();
        return $datos;
    }
    
    public static function hayCitasActivasAsociadas($idServicio) {
        $conexion = new Conexion();
        $conexion->abrir();
        $citaDAO = new CitaDAO();
        $conexion->ejecutar($citaDAO->verificarCitasActivasPorServicio($idServicio));
        
        $registro = $conexion->registro();
        $cantidadCitasActivas = $registro[0];
        
        $conexion->cerrar();
        return ($cantidadCitasActivas > 0);
    }
    
    public static function consultarCitasFiltradas($idEmpleado, $fInicio, $fFin, $servicioId, $estadoId) {
        $conexion = new Conexion();
        try {
            $conexion->abrir();
            $citaDAO = new CitaDAO();
            
            $sql = $citaDAO->consultarCitasFiltradas($idEmpleado, $fInicio, $fFin, $servicioId, $estadoId);
            $conexion->ejecutar($sql);
            
            $citas = [];
            $resultado = $conexion->getResultado();
            
            if ($resultado instanceof mysqli_result) {
                while ($fila = $resultado->fetch_assoc()) {
                    $citas[] = $fila;
                }
            }
            $conexion->cerrar();
            return ['success' => true, 'data' => $citas];
            
        } catch (Exception $e) {
            $conexion->cerrar();
            return ['success' => false, 'error' => 'Error de BD: ' . $e->getMessage()];
        }
    }
    
    public static function verificarConflicto($idEmpleado, $fecha, $horaInicio, $horaFin) {
        $conexion = new Conexion();
        try {
            $conexion->abrir();
            $citaDAO = new CitaDAO();
            
            $sql = $citaDAO->verificarConflictoSQL($idEmpleado, $fecha, $horaInicio, $horaFin);
            $conexion->ejecutar($sql);
            
            $resultado = $conexion->getResultado();
            $fila = $resultado->fetch_assoc();
            
            $conexion->cerrar();
            
            return $fila['conteo'] > 0;
            
        } catch (Exception $e) {
            $conexion->cerrar();
            throw new Exception("Error de validación de conflicto: " . $e->getMessage());
        }
    }
    
    public static function aceptarCita($idCita, $idEmpleado, $fecha, $horaInicio, $horaFin) {
        try {
            if (self::verificarConflicto($idEmpleado, $fecha, $horaInicio, $horaFin)) {
                return ['success' => false, 'error' => "No puede aceptar esta cita, ya tiene un servicio programado en este horario."];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => "Error de validación: " . $e->getMessage()];
        }
        
        $conexion = new Conexion();
        try {
            $conexion->abrir();
            $citaDAO = new CitaDAO();
            
            $sql = $citaDAO->actualizarEstado($idCita, 1, "Cita aceptada por el empleado.");
            $conexion->ejecutar($sql);
            
            $conexion->cerrar();
            return ['success' => true, 'message' => "Cita aceptada correctamente."];
            
        } catch (Exception $e) {
            $conexion->cerrar();
            return ['success' => false, 'error' => "Error al actualizar la cita: " . $e->getMessage()];
        }
    }
    
    public static function rechazarCita($idCita) {
        $conexion = new Conexion();
        try {
            $conexion->abrir();
            $citaDAO = new CitaDAO();
            
            $sql = $citaDAO->actualizarEstado($idCita, 4, "Cita rechazada por el empleado.");
            $conexion->ejecutar($sql);
            
            $conexion->cerrar();
            return ['success' => true, 'message' => "Cita rechazada correctamente. Se notificó al cliente."];
            
        } catch (Exception $e) {
            $conexion->cerrar();
            return ['success' => false, 'error' => "Error al rechazar la cita: " . $e->getMessage()];
        }
    }
    
    public static function consultarPendientesPorEmpleado($idEmpleado) {
        $conexion = new Conexion();
        try {
            $conexion->abrir();
            $citaDAO = new CitaDAO();
            
            $sql = $citaDAO->consultarPendientesPorEmpleado($idEmpleado);
            $conexion->ejecutar($sql);
            
            $solicitudes = [];
            $resultado = $conexion->getResultado();
            
            if ($resultado instanceof mysqli_result) {
                while ($fila = $resultado->fetch_assoc()) {
                    $solicitudes[] = $fila;
                }
            }
            $conexion->cerrar();
            return $solicitudes;
            
        } catch (Exception $e) {
            $conexion->cerrar();
            
            return ['error' => 'Error al consultar pendientes: ' . $e->getMessage()];
        }
    }
    
    public static function finalizarCita($idCita) {
        $conexion = new Conexion();
        try {
            $conexion->abrir();
            $citaDAO = new CitaDAO();
            $conexion->ejecutar($citaDAO->finalizarCita($idCita));
            $conexion->cerrar();
            return ['success' => true, 'message' => "Cita finalizada correctamente."];
        } catch (Exception $e) {
            $conexion->cerrar();
            return ['success' => false, 'error' => "Error al finalizar la cita: " . $e->getMessage()];
        }
    }
    
    public static function noAsistioCita($idCita) {
        $conexion = new Conexion();
        try {
            $conexion->abrir();
            $citaDAO = new CitaDAO();
            $conexion->ejecutar($citaDAO->noAsistioCita($idCita));
            $conexion->cerrar();
            return ['success' => true, 'message' => "Cita marcada como 'No Asistió'."];
        } catch (Exception $e) {
            $conexion->cerrar();
            return ['success' => false, 'error' => "Error al marcar 'No Asistió': " . $e->getMessage()];
        }
    }
    
    public static function consultarActivasHoyPorEmpleado($idEmpleado) {
        $conexion = new Conexion();
        try {
            $conexion->abrir();
            $citaDAO = new CitaDAO();
            $sql = $citaDAO->consultarActivasHoyPorEmpleado($idEmpleado, null); 
            $conexion->ejecutar($sql);
            
            $citasActivas = [];
            $resultado = $conexion->getResultado();
            
            if ($resultado instanceof mysqli_result) {
                while ($fila = $resultado->fetch_assoc()) {
                    $citasActivas[] = $fila;
                }
            }
            $conexion->cerrar();
            return $citasActivas;
            
        } catch (Exception $e) {
            $conexion->cerrar();
            return ['error' => 'Error al consultar citas activas: ' . $e->getMessage()];
        }
    }
}
?>