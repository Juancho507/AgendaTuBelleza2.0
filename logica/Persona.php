<?php

abstract class Persona {
    protected $id;
    protected $nombre;
    protected $apellido;
    protected $correo;
    protected $contraseña;
    protected $telefono;

    public function __construct($id = "", $nombre="", $apellido="", $correo="", $contraseña="", $telefono="") {
        $this -> id = $id;
        $this -> nombre = $nombre;
        $this -> apellido = $apellido;
        $this -> correo = $correo;
        $this -> contraseña = $contraseña;
        $this -> telefono = $telefono;
    }

    public function getId(){
        return $this -> id;
    }
    
    public function getNombre() {
        return $this->nombre;
    }

    public function getApellido() {
        return $this->apellido;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function getContraseña() {
        return $this->contraseña;
    }
    
    public function getTelefono() {
        return $this->telefono;
    }

    public function setId($id){
        $this -> id = $id;
    }
    
    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setApellido($apellido) {
        $this->apellido = $apellido;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }

    public function setContraseña($contraseña) {
        $this->clave = $contraseña;
    }
    
    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }
}
?>
