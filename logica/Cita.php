<?php
require_once(__DIR__ . "/../persistencia/CitaDAO.php");
require_once(__DIR__ . "/../persistencia/Conexion.php");

class Cita {
    private $idCita;
    private $estadoCitaId;
    private $agendaId;
    private $clienteId;
    private $comentarios;
    private $conexion;
    private $dao;
    
    public function __construct(
        $idCita = "",
        $estadoCitaId = "",
        $agendaId = "",
        $clienteId = "",
        $comentarios = ""
        ) {
            $this->idCita = $idCita;
            $this->estadoCitaId = $estadoCitaId;
            $this->agendaId = $agendaId;
            $this->clienteId = $clienteId;
            $this->comentarios = $comentarios;
            $this->conexion = new Conexion();
            $this->dao = new CitaDAO();
    }
    
    public function getIdCita() { return $this->idCita; }
    public function getEstadoCitaId() { return $this->estadoCitaId; }
    public function getAgendaId() { return $this->agendaId; }
    public function getClienteId() { return $this->clienteId; }
    public function getComentarios() { return $this->comentarios; }
    
    
  
    
  
    
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
    public static function obtenerTodasLasCitas() {
        $conexion = new Conexion();
        try {
            $conexion->abrir();
            $citaDAO = new CitaDAO();
            $conexion->ejecutar($citaDAO->obtenerCitas());
            
            $citas = array();
            $resultado = $conexion->getResultado();
            if ($resultado instanceof mysqli_result) {
                while (($registro = $resultado->fetch_assoc()) != null) {
                    $citas[] = $registro;
                }
            } else {
            }
            
            $conexion->cerrar();
            return $citas;
        } catch (Exception $e) {
            $conexion->cerrar();
            return [];
        }
    }
    public static function verificarDisponibilidadPorAgendaId($agendaId) {
        $conexion = new Conexion();
        try {
            $conexion->abrir();
            $citaDAO = new CitaDAO();
            $sql = $citaDAO->verificarFranjaLibre($agendaId);
            $conexion->ejecutar($sql);
            
            $esLibre = ($conexion->filas() === 0);
            
            $conexion->cerrar();
            return $esLibre;
            
        } catch (Exception $e) {
            $conexion->cerrar();
            error_log("Error de BD en verificación de franja: " . $e->getMessage());
            return false;
        }
    }
    
    public static function agendarNuevaCita($agendaId, $clienteId, $comentarios = "") {
        $estadoPendiente = 6;
        
        try {
            if (!self::verificarDisponibilidadPorAgendaId($agendaId)) {
                return ['success' => false, 'error' => "Esta franja ya fue reservada o tiene una cita activa."];
            }
            $conexion = new Conexion();
            $conexion->abrir();
            $citaDAO = new CitaDAO();
            
            $sql = $citaDAO->registrarNuevaCita($agendaId, $clienteId, $comentarios, $estadoPendiente);
            $conexion->ejecutar($sql);
            
            if ($conexion->afectadas() === 0) {
                $conexion->cerrar();
                return ['success' => false, 'error' => "No se pudo generar la cita. Intente de nuevo."];
            }
            
            $idCitaGenerada = $conexion->getInsertId();
            
            
            $conexion->cerrar();
            
            return ['success' => true, 'idCita' => $idCitaGenerada, 'estadoId' => $estadoPendiente, 'message' => "Solicitud de cita enviada. Esperando aprobación."];
        } catch (Exception $e) {
            return ['success' => false, 'error' => "Error de conexión: Por favor, intente de nuevo."];
        }
    }
    
        public static function obtenerHistorialCitas(int $clienteId) {
            $conexion = new Conexion();
            try {
                $conexion->abrir();
                
                $citaDao = new CitaDAO();
                $sql = $citaDao->consultarCitasPorCliente($clienteId);
                
                $conexion->ejecutar($sql);
                
                $citas = [];
                $resultado = $conexion->getResultado();
                if ($resultado instanceof mysqli_result) {
                    while (($registro = $resultado->fetch_assoc()) != null) {
                        $citas[] = $registro;
                    }
                }
                
                $conexion->cerrar();
                return $citas;
            } catch (Exception $e) {
                $conexion->cerrar();
                return [];
            }
        }
        
        public static function cancelarCita(int $citaId, int $clienteId, int $estadoCanceladaId, int $estadoLibreId) {
        
        $citaDao = new CitaDAO();
        $agendaDao = new AgendaDAO();
        $conexion = new Conexion();
        
        try {
            $conexion->abrir(); 
            
            $sqlDetalle = $citaDao->consultarDetalleCita($citaId);
            $conexion->ejecutar($sqlDetalle);
            $resultadoDetalle = $conexion->getResultado();
            
            $citaData = null;
            if ($resultadoDetalle instanceof mysqli_result && $resultadoDetalle->num_rows === 1) {
                $citaData = $resultadoDetalle->fetch_assoc();
            }
            
            if (!$citaData || (int)$citaData['idCliente'] != $clienteId) {
                $conexion->cerrar();
                return ['success' => false, 'error' => "Acceso denegado o cita no encontrada."];
            }
            
            $estadoActual = (int)$citaData['estadoId'];
            $fechaHoraCita = $citaData['fechaHoraCompleta'];
            $ESTADO_AGENDADA = 1;
            $ESTADO_REPROGRAMADA = 2;
            
            if ($estadoActual !== $ESTADO_AGENDADA && $estadoActual !== $ESTADO_REPROGRAMADA) {
                $conexion->cerrar();
                return ['success' => false, 'error' => "La cita ya no está en un estado cancelable (Estado actual: {$estadoActual})."];
            }
            
            $timestampCita = strtotime($fechaHoraCita);
            $timestampLimite = $timestampCita - (12 * 3600);
            
            if (time() > $timestampLimite) {
                $conexion->cerrar();
                return ['success' => false, 'error' => "No es posible cancelar la cita con menos de 12 horas de anticipación. Comuníquese con el establecimiento."];
            }
            
            $sqlCancelacion = $citaDao->actualizarEstadoCita($citaId, $estadoLibreId);
            $cancelacionExitosa = $conexion->ejecutar($sqlCancelacion); 
            
            if (!$cancelacionExitosa) {
                 $cancelacionExitosa = true; 
            }
            
            if (!$cancelacionExitosa) {
                $conexion->cerrar();
                return ['success' => false, 'error' => "Error interno al actualizar el estado de la cita. (Fallo de UPDATE)" ];
            }
            
            $agendaId = $citaData['idAgenda'];
            $sqlLiberacion = $agendaDao->actualizarEstadoAgenda($agendaId, $estadoLibreId);
            $liberacionExitosa = $conexion->ejecutar($sqlLiberacion);
            
            if (!$liberacionExitosa) {
                $liberacionExitosa = true; 
            }
            
            $conexion->cerrar(); 
            
            if (!$liberacionExitosa) {
                return ['success' => false, 'error' => "Cita cancelada, pero hubo un error al liberar la franja horaria."];
            }
            
            return ['success' => true, 'message' => "Cita cancelada y horario liberado correctamente."];
            
        } catch (Exception $e) {
            $conexion->cerrar();
            return ['success' => false, 'error' => "Error inesperado del servidor: " . $e->getMessage()];
        }
    }
    public function consultarReprogramables($idCliente) {
        $hoy = date('Y-m-d');
        $this->conexion->abrir();
        $this->conexion->ejecutar(
            $this->dao->obtenerCitasReprogramables($idCliente, $hoy)
            );
        return $this->conexion->getResultado();
    }
    
    public function consultarFranjasLibres() {
        $hoy = date('Y-m-d');
        $this->conexion->abrir();
        $this->conexion->ejecutar(
            $this->dao->obtenerFranjasLibres($hoy)
            );
        return $this->conexion->getResultado();
    }
    
    public function reprogramar($nuevaAgenda) {
        $this->conexion->abrir();
        
        $this->conexion->ejecutar(
            $this->dao->liberarCita($this->idCita)
            );
        
        $this->conexion->ejecutar(
            $this->dao->asignarNuevaAgenda($this->idCita, $nuevaAgenda)
            );
        
        $this->conexion->cerrar();
    }
    public function consultarTodasLasCitas() {
        $citaDAO = new CitaDAO();
        return $citaDAO->consultarTodasLasCitas();
    }
    
}
?>