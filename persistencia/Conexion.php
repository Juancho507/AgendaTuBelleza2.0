<?php

class Conexion{
    private $conexion;
    private $resultado;
    
    public function abrir(){
        $this -> conexion = new mysqli("localhost", "root", "", "mydb");
    }
    
    public function cerrar(){
        $this -> conexion -> close();
    }
    
    public function ejecutar($sentencia){
        $this -> resultado = $this -> conexion -> query($sentencia);
    }
    
    public function registro(){
        return $this -> resultado -> fetch_row();
    }
    
    public function filas(){
        return $this -> resultado -> num_rows;
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