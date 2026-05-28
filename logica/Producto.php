<?php
require_once(__DIR__ . "/../persistencia/ProductoDAO.php");
require_once(__DIR__ . "/../persistencia/Conexion.php");

class Producto {
    private $idProducto;
    private $nombre;
    private $descripcion;
    private $cantidad;
    public function __construct(
        $idProducto = "", 
        $nombre = "", 
        $descripcion = "", 
        $cantidad = ""
    ) {
        $this->idProducto = $idProducto;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->cantidad = $cantidad;
    }
  
    public function getIdProducto() { return $this->idProducto; }
    public function getNombre() { return $this->nombre; }
    public function getDescripcion() { return $this->descripcion; }
    public function getCantidad() { return $this->cantidad; }
    
   
    public static function consultarTodos() {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $productoDAO = new ProductoDAO();
        $conexion->ejecutar($productoDAO->consultarTodos());
        
        $productos = [];
        $resultado = $conexion->getResultado();
        
        while ($fila = $resultado->fetch_assoc()) {
            $productos[] = $fila;
        }
        
        $conexion->cerrar();
        return $productos;
    }
    public static function obtenerTodosLosProductos() {
        $conexion = new Conexion();
        try {
            $conexion->abrir();
            
            $productoDAO = new ProductoDAO();
            $conexion->ejecutar($productoDAO->obtenerProductos());
            
            $productos = array();
            $resultado = $conexion->getResultado(); 
            if ($resultado instanceof mysqli_result) {
                while (($registro = $resultado->fetch_assoc()) != null) {
                    $productos[] = $registro;
                }
            } else {
                while (($registro = $conexion->registro()) != null) {
                    $productos[] = $registro;
                }
            }
            
            $conexion->cerrar();
            return $productos;
        } catch (Exception $e) {
            $conexion->cerrar();
            return [];
        }
    }
}
?>