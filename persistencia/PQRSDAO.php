<?php
class PQRSDAO {
 
    private $id;
    private $descripcion;
    private $fecha;
    private $tipoPQRS;
    private $cliente;
    private $gerente;
    private $empleado;
    private $evidencia;
   
    public function __construct(
        $id = "",
        $descripcion = "",
        $fecha = "",
        $tipoPQRS = "",
        $cliente = "",
        $gerente = "",
        $empleado = "",
        $evidencia = ""
        ) {
            $this->id = $id;
            $this->descripcion = $descripcion;
            $this->fecha = $fecha;
            $this->tipoPQRS = $tipoPQRS;
            $this->cliente = $cliente;
            $this->gerente = $gerente;
            $this->empleado = $empleado;
            $this->evidencia = $evidencia;
    }

    public function registrar() {
        $empleadoVal = is_null($this->empleado) ? "NULL" : "'" . $this->empleado . "'";
        $evidenciaVal = empty($this->evidencia) ? "NULL" : "'" . $this->evidencia . "'"; 
        
        return "INSERT INTO pqrs (Descripcion, Fecha, TipoPQRS_idTipoPQRS, Cliente_idCliente, Gerente_idGerente, Empleado_idEmpleado, Evidencia)
            VALUES (
                '" . $this->descripcion . "',
                '" . $this->fecha . "',
                '" . $this->tipoPQRS . "',
                '" . $this->cliente . "',
                '" . $this->gerente . "',
                " . $empleadoVal . ",
                " . $evidenciaVal . "
            )";
    }
    
    
    public function consultarHistorialPorCliente($idCliente) {
       
        return "
        SELECT
            p.idPQRS,
            p.Descripcion,
            p.Fecha,
            p.Evidencia,
            tp.Tipo AS TipoPQRS,
            COALESCE(e.Nombre, 'General') AS Empleado
        FROM pqrs p
        JOIN tipopqrs tp ON p.TipoPQRS_idTipoPQRS = tp.idTipoPQRS
        LEFT JOIN empleado e ON p.Empleado_idEmpleado = e.idEmpleado
        WHERE p.Cliente_idCliente = " . $idCliente . "
        ORDER BY p.idPQRS DESC";
    }
}
?>