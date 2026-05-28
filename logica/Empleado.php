
<?php
require_once(__DIR__ . "/Persona.php");
require_once(__DIR__ . "/../persistencia/EmpleadoDAO.php");
require_once(__DIR__ . "/../persistencia/Conexion.php");

class Empleado extends Persona {
    private $estado;
    private $salario;
    private $horario;
    private $gerente;
    private $foto;
    private $hojadevida;
    public function __construct($id = "", $nombre = "", $apellido = "", $correo = "", $contraseña = "", $telefono = "", $estado = "", $salario = "", $horario = "", $gerente = "", $foto = "",
        $hojadevida = "") {
        parent::__construct($id, $nombre, $apellido, $correo, $contraseña, $telefono);
        $this->estado = $estado;
        $this->salario = $salario;
        $this->horario = $horario;
        $this->gerente = $gerente;
        $this->foto = $foto; 
        $this->hojadevida = $hojadevida;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getSalario() {
        return $this->salario;
    }

    public function getHorario() {
        return $this->horario;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setSalario($salario) {
        $this->salario = $salario;
    }

    public function setHorario($horario) {
        $this->horario = $horario;
    }
    public function getFoto() {
        return $this->foto;
    }
    
    public function getGerente() {
        return $this->gerente;
    }
    public function getHojadevida() {
        return $this->hojadevida;
    }
    
    public function registrar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $claveMd5 = md5($this->contraseña);
        
        $empleadoDAO = new EmpleadoDAO(
            id: "",
            nombre: $this->nombre,
            apellido: $this->apellido,
            correo: $this->correo,
            contraseña: $claveMd5,
            telefono: $this->telefono,
            estado: $this->estado,
            salario: $this->salario,
            horario: $this->horario,
            gerente: $this->gerente,
            foto: $this->foto,
            hojadevida: $this->hojadevida
            );
        
        $conexion->ejecutar($empleadoDAO->registrar());
        $conexion->cerrar();
        
        return true;
    }

    public function autenticarse() {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $claveMd5 = md5($this->contraseña);
        $empleadoDAO = new EmpleadoDAO(correo: $this->correo, contraseña: $claveMd5);
        
        $conexion->ejecutar($empleadoDAO->autenticarse());
        
        if ($conexion->filas() == 1) {
            $datos = $conexion->registro();
            $this->id         = $datos[0];
            $this->nombre     = $datos[1];
            $this->apellido   = $datos[2];
            $this->correo     = $datos[3];
            $this->telefono   = $datos[4];
            $this->estado     = $datos[5];  
            $this->foto       = $datos[6];
            $this->hojaDeVida = $datos[7];
            
            $conexion->cerrar();
            
            if ($this->estado == 1) {
                return true; 
            } else {
                return "inactivo"; 
            }
        }
        
        $conexion->cerrar();
        return false;
    }
    
    public static function consultarTodos() {
        $conexion = new Conexion();
        $conexion->abrir();
        $empleadoDAO = new EmpleadoDAO();
        $conexion->ejecutar($empleadoDAO->consultarTodos());
        $empleados = [];
        while ($registro = $conexion->registro()) {
            $empleados[] = [
                'idEmpleado' => $registro[0],
                'NombreCompleto' => $registro[1] . " " . $registro[2]
            ];
        }
        $conexion->cerrar();
        return $empleados;
    }
    public function consultar() {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $empleadoDAO = new EmpleadoDAO();
        
        $conexion->ejecutar($empleadoDAO->consultarPorId($this->id));
        
        if ($conexion->filas() == 1) {
            $datos = $conexion->registro();
            
            $this->nombre = $datos[0];
            $this->apellido = $datos[1];
            $this->correo = $datos[2];
            $this->telefono = $datos[3];
            $this->estado = $datos[4];
            $this->salario = $datos[5];
            $this->horario = $datos[6];
            $this->gerente = $datos[7]; 
            $this->foto = $datos[8];
            $this->hojadevida = $datos[9];
            $conexion->cerrar();
            return true;
        } else {
            $conexion->cerrar();
            return false;
        }
    }
    public function actualizarPerfilCompleto() {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $empleadoDAO = new EmpleadoDAO(
            $this->id,
            $this->nombre,
            $this->apellido,
            $this->correo,
            $this->contraseña, 
            $this->telefono,
            $this->estado,
            $this->salario,
            $this->horario,
            $this->gerente,
            $this->foto 
            );
        
        $conexion->ejecutar($empleadoDAO->actualizarPerfilCompleto());
        
        $filasAfectadas = $conexion->filas();
        $conexion->cerrar();
        
        return $filasAfectadas > 0;
    }
    public function consultarServiciosOfrecidos() {
        $conexion = new Conexion();
        $conexion->abrir();
        $empleadoDAO = new EmpleadoDAO($this->id);
        $conexion->ejecutar($empleadoDAO->consultarServiciosOfrecidos());
        
        $servicios = [];
        while ($registro = $conexion->registro()) {
            $servicios[] = $registro[0];
        }
        $conexion->cerrar();
        return $servicios; 
    }
  
    public function actualizarServiciosOfrecidos($servicios) {
        $conexion = new Conexion();
        $conexion->abrir();
        $empleadoDAO = new EmpleadoDAO($this->id);
        
        $conexion->ejecutar($empleadoDAO->eliminarServiciosOfrecidos());
        
        $filasAfectadas = $conexion->afectadas();
        if (is_array($servicios) && count($servicios) > 0) {
            foreach ($servicios as $idServicio) {
                $conexion->ejecutar($empleadoDAO->insertarServicioOfrecido((int)$idServicio));
                $filasAfectadas += $conexion->afectadas();
            }
        }
        $conexion->cerrar();
        return $filasAfectadas > 0;
    }
    public function consultarPQRS() {
        $conexion = new Conexion();
        $conexion->abrir();
        require_once(__DIR__ . "/../persistencia/PQRSDAO.php"); 
        $pqrsDAO = new PQRSDAO();
        
        try {
            $conexion->ejecutar($pqrsDAO->consultarPQRSPorEmpleado($this->id));
            
            $pqrs = $conexion->getResultado()->fetch_all(MYSQLI_ASSOC);
            $conexion->cerrar();
            return $pqrs;
        } catch (Exception $e) {
            $conexion->cerrar();
            return ['error' => 'Error al cargar los PQRS: ' . $e->getMessage()];
        }
    }
    public function correoExiste() {
        $conexion = new Conexion();
        $conexion->abrir();
        $EmpleadoDAO = new EmpleadoDAO(correo: $this->correo);
        $conexion->ejecutar($EmpleadoDAO->correoExiste());
        $existe = $conexion->filas() > 0;
        $conexion->cerrar();
        return $existe;
    }
    public static function consultarPendientes() {
        $conexion = new Conexion();
        $conexion->abrir();
        $dao = new EmpleadoDAO();
        $conexion->ejecutar($dao->consultarPendientes());
        
        $pendientes = [];
        while ($r = $conexion->registro()) {
            $pendientes[] = [
                "idEmpleado" => $r[0],
                "Nombre" => $r[1],
                "Apellido" => $r[2],
                "Correo" => $r[3],
                "Estado" => $r[4],
                "Salario" => $r[5],
                "Horario" => $r[6],
                "Gerente" => $r[7],
                "Foto" => $r[8],
                "HojaDeVida" => $r[9]
            ];
        }
        $conexion->cerrar();
        return $pendientes;
    }
    
    public static function consultarActivosInactivos() {
        $conexion = new Conexion();
        $conexion->abrir();
        $dao = new EmpleadoDAO();
        $conexion->ejecutar($dao->consultarActivosInactivos());
        
        $lista = [];
        while ($r = $conexion->registro()) {
            $lista[] = [
                "idEmpleado" => $r[0],
                "Nombre" => $r[1],
                "Apellido" => $r[2],
                "Correo" => $r[3],
                "Estado" => $r[4],
                "Salario" => $r[5],
                "Horario" => $r[6],
                "Gerente" => $r[7],
                "Foto" => $r[8],
                "HojaDeVida" => $r[9]
            ];
        }
        $conexion->cerrar();
        return $lista;
    }
    public function aprobar($salario, $horario, $idGerente) {
        $conexion = new Conexion();
        $conexion->abrir();
        $dao = new EmpleadoDAO(
        $this->id,
        "", "", "", "", "", 
        "", 
        $salario,
        $horario,
        $idGerente,
        "", "" 
        );
        
        $conexion->ejecutar($dao->aceptarEmpleado());
        
        $afectadas = 0;
        if (method_exists($conexion, "afectadas")) {
            $afectadas = $conexion->afectadas();
        } else {
            $afectadas = $conexion->filas();
        }
        
        $conexion->cerrar();
        return $afectadas > 0;
    }
    
    public function inactivar() {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $dao = new EmpleadoDAO($this->id);
        $conexion->ejecutar($dao->inactivarEmpleado());
        
        $afectadas = 0;
        if (method_exists($conexion, "afectadas")) {
            $afectadas = $conexion->afectadas();
        } else {
            $afectadas = $conexion->filas();
        }
        
        $conexion->cerrar();
        return $afectadas > 0;
    }
    
    public function reactivar() {
        $conexion = new Conexion();
        $conexion->abrir();
        
        $dao = new EmpleadoDAO($this->id);
        $conexion->ejecutar($dao->reactivarEmpleado());
        
        $afectadas = 0;
        if (method_exists($conexion, "afectadas")) {
            $afectadas = $conexion->afectadas();
        } else {
            $afectadas = $conexion->filas();
        }
        
        $conexion->cerrar();
        return $afectadas > 0;
    }
    
}
?>
