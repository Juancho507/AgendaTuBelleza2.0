<?php
require_once("logica/Persona.php");
require_once("persistencia/ClienteDAO.php");
require_once("persistencia/Conexion.php");

class Cliente extends Persona {
    private $estado;
    private $fechaRegistro;
    private $gerente; 
    private $foto; 
    
    public function __construct(
        $id = "",
        $nombre = "",
        $apellido = "",
        $correo = "",
        $contraseña = "",
        $telefono = "",
        $estado = 1,
        $fechaRegistro = "",
        $gerente = "",
        $foto = "" 
        ) {
           
            parent::__construct($id, $nombre, $apellido, $correo, $contraseña, $telefono);
            $this->estado = $estado;
            $this->fechaRegistro = $fechaRegistro;
            $this->gerente = $gerente;
            $this->foto = $foto; 
    }
    
    
    public function getFoto() {
        return $this->foto;
    }
    
    public function getGerente() {
        return $this->gerente;
    }
    
    public function getEstado() {
        return $this->estado;
    }
    
    public function getFechaRegistro() {
        return $this->fechaRegistro;
    }
    
    
    public function registrar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $claveMd5 = md5($this->contraseña);
        
        $clienteDAO = new ClienteDAO(
            id: "",
            nombre: $this->nombre,
            apellido: $this->apellido,
            correo: $this->correo,
            contraseña: $claveMd5,
            telefono: $this->telefono,
            estado: $this->estado,
            fechaRegistro: date("Y-m-d H:i:s"),
            foto: $this->foto
            );
        
        $conexion->ejecutar($clienteDAO->registrar());
        $conexion->cerrar();
        return $conexion->getResultado();
    }
    
    
    public function correoExiste() {
        $conexion = new Conexion();
        $conexion->abrir();
        $clienteDAO = new ClienteDAO(correo: $this->correo);
        $conexion->ejecutar($clienteDAO->correoExiste());
        $existe = $conexion->filas() > 0;
        $conexion->cerrar();
        return $existe;
    }
    
    
    public function autenticarse() {
        $conexion = new Conexion();
        $conexion->abrir();
        $claveMd5 = md5($this->contraseña);
        $clienteDAO = new ClienteDAO(correo: $this->correo, contraseña: $claveMd5);
        $conexion->ejecutar($clienteDAO->autenticarse());
        
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
        $clienteDAO = new ClienteDAO($this->id);
        $conexion->ejecutar($clienteDAO->consultar());
        $datos = $conexion->registro();
        if ($datos) {
            $this->nombre = $datos[0];
            $this->apellido = $datos[1];
            $this->correo = $datos[2];
            $this->telefono = $datos[3];
            $this->estado = $datos[4];
            $this->fechaRegistro = $datos[5];
            $this->gerente = $datos[6]; 
            $this->foto = $datos[7];   
            if (empty($this->gerente)) {
                $this->gerente = 1;
            }
            
        }
        $conexion->cerrar();
    }
    
    
    public function actualizar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $clienteDAO = new ClienteDAO($this->id, $this->nombre, $this->apellido, $this->correo, $this->contraseña, $this->telefono, $this->estado, $this->fechaRegistro, $this->gerente, $this->foto);
        $conexion->ejecutar($clienteDAO->actualizar());
        $conexion->cerrar();
    }
    
    
    public function desactivar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $clienteDAO = new ClienteDAO($this->id);
        $conexion->ejecutar($clienteDAO->desactivar());
        $conexion->cerrar();
    }
    
    
    public function activar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $clienteDAO = new ClienteDAO($this->id);
        $conexion->ejecutar($clienteDAO->activar());
        $conexion->cerrar();
    }
    
    
    public function consultarHistorialCitas() {
        $conexion = new Conexion();
        $conexion->abrir();
        $clienteDAO = new ClienteDAO($this->id);
        $resultado = $conexion->ejecutar($clienteDAO->consultarHistorialCitas());
        
        $historial = [];
        while ($registro = $conexion->registro($resultado)) {
            $historial[] = [
                'idCita'     => $registro[0],
                'Fecha'      => $registro[1],
                'HoraInicio' => $registro[2],
                'HoraFin'    => $registro[3],
                'Servicio'   => $registro[4],
                'Empleado'   => $registro[5],
                'Estado'     => $registro[6],
                'Comentarios'=> $registro[7]
            ];
        }
        
        $conexion->cerrar();
        return $historial;
    }
    public function tieneCitasActivas() {
        $conexion = new Conexion();
        $conexion->abrir();
        $clienteDAO = new ClienteDAO($this->id);
        
        $conexion->ejecutar($clienteDAO->verificarCitasActivas());
        
        $tieneActivas = $conexion->filas() > 0;
        $conexion->cerrar();
        
        return $tieneActivas;
    }
}
    

?>