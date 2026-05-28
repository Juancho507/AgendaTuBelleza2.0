<?php
class EmpleadoDAO {
    private $id;
    private $nombre;
    private $apellido;
    private $correo;
    private $contraseña;
    private $telefono;
    private $estado;
    private $salario;
    private $horario;
    private $gerente;
    private $foto;
    private $hojadevida;
    
    
    public function __construct($id = "", $nombre = "", $apellido = "", $correo = "", $contraseña = "",$telefono = "", $estado = "", $salario = "", $horario = "", $gerente = "", $foto = "",
        $hojadevida = "") {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->correo = $correo;
        $this->contraseña = $contraseña;
        $this->telefono = $telefono;
        $this->estado = $estado;
        $this->salario = $salario;
        $this->horario = $horario;
        $this->gerente = $gerente;
        $this->foto = $foto;
        $this->hojadevida = $hojadevida;
    }
    public function registrar() {
        return "INSERT INTO empleado
    (Nombre, Apellido, Correo, Contraseña, Telefono, Estado, Salario, Horario, Gerente_idGerente, Foto, HojaDeVida)
    VALUES (
        '{$this->nombre}',
        '{$this->apellido}',
        '{$this->correo}',
        '{$this->contraseña}',
        '{$this->telefono}',
        {$this->estado},
        {$this->salario},
        '{$this->horario}',
        {$this->gerente},
        '{$this->foto}',
        '{$this->hojadevida}'
    )";
    }
    
    public function autenticarse() {
        return "
        SELECT idEmpleado, nombre, apellido, correo, telefono, estado, foto, hojaDeVida
        FROM empleado
        WHERE correo = '{$this->correo}'
        AND contraseña = '{$this->contraseña}'
        LIMIT 1
    ";
    }
    
    
    public function consultarTodos() {
        return "SELECT idEmpleado, Nombre, Apellido FROM empleado WHERE Estado = 1 ORDER BY Apellido ASC";
    }
    public function consultarPorId($idEmpleado) {
        return "SELECT nombre, apellido, correo, telefono, estado, salario, horario, Gerente_idGerente, Foto, Hojadevida
            FROM empleado
            WHERE idEmpleado = " . $idEmpleado;
    
    }
    public function actualizarPerfilCompleto() {
        return "UPDATE empleado SET
                nombre = '" . $this->nombre . "',
                apellido = '" . $this->apellido . "',
                correo = '" . $this->correo . "',
                contraseña = '" . $this->contraseña . "',
                telefono = '" . $this->telefono . "',
                Foto = '" . $this->foto . "'
                WHERE idEmpleado = " . $this->id;
    }
    public function consultarServiciosOfrecidos() {
        return "SELECT Servicio_idServicio FROM servicio_has_empleado
                WHERE Empleado_idEmpleado = " . $this->id;
    }
   
    public function eliminarServiciosOfrecidos() {
        return "DELETE FROM servicio_has_empleado
                WHERE Empleado_idEmpleado = " . $this->id;
    }
  
    public function insertarServicioOfrecido($idServicio) {
        return "INSERT INTO servicio_has_empleado (Servicio_idServicio, Empleado_idEmpleado)
                VALUES (" . $idServicio . ", " . $this->id . ")";
    }
    public function correoExiste() {
        return "SELECT idEmpleado FROM Empleado WHERE Correo = '{$this->correo}'";
    }
    
    public function consultarPendientes() {
        return "SELECT idEmpleado, Nombre, Apellido, Correo, Estado, Salario, Horario, Gerente_idGerente, Foto, HojaDeVida
            FROM empleado
            WHERE Estado = 0
            ORDER BY idEmpleado DESC";
    }
    
    public function consultarActivosInactivos() {
        return "SELECT idEmpleado, Nombre, Apellido, Correo, Estado, Salario, Horario, Gerente_idGerente, Foto, HojaDeVida
            FROM empleado
            WHERE Estado IN (0,1)
            ORDER BY Estado DESC, idEmpleado DESC";
    }
    public function aceptarEmpleado() {
        return "UPDATE empleado SET
            Estado = 1,
            Salario = " . (is_numeric($this->salario) ? $this->salario : "0") . ",
            Horario = '" . $this->horario . "',
            Gerente_idGerente = " . (int)$this->gerente . "
            WHERE idEmpleado = " . (int)$this->id;
    }
    
    public function inactivarEmpleado() {
        return "UPDATE empleado SET Estado = 0 WHERE idEmpleado = " . (int)$this->id;
    }
    
    public function reactivarEmpleado() {
        return "UPDATE empleado SET Estado = 1 WHERE idEmpleado = " . (int)$this->id;
    }
    
}
?>