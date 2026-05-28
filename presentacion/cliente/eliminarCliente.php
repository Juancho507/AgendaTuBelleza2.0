<?php
// ARCHIVO: presentacion/cliente/eliminarCliente.php (SOLO ESTE CONTENIDO)

if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

require_once "logica/Cliente.php";
$id = $_SESSION["id"];
$cliente = new Cliente($id);

$cliente->consultar();

if ($cliente->tieneCitasActivas()) {
    $_SESSION['error_eliminacion'] = "🚫 No puedes eliminar tu cuenta. Debes cancelar o completar todas tus citas activas o pendientes primero.";
    
    header("Location: ?pid=" . base64_encode("presentacion/cliente/editarCliente.php"));
    exit();
    
} else {
    $clienteInactivo = new Cliente(
        $cliente->getId(),
        $cliente->getNombre(),
        $cliente->getApellido(),
        $cliente->getCorreo(),
        $cliente->getContraseña(),
        $cliente->getTelefono(),
        0,
        $cliente->getFechaRegistro(),
        $cliente->getGerente(),
        $cliente->getFoto()
        );
    
    $clienteInactivo->actualizar();
    
    session_destroy();
    
    header("Location: ?pid=" . base64_encode("presentacion/autenticarse.php") . "&desactivado=1");
    exit();
}
?>