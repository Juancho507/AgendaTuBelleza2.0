<?php

if (!isset($_SESSION) || $_SESSION["rol"] != "gerente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}


$mensaje = "";
$idServicio = isset($_REQUEST["idServicio"]) && is_numeric($_REQUEST["idServicio"]) ? $_REQUEST["idServicio"] : 0;
$productos = Producto::consultarTodos();
$servicios = Servicio::consultarTodos();

$modoEdicion = ($idServicio > 0);
$servicio = new Servicio($idServicio);



if (isset($_POST["actualizar"])) {
    $idServicioPost = $_POST["idServicio"];
    $nombre = $_POST["nombre"];
    $descripcion = $_POST["descripcion"];
    $precio = $_POST["precio"];
    $idProducto = $_POST["idProducto"];
    
    $servicioOriginal = new Servicio($idServicioPost);
    $servicioOriginal->consultar();
    $estadoOriginal = $servicioOriginal->getEstado();
    
    $servicioActualizar = new Servicio(
        $idServicioPost,
        $nombre,
        $descripcion,
        $precio,
        $estadoOriginal, 
        $idProducto,
        $_SESSION["id"]
        );
    $servicioActualizar->actualizar();
    
    $mensaje = "<div class='alert alert-success' role='alert'>Servicio actualizado correctamente.</div>";
    
    $idServicio = 0;
    $modoEdicion = false;
    $servicios = Servicio::consultarTodos();
    
}

if ($modoEdicion) {
    $consultado = $servicio->consultar();
    
    if (!$consultado) {
        $mensaje = "<div class='alert alert-danger' role='alert'>El servicio no fue encontrado o el ID es inválido.</div>";
        $modoEdicion = false;
    }
}


?>

<?php 
include("presentacion/encabezadoG.php");
include("presentacion/menuGerente.php");
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2 class="text-danger mb-4"><i class="fa-solid fa-pen-to-square"></i> Gestión de Servicios</h2>
            
            <?php echo $mensaje; ?>

            <?php if (!$modoEdicion):  ?>
                
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        Lista de Servicios Registrados
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
                                            <th>Acciones</th>
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
                                            <td>
                                                <a href="?pid=<?php echo base64_encode("presentacion/gerente/editarServicio.php") . "&idServicio=" . $s['idServicio']; ?>" class="btn btn-sm btn-outline-info" title="Editar">
                                                    <i class="fa-solid fa-pen-to-square"></i> Editar
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                
                <div class="col-lg-8 offset-lg-2">
                    <h4 class="text-danger mb-4">Editando: <?php echo htmlspecialchars($servicio->getNombre()); ?></h4>
                    
                    <div class="card shadow">
                        <div class="card-header bg-danger text-white">
                            Información del Servicio
                        </div>
                        <div class="card-body">
                            
                            <form action="?pid=<?php echo base64_encode("presentacion/gerente/editarServicio.php"); ?>" method="POST" id="formEditarServicio">
                                
                                <input type="hidden" name="idServicio" value="<?php echo $servicio->getIdServicio(); ?>">

                                <div class="mb-3">
                                    <label for="nombre" class="form-label fw-bold">Nombre del Servicio <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($servicio->getNombre()); ?>" required>
                                    <div class="invalid-feedback" id="feedbackNombre">El nombre no puede estar vacío y no debe ser duplicado.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label fw-bold">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($servicio->getDescripcion()); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="precio" class="form-label fw-bold">Precio ($) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0.01" class="form-control" id="precio" name="precio" value="<?php echo htmlspecialchars($servicio->getPrecio()); ?>" required>
                                        <div class="invalid-feedback">El precio debe ser numérico y mayor que cero.</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="idProducto" class="form-label fw-bold">Producto Principal <span class="text-danger">*</span></label>
                                        <select class="form-select" id="idProducto" name="idProducto" required>
                                            <option value="">Seleccione un producto</option>
                                            <?php 
                                            $idProductoActual = $servicio->getProductoIdProducto();
                                            foreach ($productos as $producto) {
                                                $selected = ($producto['idProducto'] == $idProductoActual) ? 'selected' : '';
                                                echo "<option value='{$producto['idProducto']}' {$selected}>" . htmlspecialchars($producto['Nombre']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                        <div class="invalid-feedback">Debe asociar un producto.</div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="?pid=<?php echo base64_encode("presentacion/gerente/editarServicio.php"); ?>" class="btn btn-secondary">
                                        <i class="fa-solid fa-xmark"></i> Cancelar
                                    </a>
                                    
                                    <button type="submit" name="actualizar" class="btn btn-danger" id="btnGuardarCambios">
                                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                                    </button>
                                </div>
                            </form>
                            
                            </div>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formEditarServicio');
        
        if (form) {
            const nombreInput = document.getElementById('nombre');
            const precioInput = document.getElementById('precio');
            const btnGuardar = document.getElementById('btnGuardarCambios');
            const feedbackNombre = document.getElementById('feedbackNombre');
            
            const nombreOriginal = "<?php echo addslashes(strtolower($servicio->getNombre())); ?>";
            
           
            const serviciosExistentes = <?php 
                $nombresExistentes = array_column(Servicio::consultarTodos(), 'Nombre');
                $nombresFiltrados = array_filter($nombresExistentes, function($nombre) use ($nombreOriginal) {
                    return strtolower($nombre) !== $nombreOriginal;
                });
                echo json_encode(array_map('strtolower', array_values($nombresFiltrados))); 
            ?>; 

            nombreInput.addEventListener('input', validarNombre);
            precioInput.addEventListener('input', validarPrecio);
            
            function validarNombre() {
                const nombreNormalizado = nombreInput.value.trim().toLowerCase();
                const esDuplicado = serviciosExistentes.includes(nombreNormalizado);

                nombreInput.classList.remove('is-invalid');
                feedbackNombre.textContent = "El nombre no puede estar vacío y no debe ser duplicado.";
                btnGuardar.disabled = false;
                
                if (nombreInput.value.trim() === '') {
                    nombreInput.classList.add('is-invalid');
                    feedbackNombre.textContent = "El nombre del servicio no puede quedar vacío.";
                    return false;
                } else if (esDuplicado) {
                    nombreInput.classList.add('is-invalid');
                    feedbackNombre.textContent = "Ya existe un servicio con este nombre.";
                    btnGuardar.disabled = true;
                    return false;
                }
                return true;
            }

            function validarPrecio() {
                const precio = parseFloat(precioInput.value);
                if (isNaN(precio) || precio <= 0) {
                    precioInput.classList.add('is-invalid');
                    return false;
                } else {
                    precioInput.classList.remove('is-invalid');
                    return true;
                }
            }
            
            form.addEventListener('submit', function(event) {
                const nombreValido = validarNombre();
                const precioValido = validarPrecio();
                
                if (!nombreValido || !precioValido) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';
                    btnGuardar.disabled = true;
                }
            }, false);
        }
    });
</script>