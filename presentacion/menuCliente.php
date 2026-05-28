<?php
$id = $_SESSION["id"];
$cliente = new Cliente($id);
$cliente->consultar();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
  <a class="navbar-brand" href="?pid=<?php echo base64_encode("presentacion/sesionCliente.php"); ?>">
    <i class="fa-solid fa-user"></i> Panel Cliente
  </a>

  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCliente" aria-controls="navbarCliente" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarCliente">
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">

      
      <li class="nav-item">
        <a class="nav-link" href="?pid=<?php echo base64_encode("presentacion/sesionCliente.php"); ?>">
          <i class="fa-solid fa-house"></i> Inicio
        </a>
      </li>

     
      <li class="nav-item">
        <a class="nav-link" href="?pid=<?php echo base64_encode("presentacion/cliente/editarCliente.php"); ?>">
          <i class="fa-solid fa-user-pen"></i> Editar Información
        </a>
      </li>

      
      <li class="nav-item">
        <a class="nav-link" href="?pid=<?php echo base64_encode("presentacion/cliente/registrarPQRS.php"); ?>">
          <i class="fa-solid fa-envelope-circle-check"></i> PQRS
        </a>
      </li>

      
      <li class="nav-item">
        <a class="nav-link" href="?pid=<?php echo base64_encode("presentacion/cliente/historialCliente.php"); ?>">
          <i class="fa-solid fa-clock-rotate-left"></i> Historial
        </a>
      </li>


    </ul>

    
    <ul class="navbar-nav mb-2 mb-lg-0">
      <li class="nav-item">
        <span class="navbar-text text-white me-3">
          <?php echo $cliente->getNombre() . " " . $cliente->getApellido(); ?>
        </span>
      </li>
      <li class="nav-item">
        <a class="nav-link text-danger" href="?pid=<?php echo base64_encode("presentacion/autenticarse.php"); ?>&sesion=false">
          <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
        </a>
      </li>
    </ul>
  </div>
</nav>
