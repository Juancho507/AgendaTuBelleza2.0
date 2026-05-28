<?php
require_once(__DIR__ . "/../persistencia/PQRSDAO.php");
require_once(__DIR__ . "/../persistencia/Conexion.php");

class PQRS {
    private $id;
    private $descripcion;
    private $fecha;
    private $tipoPQRS;
    private $cliente;
    private $gerente;
    private $empleado;
    private $evidencia;
    
    public function __construct(
        $id = "",
        $descripcion = "",
        $fecha = "",
        $tipoPQRS = "",
        $cliente = "",
        $gerente = "",
        $empleado = "",
        $evidencia = "" 
        ) {
            $this->id = $id;
            $this->descripcion = $descripcion;
            $this->fecha = $fecha;
            $this->tipoPQRS = $tipoPQRS;
            $this->cliente = $cliente;
            $this->gerente = $gerente;
            $this->empleado = $empleado;
            $this->evidencia = $evidencia;
    }
    
    
    public function registrar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $gerenteId = $this->gerente ?: 1;
        $pqrsDAO = new PQRSDAO(
            "",                               
            $this->descripcion,              
            date("Y-m-d H:i:s"),            
            $this->tipoPQRS,              
            $this->cliente,                
            $gerenteId,                     
            $this->empleado,                
            $this->evidencia
            );
        
        $conexion->ejecutar($pqrsDAO->registrar());
        $conexion->cerrar();
        return $conexion->getResultado();
    }
    
    public static function consultarTiposPQRS() {
        $conexion = new Conexion();
        $conexion->abrir();
        $conexion->ejecutar("SELECT idTipoPQRS, Tipo FROM tipopqrs");
        $tipos = [];
        while ($registro = $conexion->registro()) {
            $tipos[$registro[0]] = $registro[1];
        }
        $conexion->cerrar();
        return $tipos;
    }
    public static function consultarHistorialPorCliente($idCliente) {
        $conexion = new Conexion();
        $conexion->abrir();
        $pqrsDAO = new PQRSDAO();
        $conexion->ejecutar($pqrsDAO->consultarHistorialPorCliente($idCliente));
        
        $historial = [];
        $resultado = $conexion->getResultado();
        while ($fila = $resultado->fetch_assoc()) {
            $historial[] = $fila;
        }
        
        $conexion->cerrar();
        return $historial;
    }
    
}
?>