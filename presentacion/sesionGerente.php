<?php

if ($_SESSION["rol"] != "gerente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

$id = $_SESSION["id"];

include("presentacion/encabezadoG.php");
include("presentacion/menuGerente.php");

$gerente = new Gerente($id);
$gerente->consultar();
?>

<div class="container mt-5">
  <div class="row">
    <div class="col-md-7 mx-auto"> 
      <div class="card shadow-sm">
        <div class="card-body">
          <h2 class="my-2 text-center text-info"><i class="fa-solid fa-user-gear"></i> Perfil del Gerente</h2>
          
          <div class="table-responsive-sm my-4">
            <table class="table table-striped table-hover">
              <tbody>
                <tr>
                  <th>ID Gerente</th>
                  <td><?php echo htmlspecialchars($gerente->getId()); ?></td>
                </tr>
                <tr>
                  <th>Nombre</th>
                  <td><?php echo htmlspecialchars($gerente->getNombre()); ?></td>
                </tr>
                <tr>
                  <th>Apellido</th>
                  <td><?php echo htmlspecialchars($gerente->getApellido()); ?></td>
                </tr>
                <tr>
                  <th>Correo</th>
                  <td><?php echo htmlspecialchars($gerente->getCorreo()); ?></td>
                </tr>
                <tr>
                  <th>Teléfono</th>
                  <td><?php echo htmlspecialchars($gerente->getTelefono()); ?></td>
                </tr>
                </tbody>
            </table>
          </div>
          
        </div>
      </div>
    </div>
  </div>
</div>