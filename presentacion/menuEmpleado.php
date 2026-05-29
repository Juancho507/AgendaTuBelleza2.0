<?php
$id = $_SESSION["id"];
$empleado = new Empleado($id);
$empleado->consultar();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary px-3">
  
  <a class="navbar-brand" href="?pid=<?php echo base64_encode("presentacion/sesionEmpleado.php"); ?>">
    <i class="fa-solid fa-user-tie"></i> Panel Empleado
  </a>

  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarEmpleado" aria-controls="navbarEmpleado" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarEmpleado">
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">

      <li class="nav-item">
        <a class="nav-link" href="?pid=<?php echo base64_encode("presentacion/sesionEmpleado.php"); ?>">
          <i class="fa-solid fa-house"></i> Inicio
        </a>
      </li>
      
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="serviciosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fa-solid fa-calendar-days"></i> Citas
        </a>
        <ul class="dropdown-menu" aria-labelledby="serviciosDropdown">
          
          <li><h6 class="dropdown-header">Administración de Citas</h6></li>
          
          <li>
            <a class="dropdown-item" href="?pid=<?php echo base64_encode("presentacion/cita/agendarCita.php"); ?>">
              <i class="fa-solid fa-calendar-plus"></i> Agendar Cita
            </a>
          </li>
          
          <li>
            <a class="dropdown-item" href="?pid=<?php echo base64_encode("presentacion/cita/reprogramarCita.php"); ?>">
              <i class="fa-solid fa-arrows-rotate"></i> Reprogramar Cita
            </a>
          </li>
          
          <li>
            <a class="dropdown-item" href="?pid=<?php echo base64_encode("presentacion/cita/cancelarCita.php"); ?>">
              <i class="fa-solid fa-xmark"></i> Cancelar Cita
            </a>
          </li>
          
          <li><hr class="dropdown-divider"></li>
                        
          <li>
            <a class="dropdown-item" href="?pid=<?php echo base64_encode("presentacion/cita/consultarCita.php"); ?>">
              <i class="fa-solid fa-magnifying-glass"></i> Consultar Citas
            </a>
          </li>
          
        </ul>
      </li>
      
       <li class="nav-item">
        <a class="nav-link" href="?pid=<?php echo base64_encode("presentacion/empleado/editarPerfil.php"); ?>">
          <i class="fa-solid fa-pen-to-square"></i> Editar Información
        </a>
      </li>
      
      <li class="nav-item">
       <a class="nav-link" href="?pid=<?php echo base64_encode("presentacion/empleado/consultarAgenda.php"); ?>">
              <i class="fa-solid fa-calendar-days"></i> Consultar Agenda
        </a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link" href="?pid=<?php echo base64_encode("presentacion/empleado/asignarCita.php"); ?>">
              <i class="fa-solid fa-user-plus"></i> Asignar Cita
        </a>
      </li>


      <li class="nav-item">
        <a class="nav-link" href="?pid=<?php echo base64_encode("presentacion/empleado/consultarPQRS.php"); ?>">
          <i class="fa-solid fa-comments"></i> Consultar PQRS/Reseñas
        </a>
      </li>
      
       <li class="nav-item">
        <a class="nav-link" href="?pid=<?php echo base64_encode("presentacion/cita/envioRecordatorios.php"); ?>">
          <i class="fa-solid fa-bell"></i> Recordatorios
        </a>
      </li>

    </ul>

    
    <ul class="navbar-nav mb-2 mb-lg-0">
      
      <li class="nav-item">
        <span class="navbar-text text-white me-3">
          <i class="fa-solid fa-user"></i> <?php echo $empleado->getNombre() . " " . $empleado->getApellido(); ?>
        </span>
      </li>
      
      <li class="nav-item">
        <a class="nav-link text-warning" href="?pid=<?php echo base64_encode("presentacion/autenticarse.php"); ?>&sesion=false">
          <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
        </a>
      </li>
    </ul>
  </div>
</nav>