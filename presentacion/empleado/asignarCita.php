<?php

if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "empleado") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

$idEmpleado = $_SESSION["id"];


$solicitudes = Cita::consultarPendientesPorEmpleado($idEmpleado);
$citasActivasHoy = Cita::consultarActivasHoyPorEmpleado($idEmpleado);
$errorConsultaPendientes = isset($solicitudes['error']) ? $solicitudes['error'] : false;
$solicitudesTabla = $errorConsultaPendientes ? [] : $solicitudes;

$errorConsultaActivas = isset($citasActivasHoy['error']) ? $citasActivasHoy['error'] : false;
$citasActivasTabla = $errorConsultaActivas ? [] : $citasActivasHoy;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Citas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
</head>
<body>
<?php
include("presentacion/encabezadoE.php");
include("presentacion/menuEmpleado.php");
?>
<div class="container mt-4">
    
    <h2><i class="fa-solid fa-bell"></i> Solicitudes de Citas Pendientes</h2>
    <p class="text-muted">Acepte o rechace las nuevas solicitudes. Revise los comentarios del cliente.</p>
    <hr>
    
    <div class="table-responsive shadow-sm p-3 bg-white rounded mb-5" id="tabla-pendientes">
        <?php if ($errorConsultaPendientes) : ?>
            <div class="alert alert-danger text-center">
                <i class="fa-solid fa-triangle-exclamation"></i> Error al cargar solicitudes: <?php echo htmlspecialchars($errorConsultaPendientes); ?>
            </div>
        <?php elseif (empty($solicitudesTabla)) : ?>
            <div class="alert alert-info text-center" id="mensaje-pendientes">
                <i class="fa-solid fa-check-circle"></i> No hay solicitudes de citas pendientes en este momento.
            </div>
        <?php else : ?>
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Comentarios</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudesTabla as $solicitud) : ?>
                    <tr data-cita-id="<?php echo $solicitud['idCita']; ?>" 
                        data-fecha="<?php echo htmlspecialchars($solicitud['Fecha']); ?>"
                        data-inicio="<?php echo htmlspecialchars(substr($solicitud['HoraInicio'], 0, 5)); ?>"
                        data-fin="<?php echo htmlspecialchars(substr($solicitud['HoraFin'], 0, 5)); ?>"
                        data-servicio="<?php echo htmlspecialchars($solicitud['NombreServicio']); ?>"
                        data-cliente="<?php echo htmlspecialchars($solicitud['NombreCliente']); ?>"
                    >
                        <td><?php echo htmlspecialchars($solicitud['NombreCliente']); ?></td>
                        <td><?php echo htmlspecialchars($solicitud['NombreServicio']); ?></td>
                        <td class="cita-fecha"><?php echo htmlspecialchars($solicitud['Fecha']); ?></td>
                        <td>
                            <span class="cita-inicio"><?php echo htmlspecialchars(substr($solicitud['HoraInicio'], 0, 5)); ?></span> - 
                            <span class="cita-fin"><?php echo htmlspecialchars(substr($solicitud['HoraFin'], 0, 5)); ?></span>
                        </td>
                        <td>
                            <?php if (!empty($solicitud['comentarios'])): ?>
                                <button type="button" class="btn btn-info btn-sm" 
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="<?php echo htmlspecialchars($solicitud['comentarios']); ?>">
                                    <i class="fa-solid fa-comment"></i>
                                </button>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm btn-aceptar" data-id="<?php echo $solicitud['idCita']; ?>">
                                <i class="fa-solid fa-check"></i> Aceptar
                            </button>
                            <button class="btn btn-danger btn-sm btn-rechazar" data-id="<?php echo $solicitud['idCita']; ?>">
                                <i class="fa-solid fa-times"></i> Rechazar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <h2><i class="fa-solid fa-calendar-check"></i> Citas Activas de Hoy</h2>
    <p class="text-muted">Finalice o marque como "No Asistió" las citas programadas para hoy.</p>
    <hr>
    
    <div class="table-responsive shadow-sm p-3 bg-white rounded" id="tabla-activas">
        <?php if ($errorConsultaActivas) : ?>
            <div class="alert alert-danger text-center">
                <i class="fa-solid fa-triangle-exclamation"></i> Error al cargar citas activas: <?php echo htmlspecialchars($errorConsultaActivas); ?>
            </div>
        <?php elseif (empty($citasActivasTabla)) : ?>
            <div class="alert alert-info text-center" id="mensaje-activas">
                <i class="fa-solid fa-calendar-alt"></i> No hay citas activas para el día de hoy.
            </div>
        <?php else : ?>
            <table class="table table-hover align-middle" id="citas-activas-table">
                <thead class="table-dark">
                    <tr>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Horario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citasActivasTabla as $cita) : ?>
                    <tr data-cita-id="<?php echo $cita['idCita']; ?>">
                        <td><?php echo htmlspecialchars($cita['NombreCliente']); ?></td>
                        <td><?php echo htmlspecialchars($cita['NombreServicio']); ?></td>
                        <td><?php echo htmlspecialchars(substr($cita['HoraInicio'], 0, 5)); ?> - <?php echo htmlspecialchars(substr($cita['HoraFin'], 0, 5)); ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm btn-finalizar" data-id="<?php echo $cita['idCita']; ?>">
                                <i class="fa-solid fa-check-double"></i> Finalizada
                            </button>
                            <button class="btn btn-warning btn-sm btn-no-asistio" data-id="<?php echo $cita['idCita']; ?>">
                                <i class="fa-solid fa-user-slash"></i> No Asistió
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalRechazar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Rechazo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea rechazar esta solicitud de cita?</p>
                <small class="text-muted">La cita pasará a estado 'Cancelada'.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmarRechazoBtn">Rechazar</button>
            </div>
        </div>
    </div>
</div>

<div aria-live="polite" aria-atomic="true" style="position: fixed; top: 10px; right: 10px;">
    <div class="toast-container">
        </div>
</div>

<script>
$(document).ready(function() {
    var idCitaGlobal = null;
    const idEmpleado = <?php echo $idEmpleado; ?>;

    // --- FUNCIÓN PARA MOSTRAR MENSAJES  ---
    function showToast(message, type = 'success') {
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';
        const color = type === 'success' ? 'bg-success' : 'bg-danger';
        const toastHtml = `
            <div class="toast align-items-center text-white ${color}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fa-solid ${icon}"></i> ${message}
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        $('.toast-container').append(toastHtml);
        const toastEl = $('.toast-container .toast').last();
        
        const bsToast = new bootstrap.Toast(toastEl[0]);
        bsToast.show();
    }
    
    
    function handleResponse(response, $row, action) {
        if (response.success) {
            showToast(response.message, 'success');
            
            // 1. GESTIÓN DE LA TABLA PENDIENTES (Aceptar/Rechazar)
            if (action === 'aceptar' || action === 'rechazar') {
                $row.remove();
                
                // Muestra el mensaje si la tabla queda vacía
                if ($('#tabla-pendientes tbody tr').length === 0) {
                    $('#tabla-pendientes').html('<div class="alert alert-info text-center" id="mensaje-pendientes"><i class="fa-solid fa-check-circle"></i> No hay solicitudes de citas pendientes en este momento.</div>');
                }
            }
            
            // 2. AÑADIR A LA TABLA ACTIVAS SI FUE ACEPTADA
            if (action === 'aceptar') {
                const cliente = $row.data('cliente');
                const servicio = $row.data('servicio');
                const horario = `${$row.data('inicio')} - ${$row.data('fin')}`;
                
                // Si existe el mensaje de 'No hay citas activas', lo elimina y crea la tabla
                if ($('#mensaje-activas').length) {
                     $('#tabla-activas').html(`
                        <table class="table table-hover align-middle" id="citas-activas-table">
                            <thead class="table-dark">
                                <tr><th>Cliente</th><th>Servicio</th><th>Horario</th><th>Acciones</th></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    `);
                }
                
                // Añade la nueva fila a la tabla de activas
                const newRow = `
                    <tr data-cita-id="${idCitaGlobal}">
                        <td>${cliente}</td>
                        <td>${servicio}</td>
                        <td>${horario}</td>
                        <td>
                            <button class="btn btn-primary btn-sm btn-finalizar" data-id="${idCitaGlobal}">
                                <i class="fa-solid fa-check-double"></i> Finalizada
                            </button>
                            <button class="btn btn-warning btn-sm btn-no-asistio" data-id="${idCitaGlobal}">
                                <i class="fa-solid fa-user-slash"></i> No Asistió
                            </button>
                        </td>
                    </tr>
                `;
                $('#citas-activas-table tbody').append(newRow);
                
                // Rebindear eventos a los nuevos botones
                bindActiveTableEvents();
            }
            
            // 3. ELIMINAR DE LA TABLA ACTIVAS (Finalizada/No Asistió)
            if (action === 'finalizar' || action === 'noAsistio') {
                $row.remove();
                
                // Muestra el mensaje si la tabla queda vacía
                if ($('#citas-activas-table tbody tr').length === 0) {
                     $('#tabla-activas').html('<div class="alert alert-info text-center" id="mensaje-activas"><i class="fa-solid fa-calendar-alt"></i> No hay citas activas para el día de hoy.</div>');
                }
            }
            
        } else {
            showToast("Error: " + response.error, 'danger');
        }
    }
    
    // --- FUNCIÓN PARA RE-ASIGNAR EVENTOS A BOTONES ACTIVOS ---
    function bindActiveTableEvents() {
        // Desactivar y luego reactivar para evitar múltiples bindings
        $('#citas-activas-table').off('click', '.btn-finalizar');
        $('#citas-activas-table').off('click', '.btn-no-asistio');

        // Evento Finalizar (DIRECTO)
        $('#citas-activas-table').on('click', '.btn-finalizar', function() {
            const $row = $(this).closest('tr');
            idCitaGlobal = $(this).data('id');
            // Acción directa, sin confirmación.
            processFollowUpAction('finalizar', $row);
        });
        
        // Evento No Asistió (DIRECTO)
        $('#citas-activas-table').on('click', '.btn-no-asistio', function() {
            const $row = $(this).closest('tr');
            idCitaGlobal = $(this).data('id');
            // Acción directa, sin confirmación.
            processFollowUpAction('noAsistio', $row);
        });
    }

   
    function processFollowUpAction(action, $row) {
        // Se lanza un Toast para indicar que se está procesando (feedback visual)
        showToast(`Procesando acción '${action}'...`, 'info');

        $.ajax({
            url: 'presentacion/empleado/ajax/gestionarSolicitud.php', // Ruta al controlador AJAX
            method: 'POST',
            dataType: 'json',
            data: { action: action, idCita: idCitaGlobal },
            success: function(response) {
                handleResponse(response, $row, action);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error, xhr.responseText); 
                showToast('Error de conexión. No se pudo procesar la acción de seguimiento.', 'danger');
            }
        });
    }

    // --- ACCIÓN ACEPTAR (Tabla Pendientes) - Directa ---
    $('.btn-aceptar').on('click', function() {
        const $row = $(this).closest('tr');
        idCitaGlobal = $(this).data('id');
        
        const fecha = $row.data('fecha');
        const horaInicio = $row.data('inicio') + ':00';
        const horaFin = $row.data('fin') + ':00';

        // Lanza un Toast para indicar que se está procesando (feedback)
        showToast(`Procesando aceptación de cita para el ${fecha}...`, 'info');

        $.ajax({
            url: 'presentacion/empleado/ajax/gestionarSolicitud.php', // Ruta al controlador AJAX
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'aceptar',
                idCita: idCitaGlobal,
                idEmpleado: idEmpleado,
                fecha: fecha,
                horaInicio: horaInicio,
                horaFin: horaFin
            },
            success: function(response) {
                handleResponse(response, $row, 'aceptar');
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error, xhr.responseText); 
                showToast('Error de conexión o de servidor. No se pudo aceptar la cita.', 'danger');
            }
        });
    });

    // --- ACCIÓN RECHAZAR (Tabla Pendientes) - Usa Modal ---
    $('.btn-rechazar').on('click', function() {
        idCitaGlobal = $(this).data('id');
        // Se mantiene el modal para la confirmación de rechazo (acción destructiva).
        $('#modalRechazar').modal('show');
    });

    $('#confirmarRechazoBtn').on('click', function() {
        $('#modalRechazar').modal('hide');
        const $row = $('tr[data-cita-id="' + idCitaGlobal + '"]');
        
        $.ajax({
            url: 'presentacion/empleado/ajax/gestionarSolicitud.php', // Ruta al controlador AJAX
            method: 'POST',
            dataType: 'json',
            data: { action: 'rechazar', idCita: idCitaGlobal },
            success: function(response) {
                handleResponse(response, $row, 'rechazar');
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error, xhr.responseText); 
                showToast('Error de conexión. No se pudo rechazar la cita.', 'danger');
            }
        });
    });
    
    
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    bindActiveTableEvents();
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>