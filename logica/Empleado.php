
<?php
require_once("logica/Persona.php");
require_once("persistencia/EmpleadoDAO.php");
require_once("persistencia/Conexion.php");

class Empleado extends Persona {
    private $estado;
    private $salario;
    private $horario;

    public function __construct($id = "", $nombre = "", $apellido = "", $correo = "", $contraseña = "", $telefono = "", $estado = "", $salario = "", $horario = "") {
        parent::__construct($id, $nombre, $apellido, $correo, $contraseña, $telefono);
        $this->estado = $estado;
        $this->salario = $salario;
        $this->horario = $horario;
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

    public function autenticarse() {
        $conexion = new Conexion();
        $conexion->abrir();

        $claveMd5 = md5($this->contraseña);
        $empleadoDAO = new EmpleadoDAO(correo: $this->correo, contraseña: $claveMd5);
        $conexion->ejecutar($empleadoDAO->autenticarse());

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
}
?>
