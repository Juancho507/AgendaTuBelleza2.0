<?php
if ($_SESSION["rol"] != "cliente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}
$id = $_SESSION["id"];
?>
<body>
<?php 
include("presentacion/encabezadoC.php");
include("presentacion/menuCliente.php");

$cliente = new Cliente($id);
$cliente->consultar();
?>
<div class="container mt-5">
  <div class="row">
    <div class="col-md-7 mx-auto"> 
      <div class="card shadow-sm">
        <div class="card-body">
          <h2 class="my-2 text-center text-primary">Perfil</h2>
          
          <div class="text-center mb-4">
            <?php
            if ($cliente->getFoto() != "" && file_exists($cliente->getFoto())) {
                echo "<img src='" . $cliente->getFoto() . "' class='rounded-circle shadow-lg' height='150' alt='Foto de perfil'>";
            } else {
                echo "
                <p class='text-muted mt-2'>No hay foto de perfil.</p>";
            }
            ?>
          </div>
          
          <div class="table-responsive-sm my-4">
            <table class="table table-striped table-hover">
              <tbody>
                <tr>
                  <th>Nombre</th>
                  <td><?php echo htmlspecialchars($cliente->getNombre()); ?></td>
                </tr>
                <tr>
                  <th>Apellido</th>
                  <td><?php echo htmlspecialchars($cliente->getApellido()); ?></td>
                </tr>
                <tr>
                  <th>Correo</th>
                  <td><?php echo htmlspecialchars($cliente->getCorreo()); ?></td>
                </tr>
                <tr>
                  <th>Teléfono</th>
                  <td><?php echo htmlspecialchars($cliente->getTelefono()); ?></td>
                </tr>
                <tr>
                  <th>Estado de Cuenta</th>
                  <td>
                    <?php 
                    $estado = $cliente->getEstado();
                    if ($estado == 1) {
                        echo '<span class="badge bg-success">Activo</span>';
                    } else {
                        echo '<span class="badge bg-danger">Inactivo</span>';
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