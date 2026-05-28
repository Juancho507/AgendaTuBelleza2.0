<?php
class EstadisticaDAO {
    
    public function serviciosMasSolicitados() {
        return "SELECT s.Nombre AS Servicio, COUNT(ct.idCita) AS TotalCitas
                FROM cita ct
                -- CRÍTICO: Unir cita con agenda
                JOIN agenda a ON ct.Agenda_idAgenda = a.idAgenda
                -- Unir agenda con servicio para obtener el nombre
                JOIN servicio s ON a.Servicio_idServicio = s.idServicio
                WHERE ct.EstadoCita_idEstadoCita = 3
                GROUP BY s.Nombre
                ORDER BY TotalCitas DESC
                LIMIT 5";
    }
    public function ingresosEstimadosPorServicio() {
        return "SELECT s.Nombre AS Servicio,
                       (s.Precio * COUNT(ct.idCita)) AS IngresosEstimados
                FROM cita ct
                -- CRÍTICO: Unir cita con agenda
                JOIN agenda a ON ct.Agenda_idAgenda = a.idAgenda
                -- Unir agenda con servicio para obtener el nombre y precio
                JOIN servicio s ON a.Servicio_idServicio = s.idServicio
                WHERE ct.EstadoCita_idEstadoCita = 3
                GROUP BY s.Nombre, s.Precio
                ORDER BY IngresosEstimados DESC";
    }
    public function totalCitasAtendidas() {
        return "SELECT COUNT(ct.idCita) AS TotalCitas
                FROM cita ct
                WHERE ct.EstadoCita_idEstadoCita = 3";
    }
    public function totalServiciosRegistrados() {
        return "SELECT COUNT(idServicio) AS TotalServicios FROM servicio";
    }
}
?>