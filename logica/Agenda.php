<?php
require_once(__DIR__ . "/../persistencia/Conexion.php");
require_once(__DIR__ . "/../persistencia/AgendaDAO.php");
require_once(__DIR__ . "/Empleado.php");

class Agenda {
    
    private $idAgenda;
    private $fecha;
    private $horaInicio;
    private $horaFin;
    private $empleadoId;
    private $servicioId;
    
    
    public function __construct(
        $idAgenda = "",
        $fecha = "",
        $horaInicio = "",
        $horaFin = "",
        $empleadoId = "",
        $servicioId = "" 
        ) {
            $this->idAgenda = $idAgenda;
            $this->fecha = $fecha;
            $this->horaInicio = $horaInicio;
            $this->horaFin = $horaFin;
            $this->empleadoId = $empleadoId;
            $this->servicioId = $servicioId; 
    }
    
    public function getIdAgenda() { return $this->idAgenda; }
    public function getFecha() { return $this->fecha; }
    public function getHoraInicio() { return $this->horaInicio; }
    public function getHoraFin() { return $this->horaFin; }
    public function getEmpleadoId() { return $this->empleadoId; }
    public function getServicioId() { return $this->servicioId; } 
    
    
    public static function consultarAgendaPorEmpleado($idEmpleado, $fechaInicio, $fechaFin) {
        $conexion = new Conexion();
        
        try {
            $conexion->abrir();
            $agendaDAO = new AgendaDAO();
            $conexion->ejecutar($agendaDAO->consultarAgendaPorEmpleado($idEmpleado, $fechaInicio, $fechaFin));
            
            $citas = [];
            $resultado = $conexion->getResultado();
            
            if ($resultado instanceof mysqli_result) {
                while ($fila = $resultado->fetch_assoc()) {
                    $citas[] = $fila;
                }
            }
            
            $conexion->cerrar();
            return $citas;
            
        } catch (Exception $e) {
            if ($conexion->isAbierta()) {
                $conexion->cerrar();
            }
            return ['error' => 'Error al consultar la agenda: ' . $e->getMessage()];
        }
    }
    
    public static function consultarHorarioEmpleado($idEmpleado) {
        $empleado = new Empleado($idEmpleado);
        
        if ($empleado->consultar()) {
            return $empleado->getHorario();
        }
        return null;
    }
    
    public function registrar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $agendaDAO = new AgendaDAO(
            idAgenda: $this->idAgenda,
            fecha: $this->fecha,
            horaInicio: $this->horaInicio,
            horaFin: $this->horaFin,
            empleadoId: $this->empleadoId,
            servicioId: $this->servicioId 
            );
        
        $conexion->ejecutar($agendaDAO->registrar());
        $conexion->cerrar();
        return $conexion->getResultado();
    }
    
    public function eliminar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $agendaDAO = new AgendaDAO(idAgenda: $this->idAgenda);
        $conexion->ejecutar($agendaDAO->eliminar());
        $conexion->cerrar();
    }
    
    public function actualizar() {
        $conexion = new Conexion();
        $conexion->abrir();
        $agendaDAO = new AgendaDAO(
            idAgenda: $this->idAgenda,
            fecha: $this->fecha,
            horaInicio: $this->horaInicio,
            horaFin: $this->horaFin,
            empleadoId: $this->empleadoId,
            servicioId: $this->servicioId 
            );
        
        $conexion->ejecutar($agendaDAO->actualizar());
        $conexion->cerrar();
    }
    
    
}