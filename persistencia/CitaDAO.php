<?php
class CitaDAO {
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
    
    public function registrar() {
        return "INSERT INTO cita (EstadoCita_idEstadoCita, Agenda_idAgenda, Empleado_idEmpleado, Cliente_idCliente, Servicio_idServicio, comentarios)
                VALUES (
                    '" . $this->estadoCitaId . "',
                    '" . $this->agendaId . "',
                    '" . $this->empleadoId . "',
                    '" . $this->clienteId . "',
                    '" . $this->servicioId . "',
                    '" . $this->comentarios . "'
                )";
    }
 
    public function consultarTodos() {
        return "SELECT c.idCita, c.comentarios,
                       s.Nombre AS NombreServicio,
                       ec.Tipo AS EstadoCitaTipo
                FROM cita c
                INNER JOIN servicio s ON c.Servicio_idServicio = s.idServicio
                INNER JOIN estadocita ec ON c.EstadoCita_idEstadoCita = ec.idEstadoCita
                ORDER BY c.idCita DESC";
    }
    
    public function consultar() {
        return "SELECT * FROM cita WHERE idCita = " . $this->idCita;
    }
    
    public function verificarCitasActivasPorServicio($idServicio) {
        return "SELECT COUNT(idCita)
                FROM cita
                WHERE Servicio_idServicio = " . $idServicio . "
                AND EstadoCita_idEstadoCita IN (1, 2)";
    }
}
?>