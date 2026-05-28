<?php

class ServicioDAO {
    private $idServicio;
    private $nombre;
    private $descripcion;
    private $precio;
    private $estado;
    private $Producto_idProducto;
    private $Gerente_idGerente;

    public function __construct($idServicio = "", $nombre = "", $descripcion = "", $precio = "", $estado = "", $Producto_idProducto = "", $Gerente_idGerente = "") {
        $this->idServicio = $idServicio;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->estado = $estado;
        $this->Producto_idProducto = $Producto_idProducto;
        $this->Gerente_idGerente = $Gerente_idGerente;
    }

    public function registrar() {
        return "INSERT INTO servicio (nombre, descripcion, precio, estado, Producto_idProducto, Gerente_idGerente) 
                VALUES ('" . $this->nombre . "', '" . $this->descripcion . "', " . $this->precio . ", " . $this->estado . ", " . $this->Producto_idProducto . ", " . $this->Gerente_idGerente . ")";
    }

    public function consultar() {
        return "SELECT idServicio, nombre, descripcion, precio, estado, Producto_idProducto, Gerente_idGerente 
                FROM servicio 
                WHERE idServicio = " . $this->idServicio;
    }
    
    public function consultarTodos() {
        return "SELECT s.idServicio, s.Nombre, s.Descripcion, s.Precio, s.Estado, p.Nombre AS NombreProducto, s.Producto_idProducto
                FROM servicio s
                INNER JOIN producto p ON s.Producto_idProducto = p.idProducto
                ORDER BY s.idServicio ASC";
    
    }
    public function cambiarEstado() {
        return "UPDATE servicio 
                SET estado = " . $this->estado . "
                WHERE idServicio = " . $this->idServicio;
    }
    
    public function actualizar() {
        return "UPDATE servicio 
                SET nombre = '" . $this->nombre . "', 
                    descripcion = '" . $this->descripcion . "', 
                    precio = " . $this->precio . ", 
                    estado = " . $this->estado . ", 
                    Producto_idProducto = " . $this->Producto_idProducto . "
                WHERE idServicio = " . $this->idServicio;
    }
    public function consultarServiciosCompletosSQL() {
        return "SELECT s.idServicio, s.Nombre, LEFT(s.Descripcion, 100) AS DescripcionBreve,
                   s.Precio, s.Estado
            FROM servicio s
            ORDER BY s.idServicio ASC";
    }
    
    public function buscarServiciosSQL($filtro) {
        $filtroSeguro = "%" . $filtro . "%";
        return "SELECT s.idServicio, s.Nombre, LEFT(s.Descripcion, 100) AS DescripcionBreve,
                       s.Precio, s.Estado
                FROM servicio s
                WHERE s.Nombre LIKE '" . $filtroSeguro . "'
                ORDER BY s.idServicio ASC";
    }
    
    public function consultarDetalleSQL($idServicio) {
        return "SELECT s.Nombre, s.Descripcion AS DescripcionCompleta, s.Precio, s.Estado,
                   p.Nombre AS NombreProducto, s.Producto_idProducto
            FROM servicio s
            LEFT JOIN producto p ON s.Producto_idProducto = p.idProducto
            WHERE s.idServicio = " . $idServicio;
    }
    public function consultarActivos() {
        return "SELECT idServicio, Nombre FROM servicio WHERE Estado = 1 ORDER BY Nombre ASC";
    }
    public function consultarServiciosPorEmpleado($idEmpleado) {
        return "
            SELECT
                s.idServicio AS id,
                s.Nombre AS nombre
            FROM servicio s
            JOIN servicio_has_empleado se ON s.idServicio = se.Servicio_idServicio
            WHERE se.Empleado_idEmpleado = " . $idEmpleado . "
            ORDER BY s.Nombre ASC
        ";
    }
}
?>