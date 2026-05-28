<?php
require_once(__DIR__ . "/Persona.php");
require_once(__DIR__ . "/../persistencia/ClienteDAO.php");
require_once(__DIR__ . "/../persistencia/Conexion.php");

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
    
    public static function obtenerTodosLosClientes() {
        $conexion = new Conexion();
        try {
            $conexion->abrir();
            
            $clienteDAO = new ClienteDAO();
            $conexion->ejecutar($clienteDAO->obtenerClientesCompleto());
            
            $clientes = array();
            $resultado = $conexion->getResultado(); 
            if ($resultado instanceof mysqli_result) {
                while (($registro = $resultado->fetch_assoc()) != null) {
                    $clientes[] = $registro;
                }
            } else {
                while (($registro = $conexion->registro()) != null) {
                    $clientes[] = $registro;
                }
            }
            
            $conexion->cerrar();
            return $clientes;
        } catch (Exception $e) {
            $conexion->cerrar();
            return [];
        }
    }
    public static function buscarClientes($termino, $filtros) {
        $conexion = new Conexion();
        $conexion->abrir();
        $clientes = [];
        
        $clienteDAO = new ClienteDAO();
        
        $sql = $clienteDAO->buscarClientesSQL($termino, $filtros);
        
        $conexion->ejecutar($sql);
        
        while ($r = $conexion->registro()) {
            $cliente = [
                "idCliente" => $r[0],
                "Nombre" => $r[1],
                "Apellido" => $r[2],
                "Correo" => $r[3],
                "Telefono" => $r[4],
                "Estado" => $r[5],
                "citas_realizadas" => $r[6]
            ];
            if (!empty($filtros['minCitas']) && is_numeric($filtros['minCitas']) && $cliente["citas_realizadas"] < (int)$filtros['minCitas']) {
                continue;
            }
            
            $clientes[] = $cliente;
        }
        
        $conexion->cerrar();
        return $clientes;
    }
    
    public static function generarVistaDetalle(Cliente $cliente) {
        
        $estado = $cliente->getEstado() == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
        $totalCitas = $cliente->getTotalCitas(); 
        
        $html = '<div class="row p-3">';
        $html .= '<div class="col-12">';
        $html .= '<h4>Información Personal</h4>';
        $html .= '<ul class="list-group list-group-flush">';
        $html .= '<li class="list-group-item"><strong>ID:</strong> ' . $cliente->getId() . '</li>';
        $html .= '<li class="list-group-item"><strong>Nombre Completo:</strong> ' . $cliente->getNombre() . ' ' . $cliente->getApellido() . '</li>';
        $html .= '<li class="list-group-item"><strong>Correo:</strong> ' . $cliente->getCorreo() . '</li>';
        $html .= '<li class="list-group-item"><strong>Teléfono:</strong> ' . $cliente->getTelefono() . '</li>';
        $html .= '<li class="list-group-item"><strong>Estado:</strong> ' . $estado . '</li>';
        $html .= '<li class="list-group-item"><strong>Citas Finalizadas:</strong> ' . $totalCitas . '</li>'; 
        $html .= '</ul>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    private $totalCitas = 0;
    
    public function consultarTotalCitas() {
        if (!isset($this->idCliente) || empty($this->idCliente)) {
            return;
        }
        
        $clienteDAO = new ClienteDAO();
        $conexion = new Conexion();
        
        $sql = $clienteDAO->contarCitasRealizadasSQL($this->idCliente);
        
        $resultado = $conexion->ejecutar($sql);
        
        if ($registro = $resultado->fetch_assoc()) {
            $this->totalCitas = (int)$registro['totalCitas'];
        }
    }
    
    public function getTotalCitas() {
        return $this->totalCitas;
    }
}
    

?>