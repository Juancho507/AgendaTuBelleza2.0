<?php
if (!isset($_SESSION) || $_SESSION["rol"] != "gerente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}


$mensaje = "";
$idServicioAfectado = isset($_POST["idServicioAfectado"]) && is_numeric($_POST["idServicioAfectado"]) ? $_POST["idServicioAfectado"] : 0;
$accion = isset($_POST["accion"]) ? $_POST["accion"] : "";


if ($idServicioAfectado > 0) {
    
    $servicio = new Servicio($idServicioAfectado);
    
    if ($accion === "confirmar_inactivar") {
        
        
        if ($servicio->verificarDependencias()) {
            
            $mensaje = "<div class='alert alert-danger' role='alert'>
                            <i class='fa-solid fa-ban'></i>
                            No se puede desactivar. El servicio tiene citas activas pendientes.
                        </div>";
            
        } else {
            
            if ($servicio->inactivar()) {
                
                $mensaje = "<div class='alert alert-success' role='alert'>
                                <i class='fa-solid fa-check'></i>
                                Servicio desactivado con éxito.
                            </div>";
            } else {
                $mensaje = "<div class='alert alert-warning' role='alert'>
                                <i class='fa-solid fa-exclamation-circle'></i>
                                Ocurrió un error al intentar desactivar el servicio en la base de datos.
                            </div>";
            }
        }
        
    } elseif ($accion === "confirmar_activar") {
        
        if ($servicio->activar()) {
            $mensaje = "<div class='alert alert-success' role='alert'>
                            <i class='fa-solid fa-check'></i>
                            Servicio activado con éxito.
                        </div>";
        } else {
            $mensaje = "<div class='alert alert-warning' role='alert'>
                            <i class='fa-solid fa-exclamation-circle'></i>
                            Ocurrió un error al intentar activar el servicio.
                        </div>";
        }
    }
}

$servicios = Servicio::consultarTodos();

?>

<?php 
include("presentacion/encabezadoG.php");
include("presentacion/menuGerente.php");
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2 class="text-danger mb-4"><i class="fa-solid fa-sync-alt"></i> Activar y Desactivar Servicios</h2>
            
            <?php echo $mensaje; ?>

            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    Lista de Servicios
                </div>
                <div class="card-body">
                    <?php if (empty($servicios)): ?>
                        <div class="alert alert-info text-center" role="alert">
                            No hay servicios registrados actualmente.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Precio</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($servicios as $s): ?>
                                    <tr>
                                        <td><?php echo $s['idServicio']; ?></td>
                                        <td><?php echo htmlspecialchars($s['Nombre']); ?></td>
                                        <td>$<?php echo number_format($s['Precio'], 0, ',', '.'); ?></td>
                                        <td>
                                            <?php 
                                            $estado = $s['Estado'];
                                            $badgeClass = $estado == 1 ? 'bg-success' : 'bg-danger';
                                            $estadoTexto = $estado == 1 ? 'Activo' : 'Inactivo';
                                            echo "<span class='badge {$badgeClass}'>" . $estadoTexto . "</span>";
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($estado == 1):  ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalCambiarEstado"
                                                        data-id="<?php echo $s['idServicio']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($s['Nombre']); ?>"
                                                        data-accion="inactivar"
                                                        data-titulo="Desactivar Servicio"
                                                        data-mensaje="¿Está seguro de que desea desactivar el servicio? Si tiene citas activas, la acción será bloqueada.">
                                                    <i class="fa-solid fa-trash-can"></i> Desactivar
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalCambiarEstado"
                                                        data-id="<?php echo $s['idServicio']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($s['Nombre']); ?>"
                                                        data-accion="activar"
                                                        data-titulo="Reactivar Servicio"
                                                        data-mensaje="¿Está seguro de que desea activar el servicio? Estará inmediatamente disponible para citas.">
                                                    <i class="fa-solid fa-redo"></i> Activar
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-labelledby="modalCambiarEstadoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="?pid=<?php echo base64_encode("presentacion/gerente/eliminarServicio.php"); ?>" method="POST">
                <input type="hidden" name="idServicioAfectado" id="idServicioAfectadoInput">
                <input type="hidden" name="accion" id="accionInput">
                
                <div class="modal-header text-white" id="modalHeader">
                    <h5 class="modal-title" id="modalTitulo"><i class="fa-solid fa-triangle-exclamation"></i> Confirmar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3" id="modalMensaje"></p>
                    <p class="text-danger fw-bold fs-5" id="nombreServicioModal"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </button>
                    <button type="submit" class="btn" id="btnAceptar">
                        <i class="fa-solid fa-check"></i> Aceptar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalCambiarEstado = document.getElementById('modalCambiarEstado');
        
        modalCambiarEstado.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; 
            const idServicio = button.getAttribute('data-id');
            const nombreServicio = button.getAttribute('data-nombre');
            const accion = button.getAttribute('data-accion'); // 'activar' o 'inactivar'
            const titulo = button.getAttribute('data-titulo');
            const mensaje = button.getAttribute('data-mensaje');
            
            const modalHeader = document.getElementById('modalHeader');
            const btnAceptar = document.getElementById('btnAceptar');
            
           
            document.getElementById('idServicioAfectadoInput').value = idServicio;
            document.getElementById('accionInput').value = `confirmar_${accion}`; 
            
          
            document.getElementById('modalTitulo').textContent = titulo;
            document.getElementById('nombreServicioModal').textContent = nombreServicio;
            document.getElementById('modalMensaje').innerHTML = mensaje;

           
            if (accion === 'inactivar') {
                modalHeader.classList.remove('bg-success');
                modalHeader.classList.add('bg-danger');
                btnAceptar.classList.remove('btn-success');
                btnAceptar.classList.add('btn-danger');
            } else { // 'activar'
                modalHeader.classList.remove('bg-danger');
                modalHeader.classList.add('bg-success');
                btnAceptar.classList.remove('btn-danger');
                btnAceptar.classList.add('btn-success');
            }
        });
    });
</script>