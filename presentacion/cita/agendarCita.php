<?php
if (!isset($_SESSION)) {
    session_start();
}

$basePath = dirname(__DIR__, 2);

function requireAppFile($relativePath) {
    global $basePath;
    require_once($basePath . '/' . $relativePath);
}
if (
    isset($_POST['action']) &&
    $_POST['action'] === 'agendar'
    ) {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); 
        
        $clienteId = $_POST['clienteId'] ?? null;
        $agendaId = $_POST['agendaId'] ?? null;
        $comentarios = $_POST['comentarios'] ?? '';
 
        requireAppFile("logica/Cita.php");
        requireAppFile("persistencia/Conexion.php");
        requireAppFile("persistencia/CitaDAO.php");
        requireAppFile("persistencia/AgendaDAO.php");
        requireAppFile("persistencia/ClienteDAO.php");
        
        $response = ['success' => false, 'error' => 'Error de procesamiento inicial.'];
        
        if (!isset($_SESSION["id"]) || $_SESSION["id"] != $clienteId) {
            $response = ['success' => false, 'error' => 'Error de seguridad: El ID del cliente no coincide con la sesión.'];
        } else if (!$agendaId || !$clienteId) {
            $response = ['success' => false, 'error' => 'Datos de reserva incompletos.'];
        } else {
            
            $response = Cita::agendarNuevaCita($agendaId, $clienteId, $comentarios);
        }
        
        http_response_code(200); 
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
    
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

requireAppFile("logica/Cliente.php");
requireAppFile("logica/Agenda.php");
requireAppFile("persistencia/Conexion.php");
requireAppFile("persistencia/ClienteDAO.php");
requireAppFile("persistencia/AgendaDAO.php");

$idCliente = $_SESSION["id"];
$cliente = new Cliente($idCliente);

if (!$cliente->consultar()) {
  
    $nombreCliente = $cliente->getNombre() . " " . $cliente->getApellido();
}


$resultado = Agenda::obtenerFranjasDisponibles();
$franjas = $resultado['data'] ?? [];
$errorCarga = $resultado['error'] ?? '';

$clienteSesion = [
    'id' => $idCliente,
    'nombreCompleto' => $nombreCliente
];
$ESTADO_LIBRE = 7;
$ESTADO_PENDIENTE = 6;
$ESTADO_ACTIVA = 1;
?>

<?php include("presentacion/encabezadoC.php");  ?>
<?php include("presentacion/menuCliente.php"); ?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fa-regular fa-calendar-check"></i> Agendar Nueva Cita</h4>
        </div>
        <div class="card-body">
            
            <p class="lead">Bienvenido, <?php echo htmlspecialchars($clienteSesion['nombreCompleto']); ?>. Selecciona la franja horaria que mejor se ajuste a tus necesidades.</p>
            
            <div id="alertMensajes" class="alert d-none mt-3" role="alert"></div>
            
            <?php if (!empty($errorCarga)): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fa-solid fa-triangle-exclamation"></i> Error de conexión o servidor. Por favor, inténtalo de nuevo.
                    <?php if ($clienteSesion['nombreCompleto'] === "Cliente Desconocido"): ?>
                        <small class="d-block mt-1">⚠️ Error en la carga de la vista. Revisa la ruta de conexión a la Base de Datos.</small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <h5 class="mt-4 mb-3 text-primary">Disponibilidad de Empleados:</h5>
            <div id="calendarioDisponibilidad" class="list-group">
                <?php if (empty($franjas)): ?>
                    <div class="alert alert-warning text-center">
                        No se encontraron franjas horarias disponibles para agendar en el futuro.
                    </div>
                <?php else: ?>
                    <?php 
                    $fechaActual = '';
                    foreach ($franjas as $franja): 
                        
                        if ($franja['Fecha'] !== $fechaActual) {
                            $fechaActual = $franja['Fecha'];
                            echo '<h6 class="mt-4 mb-2 text-info">📅 ' . date('l, d F Y', strtotime($fechaActual)) . '</h6>';
                        }

                        $estadoId = (int)$franja['EstadoId'];
                        $claseColor = '';
                        $textoDisponibilidad = 'Ocupada/Bloqueada';
                        $esSeleccionable = false;
                        
                        if ($estadoId === $ESTADO_LIBRE) { 
                            $claseColor = 'list-group-item-success seleccionar-franja'; 
                            $textoDisponibilidad = '¡Libre!';
                            $esSeleccionable = true;
                        } elseif ($estadoId === $ESTADO_PENDIENTE) { 
                            $claseColor = 'list-group-item-warning'; 
                            $textoDisponibilidad = 'Pendiente de Aprobación';
                            $esSeleccionable = false;
                        } elseif ($estadoId === $ESTADO_ACTIVA) {
                            $claseColor = 'list-group-item-danger'; 
                            $textoDisponibilidad = 'Reservada y Confirmada';
                            $esSeleccionable = false;
                        } else {
                            
                            $claseColor = 'list-group-item-secondary'; 
                            $textoDisponibilidad = 'No Disponible';
                            $esSeleccionable = false;
                        }
                        
                        $dataAttr = "data-id='{$franja['idAgenda']}' "
                                  . "data-empleado='{$franja['NombreEmpleado']}' "
                                  . "data-servicio='{$franja['NombreServicio']}' "
                                  . "data-fecha='" . date('d/m/Y', strtotime($franja['Fecha'])) . "' "
                                  . "data-hora='{$franja['HoraInicio']}' "
                                  . "data-duracion='{$franja['DuracionMinutos']}'";
                    ?>
                        <a href="#" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $claseColor; ?>"
                           <?php echo $dataAttr; ?>
                           style="<?php echo $esSeleccionable ? 'cursor: pointer;' : 'pointer-events: none; opacity: 0.7;'; ?>">
                            <div>
                                🧑‍💻 <strong><?php echo htmlspecialchars($franja['NombreEmpleado']); ?></strong> 
                                <span class="badge bg-primary"><?php echo htmlspecialchars($franja['NombreServicio']); ?></span><br>
                                <small class="text-muted">
                                    ⏰ <?php echo date('H:i', strtotime($franja['HoraInicio'])) . ' - ' . date('H:i', strtotime($franja['HoraFin'])); ?>
                                    (Duración: <?php echo $franja['DuracionMinutos']; ?> min)
                                </small>
                            </div>
                            <span class="badge bg-dark"><?php echo $textoDisponibilidad; ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmacionCita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Confirmación de Solicitud de Cita</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Por favor, revisa el resumen de tu cita y confirma tu solicitud. Recuerda que esta cita queda en estado Pendiente hasta que el empleado la apruebe.</p>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item"><strong>Cliente:</strong> <?php echo htmlspecialchars($clienteSesion['nombreCompleto']); ?></li>
                    <li class="list-group-item"><strong>Empleado Asignado:</strong> <span id="modalEmpleado"></span></li>
                    <li class="list-group-item"><strong>Servicio:</strong> <span id="modalServicio"></span></li>
                    <li class="list-group-item"><strong>Fecha y Hora:</strong> <span id="modalFechaHora"></span></li>
                    <li class="list-group-item"><strong>Duración Estimada:</strong> <span id="modalDuracion"></span> minutos</li>
                </ul>
                
                <div class="mb-3">
                    <label for="inputComentarios" class="form-label">Comentarios Adicionales (Opcional):</label>
                    <textarea class="form-control" id="inputComentarios" rows="3" placeholder="Ej: Indicaciones, preferencias del servicio, notas especiales."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarCita">✅ Solicitar Cita</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let franjaSeleccionada = {};
    const clienteId = <?php echo $clienteSesion['id']; ?>;
    const $alert = $('#alertMensajes');
    const ESTADO_PENDIENTE = <?php echo $ESTADO_PENDIENTE; ?>; // 6
    
    function showAlert(message, type) {
        // Aseguramos que el mensaje de error de JSON se elimine al mostrar un nuevo alert
        $('#php-fatal-error-display').remove();
        $alert.removeClass().addClass(`alert alert-${type}`).html(`<i class="fa-solid fa-circle-info"></i> ${message}`).slideDown();
        setTimeout(() => $alert.slideUp(), 6000);
    }
    
    // Manejo de Selección de Franja
    $('.seleccionar-franja').on('click', function(e) {
        e.preventDefault();
        
        franjaSeleccionada = {
            idAgenda: $(this).data('id'),
            empleado: $(this).data('empleado'),
            servicio: $(this).data('servicio'),
            fecha: $(this).data('fecha'),
            hora: $(this).data('hora'),
            duracion: $(this).data('duracion')
        };
        
        $('#modalEmpleado').text(franjaSeleccionada.empleado);
        $('#modalServicio').text(franjaSeleccionada.servicio);
        $('#modalFechaHora').text(franjaSeleccionada.fecha + ' a las ' + franjaSeleccionada.hora);
        $('#modalDuracion').text(franjaSeleccionada.duracion);
        
        $('#modalConfirmacionCita').modal('show');
    });

    // Manejo de la Confirmación (Envío AJAX)
    $('#btnConfirmarCita').on('click', function() {
        const comentarios = $('#inputComentarios').val();
        
        $('#modalConfirmacionCita').modal('hide');

        $.ajax({
           
           url: '/AgendaTuBelleza/presentacion/cita/agendarCita.php', 
            type: 'POST',
            data: {
                action: 'agendar',
                agendaId: franjaSeleccionada.idAgenda,
                clienteId: clienteId,
                comentarios: comentarios
            },
            success: function(data, textStatus, jqXHR) {
                
                let responseText = data;
                if (typeof data !== 'string' && jqXHR && jqXHR.responseText) {
                    responseText = jqXHR.responseText;
                }

                try {
                    const response = JSON.parse(responseText);
                    
                    if (response.success) {
                        // 🟢 MENSAJE DE ÉXITO FINAL
                        showAlert(`🎉 Cita Solicitada con Éxito. La franja seleccionada espera la confirmación del empleado.`, 'success'); 
                        
                        // Actualizar la apariencia de la franja a Pendiente
                        const $franjaElement = $(`[data-id="${franjaSeleccionada.idAgenda}"]`);
                        $franjaElement
                            .removeClass('list-group-item-success seleccionar-franja')
                            .addClass('list-group-item-warning')
                            .css({'pointer-events': 'none', 'opacity': '0.7'})
                            .find('.badge').text('Pendiente de Aprobación');
                        
                    } else {
                        // Si falla la lógica (ej: "tomada por otro cliente")
                        showAlert('❌ Error al agendar: ' + response.error, 'danger'); 
                    }
                    
                } catch (e) {
                    
                    // Si falla el JSON.parse, es probable que haya output de PHP no esperado (warnings/espacios)
                    let errorContent = "No se pudo leer la respuesta del servidor o es una respuesta vacía.";
                    if (typeof responseText === 'string') {
                        errorContent = responseText.substring(0, 500);
                    } else if (jqXHR.status !== 200) {
                        errorContent = `Error HTTP ${jqXHR.status}: ${jqXHR.statusText}`;
                    }

                    showAlert(`⚠️ ERROR CRÍTICO: El servidor envió datos inválidos.`, 'danger');
                    
                    const errorModalHtml = `
                        <div id="php-fatal-error-display" class="alert alert-danger mt-3" style="white-space: pre-wrap; word-wrap: break-word; overflow-x: scroll; max-height: 200px;">
                            <strong>Respuesta del Servidor (Error PHP):</strong>
                            ${errorContent}
                            <hr>
                            <strong>ACCIÓN:</strong> Verifica que no haya output de texto antes de la respuesta JSON.
                        </div>`;
                    
                    $('#alertMensajes').after(errorModalHtml); 
                    
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                showAlert('⚠️ Error de conexión o servidor. El servidor no respondió con un código 200 OK.', 'warning');
            }
        });
    });
});
</script>