<?php
require_once("persistencia/CitaDAO.php");
require_once("persistencia/Conexion.php");

class Cita {
    private $idCita;
    private $estadoCitaId;
    private $agendaId;
    private $empleadoId;
    private $clienteId;
    private $servicioId;
    private $comentarios;
    
    public function __construct(
        $idCita = "",
        $estadoCitaId = "",
        $agendaId = "",
        $empleadoId = "",
        $clienteId = "",
        $servicioId = "",
        $comentarios = ""
        ) {
            $this->idCita = $idCita;
            $this->estadoCitaId = $estadoCitaId;
            $this->agendaId = $agendaId;
            $this->empleadoId = $empleadoId;
            $this->clienteId = $clienteId;
            $this->servicioId = $servicioId;
            $this->comentarios = $comentarios;
    }
    public function getIdCita() { return $this->idCita; }
    public function getEstadoCitaId() { return $this->estadoCitaId; }
    public function getAgendaId() { return $this->agendaId; }
    public function getEmpleadoId() { return $this->empleadoId; }
    public function getClienteId() { return $this->clienteId; }
    public function getServicioId() { return $this->servicioId; }
    public function getComentarios() { return $this->comentarios; }
    
    
    public function registrar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $citaDAO = new CitaDAO(
            $this->estadoCitaId,
            $this->agendaId,
            $this->empleadoId,
            $this->clienteId,
            $this->servicioId,
            $this->comentarios
            );
        $conexion->ejecutar($citaDAO->registrar());
        $conexion->cerrar();
        return $conexion->getResultado();
    }
    
    public function consultar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $citaDAO = new CitaDAO($this->idCita);
        $conexion->ejecutar($citaDAO->consultar());
        $registro = $conexion->registro();
        $conexion->cerrar();
        
        if ($registro) {
            $this->estadoCitaId = $registro['EstadoCita_idEstadoCita'];
            $this->agendaId = $registro['Agenda_idAgenda'];
            $this->empleadoId = $registro['Empleado_idEmpleado'];
            $this->clienteId = $registro['Cliente_idCliente'];
            $this->servicioId = $registro['Servicio_idServicio'];
            $this->comentarios = $registro['comentarios'];
            return true;
        }
        return false;
    }
    
    public static function consultarTodos() {
        $conexion = new Conexion();
        $conexion->abrir();
        $citaDAO = new CitaDAO();
        $conexion->ejecutar($citaDAO->consultarTodos());
        $datos = $conexion->getResultado();
        $conexion->cerrar();
        return $datos;
    }
    public static function hayCitasActivasAsociadas($idServicio) {
        $conexion = new Conexion();
        $conexion->abrir();
        $citaDAO = new CitaDAO();
        $conexion->ejecutar($citaDAO->verificarCitasActivasPorServicio($idServicio));
        
        $registro = $conexion->registro();
        $cantidadCitasActivas = $registro[0];
        
        $conexion->cerrar();
        return ($cantidadCitasActivas > 0);
    }
    
}
?>