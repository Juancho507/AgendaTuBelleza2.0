<?php
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "gerente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

$action = $_GET['action'] ?? '';


function generarVistaDetalleEmpleado($empleado) {
    $estado = $empleado->getEstado() == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
    
    $servicios = is_array($empleado->getServicios()) ? implode(', ', $empleado->getServicios()) : ($empleado->getServicios() ?? 'N/A');
    $html = '<div class="p-3">';
    $html .= '<h4>Detalle de Empleado</h4>';
    $html .= '<ul class="list-group list-group-flush">';
    $html .= '<li class="list-group-item"><strong>Nombre:</strong> ' . $empleado->getNombre() . ' ' . $empleado->getApellido() . '</li>';
    $html .= '<li class="list-group-item"><strong>Correo:</strong> ' . $empleado->getCorreo() . '</li>';
    $html .= '<li class="list-group-item"><strong>Servicios:</strong> ' . $servicios . '</li>';
    $html .= '<li class="list-group-item"><strong>Estado:</strong> ' . $estado . '</li>';
    $html .= '</ul></div>';
    return $html;
}

function generarVistaDetalleCliente($cliente) {
    $estado = $cliente->getEstado() == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
    
    $html = '<div class="p-3">';
    $html .= '<h4>Detalle de Cliente</h4>';
    $html .= '<ul class="list-group list-group-flush">';
    $html .= '<li class="list-group-item"><strong>Nombre:</strong> ' . $cliente->getNombre() . ' ' . $cliente->getApellido() . '</li>';
    $html .= '<li class="list-group-item"><strong>Correo:</strong> ' . $cliente->getCorreo() . '</li>';
    $html .= '<li class="list-group-item"><strong>Teléfono:</strong> ' . $cliente->getTelefono() . '</li>';
    $html .= '<li class="list-group-item"><strong>Estado:</strong> ' . $estado . '</li>';
    $html .= '</ul></div>';
    return $html;
}


if ($action === 'verDetalle' && isset($_GET['id']) && isset($_GET['tipo'])) {
    $id = (int)$_GET['id'];
    $tipo = $_GET['tipo'];
    $htmlDetalle = '';
    $tituloDetalle = '';
    
    try {
        if ($id > 0) {
            if ($tipo === 'empleado') {
                $empleado = new Empleado($id);
                $empleado->consultar();
                $empleado->consultarServicios();
                $htmlDetalle = Empleado::generarVistaDetalle($empleado);
                $tituloDetalle = "Detalles del Empleado #{$id}";
            } elseif ($tipo === 'cliente') {
                $cliente = new Cliente($id);
                $cliente->consultar();
                $cliente->consultarTotalCitas();
                $htmlDetalle = Cliente::generarVistaDetalle($cliente);
                $tituloDetalle = "Detalles del Cliente #{$id}";
            }
        } else {
            $htmlDetalle = '<div class="alert alert-danger">ID no válido.</div>';
            $tituloDetalle = "Error";
        }
    } catch (Exception $e) {
        $htmlDetalle = '<div class="alert alert-danger">Error al cargar el detalle: ' . $e->getMessage() . '</div>';
        $tituloDetalle = "Error Fatal";
    }
    $_SESSION['detalleUsuario'] = [
        'html' => $htmlDetalle,
        'titulo' => $tituloDetalle
    ];
    header("Location: " . $_SERVER['PHP_SELF'] . "?pid=" . base64_encode('presentacion/gerente/BuscarGerente.php'));
    exit();
}


$termino = $_GET['searchInput'] ?? '';
$filtros = [
    'estado' => $_GET['estado'] ?? '',
    'minCitas' => $_GET['minCitas'] ?? '',
    'servicio' => $_GET['servicio'] ?? '',
];

$empleados = [];
$clientes = [];
$serviciosList = [];
$mensaje = "";

try {
    if (isset($filtros['servicio']) && $filtros['servicio'] === '99999') {
        throw new Exception("Fallo de conexión en la Base de Datos al cargar servicios.");
    }
    $serviciosList = Servicio::consultarTodos();
} catch (Exception $e) {
    error_log("Error al cargar lista de servicios: " . $e->getMessage());
    $mensaje = 'Error crítico al cargar servicios: ' . $e->getMessage();
}


$busquedaActiva = isset($_GET['searchInput']) || !empty(array_filter($filtros, fn($value) => $value !== '' && $value !== null));

if ($busquedaActiva || count($_GET) == 0) {
    try {
        if (strtoupper($termino) === 'ERROR_DB') {
            throw new Exception("Error de conexión a la Base de Datos. La consulta falló.");
        }
        $empleados = Empleado::buscarEmpleados($termino, $filtros);
        $clientes = Cliente::buscarClientes($termino, $filtros);
        
        if (count($empleados) + count($clientes) == 0 && empty($mensaje)) {
            $mensaje = 'No se encontraron coincidencias con los criterios de búsqueda.';
        }
        
    } catch (Exception $e) {
        $mensaje = '<i class="fa-solid fa-database"></i> Error al ejecutar la búsqueda debido a un fallo en la base de datos: ' . $e->getMessage();
        error_log($mensaje);
        $empleados = [];
        $clientes = [];
    }
} else {
    $mensaje = "Ingrese un término de búsqueda para comenzar o aplique filtros.";
}

$estadoSeleccionado = $filtros['estado'];
$minCitasSeleccionado = $filtros['minCitas'];
$servicioSeleccionado = $filtros['servicio'];

include("presentacion/encabezadoG.php");
include("presentacion/menuGerente.php");
?>

<div class="container-fluid mt-4">
    <h2 class="text-primary mb-4"><i class="fa-solid fa-magnifying-glass"></i> Búsqueda Unificada de Usuarios</h2>

    <div class="row">
        <div class="col-md-3">
            <div class="card bg-light shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Filtros Avanzados</h5>
                </div>
                <div class="card-body">
                    <form id="formFiltros" method="GET" action="index.php">
                        <input type="hidden" name="pid" value="<?php echo base64_encode('presentacion/gerente/BuscarGerente.php'); ?>">
                        
                        <h6 class="mt-2 text-primary">Estado:</h6>
                        <div class="form-check">
                            <input class="form-check-input filtro-check" type="radio" name="estado" id="estadoTodos" value="" <?php echo $estadoSeleccionado === '' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="estadoTodos">Todos</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input filtro-check" type="radio" name="estado" id="estadoActivo" value="1" <?php echo $estadoSeleccionado === '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="estadoActivo">Activo</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input filtro-check" type="radio" name="estado" id="estadoInactivo" value="0" <?php echo $estadoSeleccionado === '0' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="estadoInactivo">Inactivo</label>
                        </div>

                        <h6 class="mt-3 text-primary">Clientes:</h6>
                        <div class="mb-3">
                            <label for="minCitas" class="form-label">Citas Mínimas:</label>
                            <input type="number" class="form-control filtro-input" id="minCitas" name="minCitas" min="0" value="<?php echo htmlspecialchars($minCitasSeleccionado); ?>">
                        </div>
                        
                        <h6 class="mt-3 text-primary">Empleados:</h6>
                        <div class="mb-3">
                            <label for="servicioAsociado" class="form-label">Servicio Asignado:</label>
                            <select class="form-select filtro-input" id="servicioAsociado" name="servicio">
                                <option value="" <?php echo $servicioSeleccionado === '' ? 'selected' : ''; ?>>Todos los Servicios</option>
                                
                                <?php foreach ($serviciosList as $servicio): ?>
                                    <option 
                                        value="<?php echo $servicio['idServicio']; ?>" 
                                        <?php echo $servicioSeleccionado == $servicio['idServicio'] ? 'selected' : ''; ?>
                                    >
                                        <?php echo htmlspecialchars($servicio['Nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                                
                            </select>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Aplicar Filtros</button>
                            <a href="?pid=<?php echo base64_encode('presentacion/gerente/BuscarGerente.php'); ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-rotate-left"></i> Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <form method="GET" action="index.php">
                        <input type="hidden" name="pid" value="<?php echo base64_encode('presentacion/gerente/BuscarGerente.php'); ?>">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user-circle"></i></span>
                            <input type="text" id="searchInput" name="searchInput" class="form-control form-control-lg" placeholder="Buscar por Nombre, Apellido, Correo o Teléfono..." value="<?php echo htmlspecialchars($termino); ?>">
                            <button type="submit" class="btn btn-info"><i class="fa-solid fa-search"></i> Buscar</button>
                        </div>
                        <?php 
                        foreach ($filtros as $key => $value) {
                            if (!empty($value)) {
                                echo "<input type='hidden' name='{$key}' value='{$value}'>";
                            }
                        }
                        ?>
                    </form>
                </div>

                <div class="card-body">
                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-info" role="alert">
                            <i class="fa-solid fa-circle-info"></i> <?php echo $mensaje; ?>
                        </div>
                    <?php endif; ?>

                    <div id="resultadosContainer" class="row">
                        <div class="col-12 mb-3">
                            <h4 class="text-dark"><i class="fa-solid fa-briefcase"></i> Empleados (<span id="countEmpleados"><?php echo count($empleados); ?></span>)</h4>
                            <hr class="mt-1">
                            <div id="empleadosResultados" class="list-group">
                                <?php if (count($empleados) > 0): ?>
                                    <?php foreach ($empleados as $emp): 
                                        $estado = $emp['Estado'] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                                      
                                        $servicios = is_array($emp['Servicios']) ? implode(', ', $emp['Servicios']) : ($emp['Servicios'] ?? 'N/A'); 
                                    ?>
                                        <a href="?pid=<?php echo base64_encode('presentacion/gerente/BuscarGerente.php'); ?>&action=verDetalle&tipo=empleado&id=<?php echo $emp['idEmpleado']; ?>" 
                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($emp['Nombre'] . ' ' . $emp['Apellido']); ?></strong> (<?php echo htmlspecialchars($servicios); ?>)<br>
                                                <small class="text-muted"><?php echo htmlspecialchars($emp['Correo']); ?> | Tel: <?php echo htmlspecialchars($emp['Telefono']); ?></small>
                                            </div>
                                            <?php echo $estado; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center">No se encontraron empleados.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <h4 class="text-dark"><i class="fa-solid fa-users"></i> Clientes (<span id="countClientes"><?php echo count($clientes); ?></span>)</h4>
                            <hr class="mt-1">
                            <div id="clientesResultados" class="list-group">
                                <?php if (count($clientes) > 0): ?>
                                    <?php foreach ($clientes as $cli): 
                                        $estado = $cli['Estado'] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                                        $citas = $cli['citas_realizadas'] ?? 0;
                                    ?>
                                        <a href="?pid=<?php echo base64_encode('presentacion/gerente/BuscarGerente.php'); ?>&action=verDetalle&tipo=cliente&id=<?php echo $cli['idCliente']; ?>" 
                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($cli['Nombre'] . ' ' . $cli['Apellido']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($cli['Correo']); ?> | Citas: <?php echo $citas; ?></small>
                                            </div>
                                            <?php echo $estado; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center">No se encontraron clientes.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalTitulo">Detalles del Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalContenido">
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    
    const detalleSesion = <?php 
        if (isset($_SESSION['detalleUsuario'])) {
            $detalle = $_SESSION['detalleUsuario'];
            unset($_SESSION['detalleUsuario']); 
            echo json_encode($detalle);
        } else {
            echo 'null';
        }
    ?>;

   
    if (detalleSesion) {
        const modalTitulo = $('#modalDetalle').find('#modalTitulo');
        const modalContenido = $('#modalDetalle').find('#modalContenido');

        modalTitulo.text(detalleSesion.titulo);
        modalContenido.html(detalleSesion.html);
        
        const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
        modal.show();
    }
    
    $('.list-group-item-action').on('click', function(e) {
        
    });

});
</script>