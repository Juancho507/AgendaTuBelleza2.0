<?php
class EstadisticaDAO {
    
   
    public function serviciosMasSolicitados() {
        return "SELECT s.Nombre AS Servicio, COUNT(c.idCita) AS TotalCitas
                FROM cita c
                JOIN servicio s ON c.Servicio_idServicio = s.idServicio
                WHERE c.EstadoCita_idEstadoCita = 4 
                GROUP BY s.Nombre
                ORDER BY TotalCitas DESC
                LIMIT 5";
    }

    
    public function ingresosEstimadosPorServicio() {
        return "SELECT s.Nombre AS Servicio, 
                       (s.Precio * COUNT(c.idCita)) AS IngresosEstimados
                FROM cita c
                JOIN servicio s ON c.Servicio_idServicio = s.idServicio
                WHERE c.EstadoCita_idEstadoCita = 4 
                GROUP BY s.Nombre, s.Precio
                ORDER BY IngresosEstimados DESC";
    }
    
    
    public function totalCitasAtendidas() {
        return "SELECT COUNT(idCita) AS TotalCitas
                FROM cita
                WHERE EstadoCita_idEstadoCita = 4";
    }
    public function totalServiciosRegistrados() {
        return "SELECT COUNT(idServicio) AS TotalServicios FROM servicio";
    }
}
?>