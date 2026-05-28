<?php

if (!isset($_SESSION) || $_SESSION["rol"] != "gerente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}


$idGerente = $_SESSION["id"];
$mensaje = "";

if (isset($_POST["registrar"])) {
    
    $nombre = $_POST["nombre"];
    $descripcion = $_POST["descripcion"];
    $precio = $_POST["precio"];
    $idProducto = $_POST["idProducto"];
    
    $servicio = new Servicio("", $nombre, $descripcion, $precio, 1, $idProducto, $idGerente);
    $exito = $servicio->registrar();
    
    if ($exito) {
        $msg_codificado = base64_encode("Servicio registrado correctamente.");
        header("Location: ?pid=" . base64_encode("presentacion/gerente/registrarServicio.php") . "&msg=" . $msg_codificado);
        exit();
    } else {
        $mensaje = "<div class='alert alert-danger' role='alert'>Error al registrar el servicio en la base de datos.</div>";
    }
}
if (isset($_GET["msg"])) {
    $mensaje = "<div class='alert alert-success' role='alert'>" . htmlspecialchars(base64_decode($_GET["msg"])) . "</div>";
}

$servicios = Servicio::consultarTodos();
$productos = Producto::consultarTodos();
?>

<?php 
include("presentacion/encabezadoG.php");
include("presentacion/menuGerente.php");
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2 class="text-danger mb-4"><i class="fa-solid fa-scissors"></i> Registro de Servicios</h2>
            
            <?php echo $mensaje;  ?>

            <div class="card shadow">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    Lista de Servicios Registrados
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalRegistroServicio">
                        <i class="fa-solid fa-square-plus"></i> Registrar Nuevo Servicio
                    </button>
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
                                        <th>Descripción</th>
                                        <th>Precio</th>
                                        <th>Producto Asoc.</th>
                                        <th>Estado</th>
                                        </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($servicios as $s): ?>
                                    <tr>
                                        <td><?php echo $s['idServicio']; ?></td>
                                        <td><?php echo htmlspecialchars($s['Nombre']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($s['Descripcion'], 0, 50)) . (strlen($s['Descripcion']) > 50 ? '...' : ''); ?></td>
                                        <td>$<?php echo number_format($s['Precio'], 0, ',', '.'); ?></td>
                                        <td><?php echo htmlspecialchars($s['NombreProducto']); ?></td>
                                        <td>
                                            <?php 
                                            $badgeClass = $s['Estado'] == 1 ? 'bg-success' : 'bg-danger';
                                            echo "<span class='badge {$badgeClass}'>" . $s['EstadoTexto'] . "</span>";
                                            ?>
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

<div class="modal fade" id="modalRegistroServicio" tabindex="-1" aria-labelledby="tituloModalRegistro" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="?pid=<?php echo base64_encode("presentacion/gerente/registrarServicio.php"); ?>" method="POST" id="formRegistroServicio">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="tituloModalRegistro">
                        <i class="fa-solid fa-square-plus"></i> Registrar Nuevo Servicio
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                    <div id="alertaDuplicado" class="alert alert-warning d-none" role="alert">
                        Ya existe un servicio con este nombre.
                    </div>
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-bold">Nombre del Servicio <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                        <div class="invalid-feedback">El nombre no puede estar vacío.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-bold">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="precio" class="form-label fw-bold">Precio ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="precio" name="precio" required>
                            <div class="invalid-feedback">El precio debe ser numérico y mayor que cero.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="idProducto" class="form-label fw-bold">Producto Principal <span class="text-danger">*</span></label>
                            <select class="form-select" id="idProducto" name="idProducto" required>
                                <option value="">Seleccione un producto</option>
                                <?php 
                                foreach ($productos as $producto) {
                                    echo "<option value='{$producto['idProducto']}'>" . htmlspecialchars($producto['Nombre']) . "</option>";
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">Debe asociar un producto.</div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </button>
                    <button type="submit" name="registrar" class="btn btn-danger">
                        <i class="fa-solid fa-floppy-disk"></i> Registrar Servicio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formRegistroServicio');
        const nombreInput = document.getElementById('nombre');
        const precioInput = document.getElementById('precio');
        const alertaDuplicado = document.getElementById('alertaDuplicado');
        const serviciosExistentes = <?php echo json_encode(array_map('strtolower', array_column($servicios, 'Nombre'))); ?>; 

        nombreInput.addEventListener('input', function() {
            const nombreNormalizado = this.value.trim().toLowerCase();
            const esDuplicado = serviciosExistentes.includes(nombreNormalizado);

            nombreInput.classList.remove('is-invalid');
            alertaDuplicado.classList.add('d-none');

            if (this.value.trim() === '') {
                 nombreInput.classList.add('is-invalid');
            } else if (esDuplicado) {
                nombreInput.classList.add('is-invalid');
                alertaDuplicado.classList.remove('d-none');
            }
        });
        
       
        precioInput.addEventListener('input', function() {
            const precio = parseFloat(this.value);
            if (isNaN(precio) || precio <= 0) {
                precioInput.classList.add('is-invalid');
            } else {
                precioInput.classList.remove('is-invalid');
            }
        });

        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            const nombreNormalizado = nombreInput.value.trim().toLowerCase();
            if (nombreInput.value.trim() === '' || serviciosExistentes.includes(nombreNormalizado)) {
                nombreInput.classList.add('is-invalid');
                isValid = false;
            } else {
                nombreInput.classList.remove('is-invalid');
            }
            const precio = parseFloat(precioInput.value);
            if (isNaN(precio) || precio <= 0) {
                precioInput.classList.add('is-invalid');
                isValid = false;
            } else {
                precioInput.classList.remove('is-invalid');
            }
            
            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
            }
          
        }, false);
    });
</script>