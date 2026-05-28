<?php
class GerenteDAO {
    private $id;
    private $nombre;
    private $apellido;
    private $correo;
    private $contraseña;
    private $telefono;
    
    public function __construct($id = "", $nombre = "", $apellido = "", $correo = "", $contraseña = "", $telefono = "") {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->correo = $correo;
        $this->contraseña = $contraseña;
        $this->telefono = $telefono;
    }
    
    public function autenticarse() {
        return "SELECT idGerente
                FROM gerente
                WHERE correo = '{$this->correo}'
                AND contraseña = '{$this->contraseña}'";
    }
    
    public function consultar() {
        return "SELECT idGerente, nombre, apellido, correo, telefono
                FROM gerente
                WHERE idGerente = {$this->id}";
    }
    
    public function actualizar() {
        return "UPDATE gerente SET
                nombre = '{$this->nombre}',
                apellido = '{$this->apellido}',
                correo = '{$this->correo}',
                contraseña = '{$this->contraseña}',
                telefono = '{$this->telefono}'
                WHERE idGerente = {$this->id}";
    }
    
}
?>
