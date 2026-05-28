<?php
class EstadoCitaDAO {
    
    private $idEstadoCita;
    private $tipo; 
    
    public function __construct(
        $idEstadoCita = "",
        $tipo = ""
        ) {
            $this->idEstadoCita = $idEstadoCita;
            $this->tipo = $tipo;
    }
    
    public function consultarTodos() {
        return "SELECT idEstadoCita AS id, Tipo AS tipo FROM estadocita ORDER BY idEstadoCita ASC";
    }
    
    public function consultar() {
        return "SELECT * FROM estadocita WHERE idEstadoCita = " . $this->idEstadoCita;
    }
    
}
?>