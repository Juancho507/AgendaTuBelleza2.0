<?php
// ARCHIVO: presentacion/menuGerente.php
require_once("logica/Gerente.php");

$id = $_SESSION["id"];
$gerente = new Gerente($id);
$gerente->consultar();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
  
  <a class="navbar-brand" href="?pid=<?php echo base64_encode("presentacion/sesionGerente.php"); ?>">
    <i class="fa-solid fa-user-gear"></i> Panel Gerente
  </a>

  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarGerente" aria-controls="navbarGerente" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarGerente">
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">

      <li class="nav-item">
        <a class="nav-link" href="?pid=<?php echo base64_encode("presentacion/sesionGerente.php"); ?>">
          <i class="fa-solid fa-house"></i> Inicio
        </a>
      </li>

      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="serviciosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fa-solid fa-scissors"></i> Gestión de Servicios
        </a>
        <ul class="dropdown-menu" aria-labelledby="serviciosDropdown">
          
          <li><h6 class="dropdown-header">Administración de Servicios</h6></li>
          
          <li>
            <a class="dropdown-item" href="?pid=<?php echo base64_encode("presentacion/gerente/registrarServicio.php"); ?>">
              <i class="fa-solid fa-square-plus"></i> Registrar Servicio
            </a>
          </li>
          
          <li>
            <a class="dropdown-item" href="?pid=<?php echo base64_encode("presentacion/gerente/editarServicio.php"); ?>">
              <i class="fa-solid fa-pen-to-square"></i> Editar Servicio
            </a>
          </li>
          
          <li>
            <a class="dropdown-item" href="?pid=<?php echo base64_encode("presentacion/gerente/eliminarServicio.php"); ?>">
              <i class="fa-solid fa-trash-can"></i> Eliminar Servicio
            </a>
          </li>
          
          <li><hr class="dropdown-divider"></li>
          
          <li>
            <a class="dropdown-item" href="?pid=<?php echo base64_encode("presentacion/gerente/consultarServicios.php"); ?>">
              <i class="fa-solid fa-list-check"></i> Consultar Servicios
            </a>
          </li>
          
          <li>
            <a class="dropdown-item" href="?pid=<?php echo base64_encode("presentacion/gerente/estadisticasServicios.php"); ?>">
              <i class="fa-solid fa-chart-simple"></i> Consultar Estadísticas
            </a>
          </li>
          
        </ul>
      </li>
      </ul>

    
    <ul class="navbar-nav mb-2 mb-lg-0">
      <li class="nav-item">
        <span class="navbar-text text-white me-3">
          <?php echo $gerente->getNombre() . " " . $gerente->getApellido(); ?>
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