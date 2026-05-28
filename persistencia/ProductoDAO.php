<?php

class ProductoDAO {
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

    public function consultarTodos() {
        
        return "SELECT idProducto, Nombre, Descripcion, Cantidad 
                FROM producto 
                ORDER BY Nombre";
    }
    public function obtenerProductos() {
        return "
        SELECT
            idProducto,
            Nombre AS nombre,        
            Descripcion AS descripcion,  
            Cantidad AS cantidad     
        FROM producto
        ORDER BY idProducto ASC
    ";
    }
}
?>