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
        return "SELECT
        c.idCita,
        ag.Fecha,
        ag.HoraInicio,
        ag.HoraFin,
        s.Nombre AS Servicio,
        e.Nombre AS Empleado,
        ec.Tipo AS Estado
    FROM cita c
    INNER JOIN agenda ag ON c.Agenda_idAgenda = ag.idAgenda
    INNER JOIN empleado e ON c.Empleado_idEmpleado = e.idEmpleado
    INNER JOIN estadocita ec ON c.EstadoCita_idEstadoCita = ec.idEstadoCita
        -- QUITAMOS el JOIN a 'servicio' si no se usa para nada más,
        -- pero lo dejaremos ya que se usa para obtener s.Nombre AS Servicio.
    INNER JOIN servicio s ON c.Servicio_idServicio = s.idServicio
    WHERE c.Cliente_idCliente = " . $this->id . "
    ORDER BY c.idCita DESC";
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
