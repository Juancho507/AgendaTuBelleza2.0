<?php
class ClienteDAO {
    private $id;
    private $nombre;
    private $apellido;
    private $correo;
    private $contraseña;
    private $telefono;
    private $estado;
    private $fechaRegistro;
    private $gerente; 
    private $foto;
    
    public function __construct($id = "", $nombre = "", $apellido = "", $correo = "", $contraseña = "", $telefono = "", $estado = "", $fechaRegistro = "", $gerente = "", $foto = "") {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->correo = $correo;
        $this->contraseña = $contraseña;
        $this->telefono = $telefono;
        $this->estado = $estado;
        $this->fechaRegistro = $fechaRegistro;
        $this->gerente = $gerente;
        $this->foto = $foto;
    }
    
    public function registrar() {
        $gerenteIdDefecto = 1; 
        
        return "INSERT INTO Cliente (Nombre, Apellido, Correo, Contraseña, Telefono, Estado, FechaRegistro, Gerente_idGerente, Foto)
                VALUES (
                    '" . $this->nombre . "',
                    '" . $this->apellido . "',
                    '" . $this->correo . "',
                    '" . $this->contraseña . "',
                    '" . $this->telefono . "',
                    " . $this->estado . ",
                    '" . $this->fechaRegistro . "',
                    " . $gerenteIdDefecto . ",
                    '" . $this->foto . "' 
                )";
    }
    
    public function correoExiste() {
        return "SELECT idCliente FROM Cliente WHERE Correo = '{$this->correo}'";
    }
    
    public function autenticarse() {
        return "SELECT idCliente
                FROM Cliente
                WHERE Correo = '" . $this->correo . "'
                AND Contraseña = '" . $this->contraseña . "'";
    }
    
    public function consultar() {
        return "SELECT Nombre, Apellido, Correo, Telefono, Estado, FechaRegistro, Gerente_idGerente, Foto
                FROM Cliente
                WHERE idCliente = " . $this->id;
    }
    
    public function actualizar() {
        $gerenteID = empty($this->gerente) ? 1 : $this->gerente;
        return "UPDATE Cliente SET
                    Nombre = '" . $this->nombre . "',
                    Apellido = '" . $this->apellido . "',
                    Correo = '" . $this->correo . "',
                    Contraseña = '" . $this->contraseña . "',
                    Telefono = '" . $this->telefono . "',
                    Estado = " . $this->estado . ",
                    Gerente_idGerente = " . $gerenteID . ",
                    Foto = '" . $this->foto . "' 
                WHERE idCliente = " . $this->id;
    }
    
    public function desactivar() {
        return "UPDATE Cliente SET Estado = 0 WHERE idCliente = '{$this->id}'";
    }
    
    public function activar() {
        return "UPDATE Cliente SET Estado = 1 WHERE idCliente = '{$this->id}'";
    }
    public function consultarHistorialCitas() {
        return "
        SELECT
            ct.idCita,
            a.Fecha,
            a.HoraInicio,
            a.HoraFin,
            s.Nombre AS NombreServicio,
            CONCAT(e.Nombre, ' ', e.Apellido) AS NombreEmpleado,
            ec.Tipo AS EstadoCita,
            ct.comentarios
        FROM cita ct
        JOIN agenda a ON ct.Agenda_idAgenda = a.idAgenda
        JOIN empleado e ON a.Empleado_idEmpleado = e.idEmpleado
        JOIN servicio s ON a.Servicio_idServicio = s.idServicio
        JOIN estadocita ec ON ct.EstadoCita_idEstadoCita = ec.idEstadoCita
            
        WHERE ct.Cliente_idCliente = " . $this->id . "
        ORDER BY ct.idCita DESC
    ";
    }
    public function verificarCitasActivas() {
        return "SELECT idCita
                FROM cita
                WHERE Cliente_idCliente = " . $this->id . "
                AND EstadoCita_idEstadoCita IN (1, 2)
                LIMIT 1";
    }
}
?>
