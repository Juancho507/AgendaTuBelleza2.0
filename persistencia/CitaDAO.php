<?php
class CitaDAO {
    private $idCita;
    private $estadoCitaId;
    private $agendaId;
    private $clienteId;
    private $comentarios;
    
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
                ct.comentarios 
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
    public function obtenerCitas() {
        return "
        SELECT
            ct.idCita,
            a.Fecha,
            a.HoraInicio,
            ec.Tipo AS estado,
            CONCAT(cli.Nombre, ' ', cli.Apellido) AS cliente,
            CONCAT(emp.Nombre, ' ', emp.Apellido) AS empleado,
            s.Nombre AS servicio,
            ct.comentarios
        FROM cita ct
        INNER JOIN agenda a ON ct.Agenda_idAgenda = a.idAgenda
        INNER JOIN cliente cli ON ct.Cliente_idCliente = cli.idCliente
        INNER JOIN empleado emp ON a.Empleado_idEmpleado = emp.idEmpleado
        INNER JOIN servicio s ON a.Servicio_idServicio = s.idServicio
        INNER JOIN estadocita ec ON ct.EstadoCita_idEstadoCita = ec.idEstadoCita
        ORDER BY ct.idCita ASC
    ";
    }
    public function registrarNuevaCita($agendaId, $clienteId, $comentarios, $estadoPendiente) {
        $comentariosEscapados = addslashes($comentarios);
        return "
            INSERT INTO cita (EstadoCita_idEstadoCita, Agenda_idAgenda, Cliente_idCliente, comentarios)
            VALUES (
                " . intval($estadoPendiente) . ",
                " . intval($agendaId) . ",
                " . intval($clienteId) . ",
                '" . $comentariosEscapados . "'
            )
        ";
    }
    
    public function verificarFranjaLibre($agendaId) {
        return "
            SELECT
                ct.idCita
            FROM cita ct
            WHERE
                ct.Agenda_idAgenda = " . intval($agendaId) . "
                AND ct.EstadoCita_idEstadoCita IN (1, 2, 6)
        ";
    }
    public function consultarCitasPorCliente(int $clienteId) {
        return "
        SELECT
            ct.idCita AS id,
            ct.comentarios,
            ct.EstadoCita_idEstadoCita AS estadoId,
            CONCAT(emp.Nombre, ' ', emp.Apellido) AS NombreEmpleado,
            s.Nombre AS NombreServicio,
            a.Fecha AS fecha,
            a.HoraInicio AS hora_inicio,
            ec.Tipo AS EstadoNombre,
            a.idAgenda AS idAgenda
        FROM cita ct
        JOIN agenda a ON ct.Agenda_idAgenda = a.idAgenda
        JOIN empleado emp ON a.Empleado_idEmpleado = emp.idEmpleado
        JOIN servicio s ON a.Servicio_idServicio = s.idServicio
        JOIN estadocita ec ON ct.EstadoCita_idEstadoCita = ec.idEstadoCita
        WHERE ct.Cliente_idCliente = " . $clienteId . "
        ORDER BY a.Fecha DESC, a.HoraInicio DESC
    ";
    }
    
    public function consultarDetalleCita(int $citaId) {
        return "
        SELECT
            ct.Agenda_idAgenda AS idAgenda,
            ct.EstadoCita_idEstadoCita AS estadoId,
            ct.Cliente_idCliente AS idCliente,
            a.Fecha AS fecha,
            a.HoraInicio AS hora_inicio,
            CONCAT(a.Fecha, ' ', a.HoraInicio) AS fechaHoraCompleta
        FROM cita ct
        JOIN agenda a ON ct.Agenda_idAgenda = a.idAgenda
        WHERE ct.idCita = " . $citaId;
    }
    
    public function actualizarEstadoCita(int $citaId, int $nuevoEstadoId) {
        return "
        UPDATE cita
        SET EstadoCita_idEstadoCita = " . $nuevoEstadoId . "
        WHERE idCita = " . $citaId;
    }
    public function obtenerCitasReprogramables($idCliente, $hoy) {
        return "
            SELECT
                ct.idCita,
                s.Nombre AS servicio,
                CONCAT(emp.Nombre,' ',emp.Apellido) AS empleado,
                a.Fecha,
                a.HoraInicio
            FROM cita ct
            INNER JOIN agenda a ON ct.Agenda_idAgenda = a.idAgenda
            INNER JOIN empleado emp ON a.Empleado_idEmpleado = emp.idEmpleado
            INNER JOIN servicio s ON a.Servicio_idServicio = s.idServicio
            WHERE ct.Cliente_idCliente = $idCliente
            AND ct.EstadoCita_idEstadoCita IN (1,6)
            AND a.Fecha >= '$hoy'
            ORDER BY a.Fecha ASC
        ";
    }
    
    public function obtenerFranjasLibres($hoy) {
        return "
        SELECT
            a.idAgenda,
            s.Nombre AS servicio,
            CONCAT(emp.Nombre,' ',emp.Apellido) AS empleado,
            a.Fecha,
            a.HoraInicio
        FROM agenda a
        INNER JOIN servicio s ON a.Servicio_idServicio = s.idServicio
        INNER JOIN empleado emp ON a.Empleado_idEmpleado = emp.idEmpleado
        WHERE a.Fecha >= '$hoy'
        AND a.idAgenda NOT IN (
            SELECT ct.Agenda_idAgenda
            FROM cita ct
            WHERE ct.EstadoCita_idEstadoCita IN (1, 2, 6)
        )
        ORDER BY a.Fecha ASC
    ";
    }
    
    public function liberarCita($idCita) {
        return "UPDATE cita SET EstadoCita_idEstadoCita = 7 WHERE idCita = $idCita";
    }
    
    public function asignarNuevaAgenda($idCita, $nuevaAgenda) {
        return "
            UPDATE cita
            SET Agenda_idAgenda = $nuevaAgenda,
                EstadoCita_idEstadoCita = 6
            WHERE idCita = $idCita
        ";
    }
    public function consultarTodasLasCitas() {
       return "
    SELECT
        c.idCita,
        s.nombreServicio AS servicio,
        u.nombre AS empleado,
        cl.nombre AS cliente,
        a.Fecha,
        a.HoraInicio,
        ec.nombreEstado AS estado,
        c.comentarios
    FROM cita c
    INNER JOIN agenda a ON c.Agenda_idAgenda = a.idAgenda
    INNER JOIN servicio s ON a.Servicio_idServicio = s.idServicio
    INNER JOIN usuario u ON a.Empleado_idEmpleado = u.idUsuario
    INNER JOIN cliente cl ON c.Cliente_idCliente = cl.idCliente
    INNER JOIN estadocita ec ON c.EstadoCita_idEstadoCita = ec.idEstadoCita
    ORDER BY a.Fecha DESC, a.HoraInicio DESC
    ";
    }
    

}
?>