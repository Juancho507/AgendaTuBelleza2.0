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
    
    public function obtenerClientesCompleto() {
        return "
        SELECT
            c.idCliente,
            c.Nombre AS nombre,
            c.Apellido AS apellido,
            c.Correo AS correo,
            c.Telefono AS telefono,
            CASE c.Estado
                WHEN 1 THEN 'Activo'
                ELSE 'Inactivo'
            END AS estado,
            c.FechaRegistro AS fecha_registro,
            CONCAT(g.Nombre, ' ', g.Apellido) AS gerente_asignado,
            c.Foto AS foto
        FROM Cliente c
        LEFT JOIN Gerente g ON c.Gerente_idGerente = g.idGerente
        ORDER BY c.idCliente ASC;
    ";
    }
    
    public function buscarClientesSQL($termino, $filtros) {
        
        $sql = "SELECT
                c.idCliente,
                c.Nombre,
                c.Apellido,
                c.Correo,
                c.Telefono,
                c.Estado,
                COUNT(ci.idCita) AS citas_realizadas -- Contamos todas las citas
            FROM
                cliente c
            LEFT JOIN
                cita ci ON c.idCliente = ci.Cliente_idCliente
            WHERE 1=1";
        if (!empty($termino)) {
            $sql .= " AND (c.Nombre LIKE '%{$termino}%' OR c.Apellido LIKE '%{$termino}%' OR c.Correo LIKE '%{$termino}%' OR c.Telefono LIKE '%{$termino}%' OR c.idCliente = '{$termino}')";
        }
        
        if ($filtros['estado'] !== '') {
            $sql .= " AND c.Estado = '{$filtros['estado']}'";
        }
        
        if (!empty($filtros['minCitas']) && $filtros['minCitas'] > 0) {
        }
        $sql .= " GROUP BY
                c.idCliente,
                c.Nombre,
                c.Apellido,
                c.Correo,
                c.Telefono,
                c.Estado";
        if (!empty($filtros['minCitas']) && $filtros['minCitas'] > 0) {
            $sql .= " HAVING COUNT(ci.idCita) >= '{$filtros['minCitas']}'";
        }
        
        $sql .= " ORDER BY c.Nombre ASC LIMIT 50";
        
        return $sql;
    }
    public function contarCitasRealizadasSQL($idCliente) {
        $sql = "SELECT COUNT(idCita) as totalCitas
            FROM cita
            WHERE Cliente_idCliente = '{$idCliente}'
            AND Estado = 3"; 
        return $sql;
    }
    }

?>
