<?php
require_once(__DIR__ . "/../persistencia/EstadisticaDAO.php");
require_once(__DIR__ . "/../persistencia/Conexion.php");

class Estadistica {
    
    private function ejecutarConsulta($metodoDao) {
        $conexion = new Conexion();
        $dao = new EstadisticaDAO();
        $conexion->abrir();
        
        $conexion->ejecutar($dao->$metodoDao());
        
        $resultado = $conexion->getResultado();
        $datos = [];

        if ($resultado instanceof mysqli_result) {
            while ($fila = $resultado->fetch_assoc()) {
                $datos[] = $fila;
            }
        } elseif ($conexion->filas() > 0) {
             $datos[] = $conexion->registro();
        }
        
        $conexion->cerrar();
        return $datos;
    }

    public function serviciosMasSolicitados() {
        return $this->ejecutarConsulta('serviciosMasSolicitados');
    }

    public function ingresosEstimadosPorServicio() {
        return $this->ejecutarConsulta('ingresosEstimadosPorServicio');
    }
    
    public function totalCitasAtendidas() {
        $resultado = $this->ejecutarConsulta('totalCitasAtendidas');
        return !empty($resultado) ? $resultado[0]['TotalCitas'] : 0;
    }
    public function totalServiciosRegistrados() {
        $resultado = $this->ejecutarConsulta('totalServiciosRegistrados');
        return !empty($resultado) ? $resultado[0]['TotalServicios'] : 0;
    }
}
?>