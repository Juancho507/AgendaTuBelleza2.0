<?php
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "gerente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}


$idGerente = $_SESSION["id"];
$mensaje = "";

function esc($v) {
    return addslashes(trim($v));
}

try {
    $conexion = new Conexion();
    $conexion->abrir();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && isset($_POST['idEmpleado'])) {
        $accion = $_POST['accion'];
        $idEmpleado = (int) $_POST['idEmpleado'];
        $empleado = new Empleado($idEmpleado);
        $ok = false;
        
        if ($accion === 'aceptar') {
            $salario = isset($_POST['salario']) ? trim($_POST['salario']) : '';
            $horario = isset($_POST['horario']) ? trim($_POST['horario']) : '';
            
            if ($salario === '' || !is_numeric($salario)) {
                $mensaje = "<div class='alert alert-warning'>Ingrese un salario numérico válido para poder aceptar el empleado.</div>";
            } else {
                $ok = $empleado->aprobar($salario, $horario, $idGerente);
                
                if ($ok) {
                    $mensaje = "<div class='alert alert-success'>Empleado aprobado y activado correctamente.</div>";
                } else {
                    $mensaje = "<div class='alert alert-danger'>No se pudo aprobar el empleado. Es posible que ya haya sido procesado o hubo un error.</div>";
                }
            }
        }
        
        if ($accion === 'inactivar') {
            if (Cita::consultarActivasHoyPorEmpleado($idEmpleado)) {
                $mensaje = "<div class='alert alert-danger'>El empleado no puede ser inactivado/eliminado porque tiene citas o servicios activos o pendientes. Primero deben ser cancelados o finalizados.</div>";
            } else {
                $ok = $empleado->inactivar();
                
                if ($ok) {
                    $mensaje = "<div class='alert alert-success'>Empleado inactivado (eliminado de la vista principal) correctamente.</div>";
                } else {
                    $mensaje = "<div class='alert alert-danger'>No se pudo inactivar el empleado.</div>";
                }
            }
        }
        
        if ($accion === 'reactivar') {
            $ok = $empleado->reactivar();
            if ($ok) {
                $mensaje = "<div class='alert alert-success'>Empleado reactivado correctamente.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger'>No se pudo reactivar el empleado.</div>";
            }
        }
        if ($accion === 'observar') {
            $mensaje = "<div class='alert alert-info'>Solicitud no aceptada. Se mantiene inactiva.</div>";
        }
    }
    $pendientes = [];
    $sqlPend = "SELECT idEmpleado, Nombre, Apellido, Correo, Estado, Salario, Horario, Gerente_idGerente, Foto, HojaDeVida
                FROM empleado
                WHERE Estado = 0 AND (Salario IS NULL OR Salario = 0)
                ORDER BY idEmpleado DESC";
    $conexion->ejecutar($sqlPend);
    while ($r = $conexion->registro()) {
        $pendientes[] = [
            "idEmpleado" => $r[0],
            "Nombre" => $r[1],
            "Apellido" => $r[2],
            "Correo" => $r[3],
            "Estado" => $r[4],
            "Salario" => $r[5],
            "Horario" => $r[6],
            "Gerente" => $r[7],
            "Foto" => $r[8],
            "HojaDeVida" => $r[9]
        ];
    }
    
    $activos = [];
    $sqlAct = "SELECT idEmpleado, Nombre, Apellido, Correo, Estado, Salario, Horario, Gerente_idGerente, Foto, HojaDeVida
               FROM empleado
               WHERE Estado = 1 OR (Estado = 0 AND (Salario IS NOT NULL AND Salario <> 0))
               ORDER BY Estado DESC, idEmpleado DESC";
    $conexion->ejecutar($sqlAct);
    while ($r = $conexion->registro()) {
        $activos[] = [
            "idEmpleado" => $r[0],
            "Nombre" => $r[1],
            "Apellido" => $r[2],
            "Correo" => $r[3],
            "Estado" => $r[4],
            "Salario" => $r[5],
            "Horario" => $r[6],
            "Gerente" => $r[7],
            "Foto" => $r[8],
            "HojaDeVida" => $r[9]
        ];
    }
    
    $conexion->cerrar();
} catch (Exception $e) {
    $mensaje = "<div class='alert alert-danger'>Error de conexión o del servidor: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<?php 
include("presentacion/encabezadoG.php");
include("presentacion/menuGerente.php");
?>

<div class="container mt-5">
    <h2 class="text-danger mb-3"><i class="fa-solid fa-user-check"></i> Gestión de Empleados</h2>

    <?php echo $mensaje; ?>
    
    <div class="card mb-4">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <span>Solicitudes Pendientes de Aprobación</span>
        </div>
        <div class="card-body">
            <?php if (empty($pendientes)): ?>
                <div class="alert alert-info text-center">No hay solicitudes pendientes.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Foto / Hoja</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendientes as $p): ?>
                            <tr>
                                <td><?php echo $p['idEmpleado']; ?></td>
                                <td><?php echo htmlspecialchars($p['Nombre'] . " " . $p['Apellido']); ?></td>
                                <td><?php echo htmlspecialchars($p['Correo']); ?></td>
                                <td>
                                    <?php if (!empty($p['Foto'])): ?>
                                        <a href="<?php echo htmlspecialchars($p['Foto']); ?>" target="_blank">Ver Foto</a><br>
                                    <?php endif; ?>
                                    <?php if (!empty($p['HojaDeVida'])): ?>
                                        <a href="<?php echo htmlspecialchars($p['HojaDeVida']); ?>" target="_blank">Ver HojaDeVida</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalDetalle<?php echo $p['idEmpleado']; ?>">
                                        <i class="fa-solid fa-eye"></i> Revisar
                                    </button>
                                </td>
                            </tr>
                            <div class="modal fade" id="modalDetalle<?php echo $p['idEmpleado']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Solicitud: <?php echo htmlspecialchars($p['Nombre'] . " " . $p['Apellido']); ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>

                                        <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="idEmpleado" value="<?php echo $p['idEmpleado']; ?>">

                                            <div class="row">
                                                <div class="col-md-4 text-center">
                                                    <?php if (!empty($p['Foto'])): ?>
                                                        <img src="<?php echo htmlspecialchars($p['Foto']); ?>" class="img-fluid rounded mb-2" alt="Foto">
                                                    <?php else: ?>
                                                        <div class="border rounded py-5">Sin foto</div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($p['HojaDeVida'])): ?>
                                                        <a href="<?php echo htmlspecialchars($p['HojaDeVida']); ?>" target="_blank" class="btn btn-outline-secondary btn-sm mb-2">
                                                            <i class="fa-solid fa-file-pdf"></i> Descargar Hoja de Vida
                                                        </a>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="col-md-8">
                                                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($p['Nombre'] . " " . $p['Apellido']); ?></p>
                                                    <p><strong>Correo:</strong> <?php echo htmlspecialchars($p['Correo']); ?></p>
                                                    <p><strong>Estado actual:</strong>
                                                        <?php
                                                            $estadoText = ($p['Estado'] == 0) ? "En revisión / No activo" : "Activo";
                                                            $badgeClass = ($p['Estado'] == 0) ? "bg-secondary" : "bg-success";
                                                            echo "<span class='badge {$badgeClass}'>" . $estadoText . "</span>";
                                                        ?>
                                                    </p>

                                                    <hr>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Salario (COP)</label>
                                                        <input type="number" step="1" min="0" name="salario" class="form-control" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Horario</label>
                                                        <input type="text" name="horario" class="form-control" placeholder="Ej: L-V 9am-6pm" required>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="submit" name="accion" value="aceptar" class="btn btn-success">
                                                <i class="fa-solid fa-check"></i> Aceptar Registro
                                            </button>

                                            <button type="submit" name="accion" value="observar" class="btn btn-warning">
                                                <i class="fa-solid fa-user-slash"></i> Rechazar / Observar
                                            </button>

                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>Empleados Activos / Inactivos</span>
        </div>

        <div class="card-body">
            <?php if (empty($activos)): ?>
                <div class="alert alert-info text-center">No hay empleados activos o inactivos.</div>
            <?php else: ?>
                <div class="mb-3">
                    <input type="text" id="filtroTabla" class="form-control" placeholder="Buscar por Nombre, Correo, Salario o Horario...">
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tablaEmpleados">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Estado</th>
                                <th>Salario</th>
                                <th>Horario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activos as $a): ?>
                                <tr>
                                    <td><?php echo $a['idEmpleado']; ?></td>
                                    <td><?php echo htmlspecialchars($a['Nombre'] . " " . $a['Apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($a['Correo']); ?></td>
                                    <td>
                                        <?php
                                            $et = $a['Estado'];
                                            $txt = $et==1 ? "Activo" : ($et==0 ? "Inactivo" : "Desconocido");
                                            $cl = $et==1 ? "bg-success" : "bg-danger";
                                            echo "<span class='badge {$cl}'>".$txt."</span>";
                                        ?>
                                    </td>
                                    <td data-filtro="<?php echo $a['Salario'] ?? ''; ?>"><?php echo $a['Salario'] ? number_format($a['Salario'],0,',','.') : '-'; ?></td>
                                    <td data-filtro="<?php echo htmlspecialchars($a['Horario']); ?>"><?php echo htmlspecialchars($a['Horario']); ?></td>
                                    <td>
                                        <?php if ($a['Estado'] == 1): ?>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalConfirmarInactivar"
                                                data-id="<?php echo $a['idEmpleado']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($a['Nombre'] . " " . $a['Apellido']); ?>"
                                                data-correo="<?php echo htmlspecialchars($a['Correo']); ?>">
                                                <i class="fa-solid fa-user-slash"></i> Inactivar
                                            </button>
                                        <?php else: ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="idEmpleado" value="<?php echo $a['idEmpleado']; ?>">
                                                <button type="submit" name="accion" value="reactivar" class="btn btn-success btn-sm">
                                                    <i class="fa-solid fa-user-check"></i> Reactivar
                                                </button>
                                            </form>
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

<div class="modal fade" id="modalConfirmarInactivar" tabindex="-1" aria-labelledby="modalConfirmarInactivarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalConfirmarInactivarLabel">⚠️ Confirmar Inactivación de Empleado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="formInactivar">
                <div class="modal-body">
                    <input type="hidden" name="idEmpleado" id="modalIdEmpleado">
                    <input type="hidden" name="accion" value="inactivar">
                    
                    <p class="lead">¿Está seguro de inactivar al empleado?</p>
                    
                    <div class="p-3 mb-3 bg-light rounded">
                        <strong>Nombre:</strong> <span id="modalNombreEmpleado"></span><br>
                        <strong>Correo:</strong> <span id="modalCorreoEmpleado"></span>
                    </div>

                    <div class="alert alert-danger fw-bold">
                        <i class="fa-solid fa-triangle-exclamation"></i> ADVERTENCIA: Esta acción es irreversible y revocará su acceso al sistema. Si el empleado tiene citas o servicios activos, la operación será BLOQUEADA hasta que se cancelen o finalicen.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="btnConfirmarEliminar">
                        <i class="fa-solid fa-trash-can"></i> Confirmar Inactivar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  
    document.addEventListener('DOMContentLoaded', function () {
        const modalConfirmar = document.getElementById('modalConfirmarInactivar');
        modalConfirmar.addEventListener('show.bs.modal', function (event) {
            // Botón que disparó el modal
            const button = event.relatedTarget; 
            
            // Obtener datos del botón
            const id = button.getAttribute('data-id');
            const nombre = button.getAttribute('data-nombre');
            const correo = button.getAttribute('data-correo');

            // Actualizar el contenido del modal
            const modalIdEmpleado = modalConfirmar.querySelector('#modalIdEmpleado');
            const modalNombreEmpleado = modalConfirmar.querySelector('#modalNombreEmpleado');
            const modalCorreoEmpleado = modalConfirmar.querySelector('#modalCorreoEmpleado');

            modalIdEmpleado.value = id;
            modalNombreEmpleado.textContent = nombre;
            modalCorreoEmpleado.textContent = correo;
        });

    
        const filtroInput = document.getElementById('filtroTabla');
        const tabla = document.getElementById('tablaEmpleados');
        const filas = tabla ? tabla.getElementsByTagName('tbody')[0].getElementsByTagName('tr') : [];

        if (filtroInput) {
            filtroInput.addEventListener('keyup', function() {
                const filtro = filtroInput.value.toLowerCase();
                
                for (let i = 0; i < filas.length; i++) {
                    let contenidoFila = '';
                    const celdas = filas[i].getElementsByTagName('td');
                    
                    // Solo considerar las primeras 6 columnas para búsqueda
                    for (let j = 1; j < 6; j++) { 
                        if (celdas[j]) {
                            // Usar el atributo 'data-filtro' para campos formateados (Salario/Horario) si existe
                            let textoCelda = celdas[j].getAttribute('data-filtro') || celdas[j].textContent;
                            contenidoFila += textoCelda.toLowerCase() + ' ';
                        }
                    }
                    
                    // Mostrar u ocultar la fila
                    if (contenidoFila.indexOf(filtro) > -1) {
                        filas[i].style.display = '';
                    } else {
                        filas[i].style.display = 'none';
                    }
                }
            });
        }
    });
</script>