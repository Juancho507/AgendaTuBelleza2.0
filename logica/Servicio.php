<?php
require_once(__DIR__ . "/../persistencia/ServicioDAO.php");
require_once(__DIR__ . "/../persistencia/Conexion.php");

class Servicio {
    private $idServicio;
    private $nombre;
    private $descripcion;
    private $precio;
    private $estado;
    private $Producto_idProducto;
    private $Gerente_idGerente;
    
    public function __construct(
        $idServicio = "", 
        $nombre = "", 
        $descripcion = "", 
        $precio = "", 
        $estado = "", 
        $Producto_idProducto = "", 
        $Gerente_idGerente = ""
    ) {
        $this->idServicio = $idServicio;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->estado = $estado;
        $this->Producto_idProducto = $Producto_idProducto;
        $this->Gerente_idGerente = $Gerente_idGerente;
    }
    
    
    public function getIdServicio()
    { return $this->idServicio; }
    
    public function getNombre()
    { return $this->nombre; }
    
    public function getDescripcion()
    { return $this->descripcion; }
    
    public function getPrecio() 
    { return $this->precio; }
    
    public function getEstado() 
    { return $this->estado; }
    
    public function getProductoIdProducto()
    { return $this->Producto_idProducto; }
    
    public function getGerenteIdGerente() 
    { return $this->Gerente_idGerente; }
    
   
  
    public function registrar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $servicioDAO = new ServicioDAO(
            idServicio: "",
            nombre: $this->nombre,
            descripcion: $this->descripcion,
            precio: $this->precio,
            estado: 1, 
            Producto_idProducto: $this->Producto_idProducto,
            Gerente_idGerente: $this->Gerente_idGerente
            );
        
        $conexion->ejecutar($servicioDAO->registrar());
        $conexion->cerrar();
        return $conexion->getResultado();
    }
    
    public function consultar() {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $servicioDAO = new ServicioDAO(idServicio: $this->idServicio);
        $conexion->ejecutar($servicioDAO->consultar());
        
        if ($conexion->filas() == 1) {
            $registro = $conexion->registro();
            $this->nombre = $registro[1];
            $this->descripcion = $registro[2];
            $this->precio = $registro[3];
            $this->estado = $registro[4];
            $this->Producto_idProducto = $registro[5];
            $this->Gerente_idGerente = $registro[6];
            $conexion->cerrar();
            return true;
        } else {
            $conexion->cerrar();
            return false;
        }
    }
    
    public function actualizar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $servicioDAO = new ServicioDAO(
            $this->idServicio,
            $this->nombre,
            $this->descripcion,
            $this->precio,
            $this->estado,
            $this->Producto_idProducto,
            $this->Gerente_idGerente 
            );
        
        $conexion->ejecutar($servicioDAO->actualizar());
        $conexion->cerrar();
        return $conexion->getResultado();
    }
    
    public function cambiarEstado($nuevoEstado) {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $servicioDAO = new ServicioDAO(
            idServicio: $this->idServicio,
            estado: $nuevoEstado
            );
        
        $conexion->ejecutar($servicioDAO->cambiarEstado());
        $conexion->cerrar();
        return $conexion->getResultado();
    }
    
    public static function consultarTodos() {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $servicioDAO = new ServicioDAO();
        $conexion->ejecutar($servicioDAO->consultarTodos());
        
        $historial = [];
        $resultado = $conexion->getResultado();
        
        while ($fila = $resultado->fetch_assoc()) {
            $fila['EstadoTexto'] = $fila['Estado'] == 1 ? 'Activo' : 'Inactivo';
            $historial[] = $fila;
        }
        
        $conexion->cerrar();
        return $historial;
    }
    public function verificarDependencias() {
        return Cita::hayCitasActivasAsociadas($this->idServicio);
    }
    public function inactivar() {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $servicioDAO = new ServicioDAO($this->idServicio, estado: 0);
        $conexion->ejecutar($servicioDAO->cambiarEstado());
        $conexion->cerrar();
        
        return $conexion->getResultado();
    }
    
    public function activar() {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $servicioDAO = new ServicioDAO($this->idServicio, estado: 1); 
        $conexion->ejecutar($servicioDAO->cambiarEstado());
        $conexion->cerrar();
        
        return $conexion->getResultado();
    }

    public static function consultarServiciosCompletos() {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $servicioDAO = new ServicioDAO(0);
        $conexion->ejecutar($servicioDAO->consultarServiciosCompletosSQL());
        
        $servicios = [];
        $resultado = $conexion->getResultado(); 
        while ($registro = $resultado->fetch_assoc()) {
            $servicios[] = $registro;
        }
        
        $conexion->cerrar();
        return $servicios;
    }
   
    public static function buscarServicios($filtro) {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $servicioDAO = new ServicioDAO(0);
        $conexion->ejecutar($servicioDAO->buscarServiciosSQL($filtro));
        
        $servicios = [];
        while ($registro = $conexion->registro()) {
            $servicios[] = $registro;
        }
        
        $conexion->cerrar();
        return $servicios;
    }
  
    public function consultarDetalle() {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $servicioDAO = new ServicioDAO($this->idServicio);
        $conexion->ejecutar($servicioDAO->consultarDetalleSQL($this->idServicio));
        
        $resultado = $conexion->getResultado();
        
        $servicioDetalle = $resultado->fetch_assoc(); 
        
        $conexion->cerrar();
        return $servicioDetalle ?: [];
    }
}
?>