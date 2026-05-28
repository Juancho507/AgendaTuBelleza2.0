<?php

if (!isset($_SESSION) || $_SESSION["rol"] != "empleado") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}
$id = $_SESSION["id"];

$empleado = new Empleado($id);
$empleado->consultar();

?>
<body>
<?php 
include("presentacion/encabezadoE.php"); 
include("presentacion/menuEmpleado.php"); 
?>
<div class="container mt-5">
  <div class="row">
    <div class="col-md-7 mx-auto"> 
      <h2 class="my-4 text-center text-secondary"><i class="fa-solid fa-user-tie"></i> Perfil del Empleado</h2>
      <div class="card shadow-lg border-secondary">
        <div class="card-body">
          
          <div class="text-center mb-4">
            <?php
            if ($empleado->getFoto() != "" && file_exists($empleado->getFoto())) {
                echo "<img src='" . $empleado->getFoto() . "' class='rounded-circle shadow-lg' height='150' alt='Foto de perfil'>";
            } else {
                echo "
                <p class='text-muted mt-2'>No hay foto de perfil.</p>";
            }
            ?>
          </div>
          
          <div class="table-responsive-sm my-4">
            <table class="table table-striped table-hover align-middle">
              <tbody>
              
                <tr>
                  <th>Id</th>
                  <td><?php echo htmlspecialchars($empleado->getId()); ?></td>
                </tr>
                
                <tr>
                  <th>Nombre</th>
                  <td><?php echo htmlspecialchars($empleado->getNombre()); ?></td>
                </tr>
                
                <tr>
                  <th>Apellido</th>
                  <td><?php echo htmlspecialchars($empleado->getApellido()); ?></td>
                </tr>
                
                <tr>
                  <th>Correo</th>
                  <td><?php echo htmlspecialchars($empleado->getCorreo()); ?></td>
                </tr>
                
                <tr>
                  <th>Teléfono</th>
                  <td><?php echo htmlspecialchars($empleado->getTelefono()); ?></td>
                </tr>
                
                <tr>
                  <th>Salario</th>
                  <td>$<?php echo number_format($empleado->getSalario(), 0, ',', '.'); ?></td>
                </tr>

                <tr>
                  <th>Horario</th>
                  <td><?php echo htmlspecialchars($empleado->getHorario()); ?></td>
                </tr>
                <tr>               
                  <th>Estado de Cuenta</th>
                  <td>
                    <?php 
                    $estado = $empleado->getEstado();
                    if ($estado == 1) {
                        echo '<span class="badge bg-success"><i class="fa-solid fa-circle-check"></i> Activo</span>';
                    } else {
                        echo '<span class="badge bg-danger"><i class="fa-solid fa-circle-xmark"></i> Inactivo</span>';
                    }
                    ?>
                  </td>
                </tr>
             
              </tbody>
            </table>
          </div>
          
          
        </div>
      </div>
    </div>
  </div>
</div>
</body>