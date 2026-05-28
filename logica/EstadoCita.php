<?php
require_once(__DIR__ . "/../persistencia/Conexion.php");
require_once(__DIR__ . "/../persistencia/EstadoCitaDAO.php");

class EstadoCita {
    
    private $idEstadoCita;
    private $tipo;
    
    public function __construct(
        $idEstadoCita = "",
        $tipo = ""
        ) {
            $this->idEstadoCita = $idEstadoCita;
            $this->tipo = $tipo;
    }
    
    public function getIdEstadoCita() { return $this->idEstadoCita; }
    public function getTipo() { return $this->tipo; }
 
    public static function consultarTodos() {
        $conexion = new Conexion();
        
        try {
            $conexion->abrir();
            $estadoCitaDAO = new EstadoCitaDAO();
            
            $conexion->ejecutar($estadoCitaDAO->consultarTodos());
            
            $estados = [];
            $resultado = $conexion->getResultado();
            
            if ($resultado instanceof mysqli_result) {
                while ($fila = $resultado->fetch_assoc()) {
                    $estados[] = $fila;
                }
            }
            
            $conexion->cerrar();
            return $estados;
            
        } catch (Exception $e) {
            if ($conexion->isAbierta()) {
                $conexion->cerrar();
            }
            return [];
        }
    }
    
    public function consultar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $estadoCitaDAO = new EstadoCitaDAO(idEstadoCita: $this->idEstadoCita);
        $conexion->ejecutar($estadoCitaDAO->consultar());
        
        $resultado = $conexion->getResultado();
        
        if ($resultado instanceof mysqli_result && $resultado->num_rows == 1) {
            $fila = $resultado->fetch_assoc();
            $this->idEstadoCita = $fila['idEstadoCita'];
            $this->tipo = $fila['Tipo']; 
            $conexion->cerrar();
            return true;
        }
        
        $conexion->cerrar();
        return false;
    }
}
?>