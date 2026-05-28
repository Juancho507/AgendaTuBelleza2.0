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
    
    public function consultar() {
        return "SELECT * FROM cita WHERE idCita = " . $this->idCita;
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
    
    public function verificarCitasActivasPorServicio($idServicio) {
        return "
        SELECT COUNT(ct.idCita)
        FROM cita ct
        JOIN agenda a ON ct.Agenda_idAgenda = a.idAgenda
        WHERE a.Servicio_idServicio = " . $idServicio . "
        AND ct.EstadoCita_idEstadoCita IN (1, 2)
    ";
    }
    
    public function consultarCitasFiltradas($idEmpleado, $fInicio, $fFin, $servicioId, $estadoId) {
        $sql = "
            SELECT
                a.Fecha,
                a.HoraInicio,
                s.Nombre AS NombreServicio,
                ec.Tipo AS NombreEstado,
                CONCAT(cl.Nombre, ' ', cl.Apellido) AS NombreCliente
            FROM cita ct
            JOIN agenda a ON ct.Agenda_idAgenda = a.idAgenda
            JOIN servicio s ON a.Servicio_idServicio = s.idServicio
            JOIN estadocita ec ON ct.EstadoCita_idEstadoCita = ec.idEstadoCita
            JOIN cliente cl ON ct.Cliente_idCliente = cl.idCliente
            WHERE a.Empleado_idEmpleado = " . $idEmpleado . "
            AND a.Fecha BETWEEN '" . $fInicio . "' AND '" . $fFin . "'
        ";
        if (!empty($servicioId) && $servicioId != "") {
            $sql .= " AND a.Servicio_idServicio = " . intval($servicioId);
        }
        if (!empty($estadoId) && $estadoId != "") {
            $sql .= " AND ct.EstadoCita_idEstadoCita = " . intval($estadoId);
        }
        
        $sql .= " ORDER BY a.Fecha ASC, a.HoraInicio ASC";
        
        return $sql;
    }
    
    public function consultarPendientesPorEmpleado($idEmpleado) {
        return "
            SELECT
                ct.idCita,
                CONCAT(cl.Nombre, ' ', cl.Apellido) AS NombreCliente,
                s.Nombre AS NombreServicio,
                a.Fecha, a.HoraInicio, a.HoraFin,
                ct.comentarios -- <-- Campo de comentarios del cliente AÑADIDO
            FROM cita ct
            JOIN agenda a ON ct.Agenda_idAgenda = a.idAgenda
            JOIN cliente cl ON ct.Cliente_idCliente = cl.idCliente
            JOIN servicio s ON a.Servicio_idServicio = s.idServicio
            WHERE a.Empleado_idEmpleado = " . $idEmpleado . "
            AND ct.EstadoCita_idEstadoCita = 6
            ORDER BY a.Fecha ASC, a.HoraInicio ASC
        ";
    }
    
    public function verificarConflictoSQL($idEmpleado, $fecha, $horaInicio, $horaFin) {
        return "
            SELECT COUNT(ct.idCita) AS conteo
            FROM cita ct
            JOIN agenda a ON ct.Agenda_idAgenda = a.idAgenda
            WHERE a.Empleado_idEmpleado = " . $idEmpleado . "
            AND a.Fecha = '" . $fecha . "'
            AND ct.EstadoCita_idEstadoCita IN (1, 2)
            AND (
                ('" . $horaInicio . "' < a.HoraFin) AND ('" . $horaFin . "' > a.HoraInicio)
            )
        ";
    }
    
    public function actualizarEstado($idCita, $nuevoEstadoId, $comentarios) {
        return "
            UPDATE cita
            SET EstadoCita_idEstadoCita = " . $nuevoEstadoId . "
            WHERE idCita = " . $idCita;
    }
    
    public function finalizarCita($idCita) {
        return "
            UPDATE cita
            SET EstadoCita_idEstadoCita = 3
            WHERE idCita = " . $idCita;
    }
    
    public function noAsistioCita($idCita) {
        return "
            UPDATE cita
            SET EstadoCita_idEstadoCita = 5
            WHERE idCita = " . $idCita;
    }
    
    public function consultarActivasHoyPorEmpleado($idEmpleado, $fechaHoy) {
        $estadosActivos = "1, 2";
        
        return "
        SELECT
            ct.idCita,
            CONCAT(cl.Nombre, ' ', cl.Apellido) AS NombreCliente,
            s.Nombre AS NombreServicio,
            a.HoraInicio, a.HoraFin,
            a.Fecha  
        FROM cita ct
        JOIN agenda a ON ct.Agenda_idAgenda = a.idAgenda
        JOIN cliente cl ON ct.Cliente_idCliente = cl.idCliente
        JOIN servicio s ON a.Servicio_idServicio = s.idServicio
        WHERE a.Empleado_idEmpleado = " . $idEmpleado . "
        AND ct.EstadoCita_idEstadoCita IN (" . $estadosActivos . ") 
        ORDER BY a.Fecha ASC, a.HoraInicio ASC
    ";
    }
}
?>