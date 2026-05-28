<?php
require_once("logica/Persona.php");
require_once("persistencia/GerenteDAO.php");
require_once("persistencia/Conexion.php");

class Gerente extends Persona {
    
    public function __construct($id = "", $nombre = "", $apellido = "", $correo = "", $contraseña = "", $telefono = "") {
        parent::__construct($id, $nombre, $apellido, $correo, $contraseña, $telefono);
    }
    
    public function autenticarse() {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $claveMd5 = md5($this->contraseña);
        $gerenteDAO = new GerenteDAO(correo: $this->correo, contraseña: $claveMd5);
        $conexion->ejecutar($gerenteDAO->autenticarse());
        
        if ($conexion->filas() == 1) {
            $datos = $conexion->registro();
            $this->id = $datos[0];
            $conexion->cerrar();
            return true;
        } else {
            $conexion->cerrar();
            return false;
        }
    }
    
    public function consultar() {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $gerenteDAO = new GerenteDAO($this->id);
        $conexion->ejecutar($gerenteDAO->consultar());
        
        if ($conexion->filas() == 1) {
            $datos = $conexion->registro();
            $this->nombre = $datos[1];
            $this->apellido = $datos[2];
            $this->correo = $datos[3];
            $this->telefono = $datos[4];
            $conexion->cerrar();
            return true;
        } else {
            $conexion->cerrar();
            return false;
        }
    }
}
?>