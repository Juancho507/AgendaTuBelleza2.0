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
        
        if ($accion === 'aceptar') {
            $salario = isset($_POST['salario']) ? trim($_POST['salario']) : '';
            $horario = isset($_POST['horario']) ? trim($_POST['horario']) : '';
            
            if ($salario === '' || !is_numeric($salario)) {
                $mensaje = "<div class='alert alert-warning'>Ingrese un salario numérico válido para poder aceptar el empleado.</div>";
            } else {
                $empleado = new Empleado($idEmpleado);
                $ok = $empleado->aprobar($salario, $horario, $idGerente);
                
                if ($ok) {
                    $mensaje = "<div class='alert alert-success'>Empleado aprobado y activado correctamente.</div>";
                } else {
                    $mensaje = "<div class='alert alert-danger'>No se pudo aprobar el empleado. Es posible que ya haya sido procesado o hubo un error.</div>";
                }
            }
        }
        
        if ($accion === 'observar') {
            $mensaje = "<div class='alert alert-info'>Solicitud no aceptada. Se mantiene inactiva.</div>";
        }
        if ($accion === 'inactivar') {
            $empleado = new Empleado($idEmpleado);
            $ok = $empleado->inactivar();
            if ($ok) {
                $mensaje = "<div class='alert alert-warning'>Empleado inactivado correctamente.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger'>No se pudo inactivar el empleado.</div>";
            }
        }
        if ($accion === 'reactivar') {
            $empleado = new Empleado($idEmpleado);
            $ok = $empleado->reactivar();
            if ($ok) {
                $mensaje = "<div class='alert alert-success'>Empleado reactivado correctamente.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger'>No se pudo reactivar el empleado.</div>";
            }
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
    <h2 class="text-danger mb-3"><i class="fa-solid fa-user-check"></i> Gestión de Solicitudes de Empleados</h2>

    <?php echo $mensaje; ?>
    <div class="card mb-4">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <span>Solicitudes Pendientes</span>
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

                                            <button type="submit" name="accion" value="inactivar" class="btn btn-warning">
                                                <i class="fa-solid fa-user-slash"></i> Rechazar / No aceptar
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
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
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
                                    <td><?php echo $a['Salario'] ? number_format($a['Salario'],0,',','.') : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($a['Horario']); ?></td>
                                    <td>
                                        <?php if ($a['Estado'] == 1): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="idEmpleado" value="<?php echo $a['idEmpleado']; ?>">
                                                <button type="submit" name="accion" value="inactivar" class="btn btn-danger btn-sm">
                                                    <i class="fa-solid fa-user-slash"></i> Inactivar
                                                </button>
                                            </form>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
