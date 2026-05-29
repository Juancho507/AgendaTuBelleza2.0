<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);

$basePath = dirname(__DIR__, 2);

function requireAppFile($relativePath) {
    global $basePath;
    require_once($basePath . '/' . $relativePath);
}

$ESTADO_AGENDADA = 1;     
$ESTADO_REPROGRAMADA = 2; 
$ESTADO_FINALIZADA = 3;  
$ESTADO_CANCELADA = 4;    
$ESTADO_NO_ASISTIO = 5;   
$ESTADO_PENDIENTE = 6;    
$ESTADO_LIBRE = 7;        

if (isset($_POST['action']) && $_POST['action'] === 'cancelar') {
    
    if (session_status() == PHP_SESSION_NONE || session_status() == PHP_SESSION_DISABLED) {
        session_start();
    }
    
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    
    $citaId = $_POST['citaId'] ?? null;
    $clienteIdSesion = $_SESSION["id"] ?? null;
    
    requireAppFile("logica/Cita.php");
    requireAppFile("persistencia/Conexion.php");
    requireAppFile("persistencia/CitaDAO.php");
    requireAppFile("persistencia/AgendaDAO.php");
    
    if (!$citaId || !$clienteIdSesion) {
        $response = [
            'success' => false,
            'error' => "Datos o sesión incompletos para la cancelación.
                        (ID Cliente: " . (empty($clienteIdSesion) ? 'NULL' : $clienteIdSesion) . ",
                        ID Cita: " . (empty($citaId) ? 'NULL' : $citaId) . ")"
        ];
    } else {
        $response = Cita::cancelarCita($citaId, $clienteIdSesion, $ESTADO_CANCELADA, $ESTADO_LIBRE);
    }
    
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}


if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

$idCliente = $_SESSION["id"];

requireAppFile("logica/Cita.php");
requireAppFile("persistencia/Conexion.php");
requireAppFile("persistencia/CitaDAO.php");

$citas = Cita::obtenerHistorialCitas($idCliente);
?>

<?php include("presentacion/encabezadoC.php");  ?>
<?php include("presentacion/menuCliente.php"); ?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-danger text-white">
            <h4 class="mb-0"><i class="fa-solid fa-calendar-xmark"></i> Historial y Cancelación de Citas</h4>
        </div>
        <div class="card-body">
            
            <div id="alertMensajes" class="alert d-none mt-3" role="alert"></div>
            
            <h5 class="mt-4 mb-3 text-primary">Mis Citas Registradas:</h5>
            
            <?php if (empty($citas)): ?>
                <div class="alert alert-info text-center">
                    Aún no tienes citas registradas en el sistema.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Servicio</th>
                                <th>Empleado</th>
                                <th>Fecha/Hora</th>
                                <th>Estado</th>
                                <th>Comentarios</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ((array)$citas as $cita): 
                                $estadoId = (int)$cita['estadoId'];
                                $esCancelable = $estadoId === $ESTADO_AGENDADA || $estadoId === $ESTADO_REPROGRAMADA;
                                
                                
                                $claseFila = '';
                                $claseBadge = 'bg-secondary';
                                
                                switch ($estadoId) {
                                    case $ESTADO_AGENDADA:
                                        $claseBadge = 'bg-success'; 
                                        break;
                                    case $ESTADO_REPROGRAMADA:
                                        $claseBadge = 'bg-primary'; 
                                        break;
                                    case $ESTADO_FINALIZADA:
                                        $claseBadge = 'bg-info';    
                                        break;
                                    case $ESTADO_CANCELADA:
                                        $claseBadge = 'bg-dark';    
                                        $claseFila = 'table-secondary'; 
                                        break;
                                    case $ESTADO_NO_ASISTIO:
                                        $claseBadge = 'bg-danger';  
                                        $claseFila = 'table-danger'; 
                                        break;
                                    case $ESTADO_PENDIENTE:
                                        $claseBadge = 'bg-warning text-dark'; 
                                        break;
                                    default:
                                        $claseBadge = 'bg-secondary';
                                        break;
                                }
                               
                                $fechaHoraCita = $cita['fecha'] . ' ' . $cita['hora_inicio'];
                                $timestampCita = strtotime($fechaHoraCita);
                                $timestampLimite = $timestampCita - (12 * 3600);
                                $fueraDeTiempo = time() > $timestampLimite;
                                
                                $claseBoton = '';
                                if ($esCancelable && $fueraDeTiempo) {
                                    $claseBoton = 'btn-secondary btn-sm disabled';
                                } else if ($esCancelable) {
                                    $claseBoton = 'btn-danger btn-sm eliminar-cita';
                                } else {
                                    $claseBoton = 'btn-light btn-sm disabled';
                                }
                            ?>
                            <tr class="<?php echo $claseFila; ?>">
                                <td><?php echo htmlspecialchars($cita['NombreServicio']); ?></td>
                                <td><?php echo htmlspecialchars($cita['NombreEmpleado']); ?></td>
                                <td><?php echo date('d/m/Y', $timestampCita) . ' ' . date('H:i', $timestampCita); ?></td>
                                <td>
                                    <span class="badge <?php echo $claseBadge; ?>">
                                        <?php echo htmlspecialchars($cita['EstadoNombre']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($cita['comentarios']); ?></td>
                                <td>
                                    <?php if ($esCancelable): ?>
                                        <button class="<?php echo $claseBoton; ?>" 
                                            data-id="<?php echo $cita['id']; ?>"
                                            data-servicio="<?php echo htmlspecialchars($cita['NombreServicio']); ?>"
                                            data-empleado="<?php echo htmlspecialchars($cita['NombreEmpleado']); ?>"
                                            data-fecha-hora="<?php echo date('d/m/Y H:i', $timestampCita); ?>"
                                            <?php echo $fueraDeTiempo ? 'data-fuera-tiempo="true"' : 'data-fuera-tiempo="false"'; ?>
                                            >
                                            <i class="fa-solid fa-xmark"></i> Eliminar Cita
                                        </button>
                                        <?php if ($fueraDeTiempo && $esCancelable): ?>
                                            <small class="text-danger d-block">❌ Tarde para cancelar</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn btn-light btn-sm disabled"><i class="fa-solid fa-info-circle"></i> No aplica</button>
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

<div class="modal fade" id="modalConfirmacionCancelacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmación de Anulación de Cita</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-danger fw-bold">⚠️ ADVERTENCIA: Esta acción eliminará permanentemente la cita del sistema y liberará el horario del empleado.</p>
                
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item"><strong>Servicio:</strong> <span id="modalCancelServicio"></span></li>
                    <li class="list-group-item"><strong>Empleado Asignado:</strong> <span id="modalCancelEmpleado"></span></li>
                    <li class="list-group-item"><strong>Fecha y Hora:</strong> <span id="modalCancelFechaHora"></span></li>
                </ul>
                
                <div id="modalCancelAdvertencia" class="alert alert-warning d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarCancelacion">❌ Confirmar Eliminación</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let citaAAnular = {};
    const $alert = $('#alertMensajes');
    
    function showAlert(message, type) {
        $alert.removeClass().addClass(`alert alert-${type}`).html(`<i class="fa-solid fa-circle-info"></i> ${message}`).slideDown();
        setTimeout(() => $alert.slideUp(), 6000);
    }

    // Manejo del Click en "Eliminar Cita"
    $('.eliminar-cita').on('click', function() {
        citaAAnular = {
            id: $(this).data('id'),
            servicio: $(this).data('servicio'),
            empleado: $(this).data('empleado'),
            fechaHora: $(this).data('fecha-hora'),
            fueraTiempo: $(this).data('fuera-tiempo') 
        };
        
        $('#modalCancelServicio').text(citaAAnular.servicio);
        $('#modalCancelEmpleado').text(citaAAnular.empleado);
        $('#modalCancelFechaHora').text(citaAAnular.fechaHora);
        $('#modalCancelAdvertencia').addClass('d-none').empty(); 

        if (citaAAnular.fueraTiempo) {
            const mensaje = "No es posible cancelar la cita con menos de 12 horas de anticipación. Comuníquese con el establecimiento.";
            $('#modalCancelAdvertencia').html(mensaje).removeClass('d-none');
            $('#btnConfirmarCancelacion').prop('disabled', true);
        } else {
            $('#btnConfirmarCancelacion').prop('disabled', false);
        }

        $('#modalConfirmacionCancelacion').modal('show');
    });

    //  Manejo de la Confirmación (Envío AJAX)
    $('#btnConfirmarCancelacion').on('click', function() {
        $('#modalConfirmacionCancelacion').modal('hide');
        const $btn = $(this);
        $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...').prop('disabled', true);

        $.ajax({
           url: 'presentacion/cita/cancelarCita.php', 
            type: 'POST',
            data: {
                action: 'cancelar',
                citaId: citaAAnular.id
            },
            dataType: 'json', 
            success: function(response) {
                $btn.html('❌ Confirmar Eliminación').prop('disabled', false); // Restaurar botón
                
                if (response.success) {
                    showAlert(`✅ ${response.message}`, 'success'); 
                    
                    //  Actualizar la vista (Fila)
                    const $filaBoton = $(`button[data-id="${citaAAnular.id}"]`);
                    const $row = $filaBoton.closest('tr');
                    
                    
                    $row.removeClass().addClass('table-secondary');
                    $row.find('.badge').removeClass().addClass('badge bg-dark').text('Cancelada');
                    
                    // Deshabilitar y reemplazar el botón
                    $filaBoton.replaceWith('<button class="btn btn-light btn-sm disabled"><i class="fa-solid fa-ban"></i> Cancelada</button>');
                    
                } else {
                    showAlert('❌ Error de cancelación: ' + response.error, 'danger'); 
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $btn.html('❌ Confirmar Eliminación').prop('disabled', false); // Restaurar botón
                
                let errorMsg = 'Error de conexión o servidor.';
                try {
                    const serverResponse = jqXHR.responseText;
                    if (serverResponse && serverResponse.length < 500) { 
                        errorMsg += ' Respuesta del servidor (verifique rutas): ' + serverResponse;
                    }
                } catch (e) {
                    
                }
                
                console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                showAlert(`⚠️ ${errorMsg} Revise F12 > Network (Red).`, 'warning');
            }
        });
    });
});
</script>