<?php
class EmpleadoDAO {
    private $id;
    private $nombre;
    private $apellido;
    private $correo;
    private $contraseña;
    private $estado;
    private $salario;
    private $horario;
    
    public function __construct($id = "", $nombre = "", $apellido = "", $correo = "", $contraseña = "", $estado = "", $salario = "", $horario = "") {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->correo = $correo;
        $this->contraseña = $contraseña;
        $this->estado = $estado;
        $this->salario = $salario;
        $this->horario = $horario;
    }
    
    public function autenticarse() {
        return "SELECT idEmpleado FROM empleado WHERE correo = '{$this->correo}' AND contraseña = '{$this->contraseña}'";
    }
    public function consultarTodos() {
        return "SELECT idEmpleado, Nombre, Apellido FROM empleado WHERE Estado = 1 ORDER BY Apellido ASC";
    }
}
?>