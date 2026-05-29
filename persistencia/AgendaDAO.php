<?php
class AgendaDAO {
    
    private $idAgenda;
    private $fecha;
    private $horaInicio;
    private $horaFin;
    private $empleadoId;
    private $servicioId; 
    
    
    public function __construct(
        $idAgenda = "",
        $fecha = "",
        $horaInicio = "",
        $horaFin = "",
        $empleadoId = "",
        $servicioId = ""
        ) {
            $this->idAgenda = $idAgenda;
            $this->fecha = $fecha;
            $this->horaInicio = $horaInicio;
            $this->horaFin = $horaFin;
            $this->empleadoId = $empleadoId;
            $this->servicioId = $servicioId; 
    }
    
    public function consultarAgendaPorEmpleado($idEmpleado, $fechaInicio, $fechaFin) {
        return "
            SELECT
                a.idAgenda,
                a.Fecha,
                a.HoraInicio,
                a.HoraFin,
                s.Nombre AS NombreServicio,
                ct.idCita,
                ec.idEstadoCita AS EstadoCita, -- <--- ¡CORRECCIÓN CLAVE! Ahora obtenemos el ID numérico
                CONCAT(cl.Nombre, ' ', cl.Apellido) AS NombreCliente,
                ct.comentarios
            FROM agenda a
            JOIN servicio s ON a.Servicio_idServicio = s.idServicio
            LEFT JOIN cita ct ON a.idAgenda = ct.Agenda_idAgenda
            LEFT JOIN estadocita ec ON ct.EstadoCita_idEstadoCita = ec.idEstadoCita
            LEFT JOIN cliente cl ON ct.Cliente_idCliente = cl.idCliente
            WHERE a.Empleado_idEmpleado = " . $idEmpleado . "
              AND a.Fecha BETWEEN '" . $fechaInicio . "' AND '" . $fechaFin . "'
            ORDER BY a.Fecha ASC, a.HoraInicio ASC
        ";
    }
    
    public function registrar() {
        return "INSERT INTO agenda (Fecha, HoraInicio, HoraFin, Empleado_idEmpleado, Servicio_idServicio)
                VALUES (
                    '" . $this->fecha . "',
                    '" . $this->horaInicio . "',
                    '" . $this->horaFin . "',
                    " . $this->empleadoId . ",
                    " . $this->servicioId . "
                )";
    }
    
    public function actualizar() {
        return "UPDATE agenda SET
                Fecha = '" . $this->fecha . "',
                HoraInicio = '" . $this->horaInicio . "',
                HoraFin = '" . $this->horaFin . "',
                Empleado_idEmpleado = " . $this->empleadoId . ",
                Servicio_idServicio = " . $this->servicioId . "
                WHERE idAgenda = " . $this->idAgenda;
    }
    
    public function eliminar() {
        return "DELETE FROM agenda
                WHERE idAgenda = " . $this->idAgenda;
    }
    public function obtenerAgenda() {
        return "
        SELECT
            a.idAgenda,
            a.Fecha AS fecha,
            a.HoraInicio AS hora_inicio,
            a.HoraFin AS hora_fin,
            CONCAT(e.Nombre, ' ', e.Apellido) AS empleado,
            s.Nombre AS servicio,
            CASE
                WHEN ct.idCita IS NOT NULL THEN ec.Tipo
                ELSE 'Disponible'
            END AS estado_agenda,
            ct.idCita AS cita_id
        FROM agenda a
        INNER JOIN empleado e ON a.Empleado_idEmpleado = e.idEmpleado
        INNER JOIN servicio s ON a.Servicio_idServicio = s.idServicio
        LEFT JOIN cita ct ON a.idAgenda = ct.Agenda_idAgenda
        LEFT JOIN estadocita ec ON ct.EstadoCita_idEstadoCita = ec.idEstadoCita
        ORDER BY a.idAgenda ASC
    ";
    }
    public function consultarDisponibilidadGlobal() {
        return "
            SELECT
                a.idAgenda,
                a.Fecha,
                a.HoraInicio,
                a.HoraFin,
                CONCAT(e.Nombre, ' ', e.Apellido) AS NombreEmpleado,
                e.idEmpleado,
                s.Nombre AS NombreServicio,
                TIMESTAMPDIFF(MINUTE, a.HoraInicio, a.HoraFin) AS DuracionMinutos,
               
                COALESCE(ct.EstadoCita_idEstadoCita, 7) AS EstadoId
            FROM agenda a
            JOIN empleado e ON a.Empleado_idEmpleado = e.idEmpleado
            JOIN servicio s ON a.Servicio_idServicio = s.idServicio
            LEFT JOIN cita ct ON a.idAgenda = ct.Agenda_idAgenda
            WHERE
                a.Fecha >= CURDATE()
                AND e.Estado = 1 
                
                AND (ct.idCita IS NULL OR ct.EstadoCita_idEstadoCita IN (7, 6))
            ORDER BY a.Fecha ASC, a.HoraInicio ASC;
        ";
    }
    public function actualizarEstadoAgenda(int $agendaId, int $nuevoEstadoId) {
        
        return "SELECT 1";
    }

}
?>