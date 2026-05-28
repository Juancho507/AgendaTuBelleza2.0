<?php

class Conexion{
    private $conexion;
    private $resultado;
    
    public function abrir(){
        $this -> conexion = new mysqli("localhost", "root", "", "mydb");
        if ($this->conexion->connect_error) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }
    }
    
    public function cerrar(){
        if ($this->conexion) {
            $this -> conexion -> close();
        }
    }
    
    public function ejecutar($sentencia){
        $this -> resultado = $this -> conexion -> query($sentencia);
        
        if ($this->resultado === false) {
            error_log("⛔️ ERROR DE CONSULTA SQL: " . $this->conexion->error . " | SQL: " . $sentencia);
          
        }
    }
    
    public function registro(){
        if ($this->resultado instanceof mysqli_result) {
            return $this -> resultado -> fetch_row();
        }
        return false;
    }
    
    public function filas(){
        if ($this->resultado instanceof mysqli_result) {
            return $this -> resultado -> num_rows;
        }
        return 0; 
    }
    
    public function getResultado(){
        return $this -> resultado;
    }
    
    public function obtenerId() {
        return $this->conexion->insert_id;
    }
    
    public function afectadas() {
        return $this->conexion->affected_rows;
    }
}
?>