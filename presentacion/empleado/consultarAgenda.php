<?php

if (!isset($_SESSION)) {
    session_start();
}
if ($_SESSION["rol"] != "empleado") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

$idEmpleado = $_SESSION["id"];


$empleado = new Empleado($idEmpleado);
$empleado->consultar();
$nombreEmpleado = htmlspecialchars($empleado->getNombre() . ' ' . $empleado->getApellido());

$horarioEmpleado = Agenda::consultarHorarioEmpleado($idEmpleado);

$servicios = Servicio::consultarServiciosPorEmpleado($idEmpleado);
$estados = EstadoCita::consultarTodos();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agenda Personal - Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css' />
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales-all.global.min.js'></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
</head>
<body>
<?php
include("presentacion/encabezadoE.php");
include("presentacion/menuEmpleado.php");
?>
<div class="container mt-4">
    <h2><i class="fa-solid fa-calendar-alt"></i> Agenda: <?php echo $nombreEmpleado; ?></h2>
    <p class="text-muted">Horario: <?php echo htmlspecialchars($horarioEmpleado ?? 'No definido'); ?>.</p>
    <hr>
    
    <p>
        <button class="btn btn-info btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReporte" aria-expanded="false" aria-controls="collapseReporte">
            <i class="fa-solid fa-file-pdf"></i> Generar Reporte de Citas
        </button>
    </p>
    <div class="collapse mb-4" id="collapseReporte">
        <div class="card card-body bg-light">
            <h5>Filtros para Reporte PDF</h5>
            <form action="presentacion/empleado/pdfagenda.php" method="GET" target="_blank" id="formReporte">
                <input type="hidden" name="idEmpleado" value="<?php echo $idEmpleado; ?>">

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="fechaInicio" class="form-label small">Fecha Inicio:</label>
                        <input type="date" class="form-control form-control-sm" id="fechaInicio" name="fechaInicio" required>
                    </div>
                    <div class="col-md-3">
                        <label for="fechaFin" class="form-label small">Fecha Fin:</label>
                        <input type="date" class="form-control form-control-sm" id="fechaFin" name="fechaFin" required>
                    </div>
                    <div class="col-md-3">
                        <label for="servicioId" class="form-label small">Servicio:</label>
                        <select class="form-select form-select-sm" id="servicioId" name="servicioId">
                            <option value="">-- Todos --</option>
                            <?php foreach ($servicios as $servicio) {
                                echo "<option value='{$servicio['id']}'>" . htmlspecialchars($servicio['nombre']) . "</option>";
                            } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="estadoId" class="form-label small">Estado de Cita:</label>
                        <select class="form-select form-select-sm" id="estadoId" name="estadoId">
                            <option value="">-- Todos --</option>
                            <?php 
                            foreach ($estados as $estado) {
                                $estadoNombre = $estado['tipo'] ?? $estado['nombre'] ?? 'Desconocido';
                                echo "<option value='{$estado['id']}'>" . htmlspecialchars($estadoNombre) . "</option>";
                            } ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-file-arrow-down"></i> Generar PDF</button>
            </form>
        </div>
    </div>
    <p class="text-muted small">Haga clic y arrastre en el calendario para añadir disponibilidad.</p>
    <div id='calendar' class="shadow-lg p-3 bg-white rounded"></div>

</div>

<div class="modal fade" id="modalCrearFranja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa-solid fa-calendar-plus"></i> Añadir Disponibilidad</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCrearFranja">
                <div class="modal-body">
                    <input type="hidden" id="franjaFecha" name="fecha"> 
                    
                    <p><strong>Fecha Seleccionada:</strong> <span id="displayFecha"></span></p>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="inputHoraInicio" class="form-label">Hora de Inicio:</label>
                            <input type="time" class="form-control" id="inputHoraInicio" name="horaInicio" required min="08:00" max="19:00">
                        </div>
                        <div class="col-6">
                            <label for="inputHoraFin" class="form-label">Hora de Fin:</label>
                            <input type="time" class="form-control" id="inputHoraFin" name="horaFin" required min="08:00" max="20:00">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="selectServicio" class="form-label">Servicio a Ofrecer:</label>
                        <select class="form-select" id="selectServicio" name="servicioId" required>
                            <option value="">Seleccione un servicio...</option>
                            <?php foreach ($servicios as $servicio) {
                                echo "<option value='{$servicio['id']}'>" . htmlspecialchars($servicio['nombre']) . "</option>";
                            } ?>
                        </select>
                    </div>
                    <small class="text-danger">Asegúrese de que la hora de inicio sea anterior a la hora de fin.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Registrar Franja</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleCita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fa-solid fa-info-circle"></i> Detalle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <strong class="d-block mb-2"><span id="detalleFechaHora"></span></strong>
                <p><strong>Servicio:</strong> <span id="detalleServicio"></span></p>
                <p><strong>Cliente:</strong> <span id="detalleCliente"></span></p>
                <p><strong>Estado:</strong> <span id="detalleEstado" class="badge"></span></p>
                <p class="mt-2 text-wrap small">Obs: <span id="detalleObservaciones" class="text-muted"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var idEmpleado = <?php echo $idEmpleado; ?>;

    //ESTADOS Y COLORES 
    function getColorByEstado(estadoId) {
        switch (parseInt(estadoId)) {
            case 1: return '#198754';  // Activa (Verde)
            case 2: return '#0d6efd';  // En Curso (Azul)
            case 3: return '#6c757d';  // Finalizada (Gris Oscuro)
            case 4: return '#dc3545';  // Cancelada (Rojo)
            case 5: return '#ffc107';  // No Asistió (Amarillo)
            case 6: return '#6f42c1';  // Pendiente (Púrpura)
            default: return '#adb5bd'; // Libre / Disponibilidad (Gris Claro)
        }
    }

    function getBadgeClass(estadoId) {
        switch (parseInt(estadoId)) {
            case 1: return 'bg-success';      // Activa
            case 2: return 'bg-primary';      // En Curso
            case 3: return 'bg-secondary';    // Finalizada
            case 4: return 'bg-danger';       // Cancelada
            case 5: return 'bg-warning text-dark'; // No Asistió
            case 6: return 'bg-info text-dark';   // Pendiente
            default: return 'bg-light text-dark'; // Libre
        }
    }

    function getEstadoNombre(estadoId) {
        switch (parseInt(estadoId)) {
            case 1: return 'Activa';
            case 2: return 'En Curso';
            case 3: return 'Finalizada';
            case 4: return 'Cancelada';
            case 5: return 'No Asistió';
            case 6: return 'Pendiente';
            default: return 'Libre';
        }
    }
    
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek', 
        locale: 'es', 
        nowIndicator: true, 
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        
        editable: false, 
        selectable: true, 
        
        slotMinTime: '08:00:00', 
        slotMaxTime: '20:00:00',
        
        // Permite la selección de un rango de tiempo para añadir disponibilidad
        select: function(info) {
            const startDate = new Date(info.startStr);
            const initialStartTime = info.startStr.substring(11, 16); 
            const initialEndTime = info.endStr.substring(11, 16);     
            
            $('#displayFecha').text(startDate.toLocaleDateString('es-ES'));
            $('#franjaFecha').val(info.startStr.substring(0, 10)); 

            $('#inputHoraInicio').val(initialStartTime || '09:00'); 
            $('#inputHoraFin').val(initialEndTime || '10:00');

            var modal = new bootstrap.Modal(document.getElementById('modalCrearFranja'));
            modal.show();
            
            calendar.unselect();
        },

        // Función para cargar los eventos (citas y disponibilidad)
        events: function(fetchInfo, successCallback, failureCallback) {
            $.ajax({
                url: 'presentacion/empleado/ajax/cargarCitas.php', 
                method: 'GET',
                dataType: 'json',
                data: {
                    idEmpleado: idEmpleado,
                    start: fetchInfo.startStr.substring(0, 10), 
                    end: fetchInfo.endStr.substring(0, 10)
                },
                success: function(data) {
                    if (data.error) {
                         alert('Error al cargar la agenda: ' + data.error);
                         failureCallback(data.error);
                         return;
                    }
                    
                    var events = data.map(function(cita) {
                        const isAvailableSlot = !cita.NombreCliente; // Si no hay cliente, es un espacio libre/disponibilidad
                        let estadoId = cita.EstadoCita ? parseInt(cita.EstadoCita) : 0; 
                        
                        // Si es un slot libre, forzamos el estado a 0 (Libre) o usamos un ID de estado que lo represente si la DB lo requiere
                        if (isAvailableSlot) {
                            estadoId = 0; // Usamos 0 para representar "Libre" en el frontend
                        }
                        
                        // Título del evento
                        const eventTitle = isAvailableSlot 
                            ? ('Libre: ' + cita.NombreServicio) 
                            : (getEstadoNombre(estadoId) + ': ' + cita.NombreServicio + (cita.NombreCliente ? ' - ' + cita.NombreCliente : ''));
                        
                        return {
                            id: cita.idAgenda,
                            title: eventTitle,
                            start: cita.Fecha + 'T' + cita.HoraInicio, 
                            end: cita.Fecha + 'T' + cita.HoraFin,
                            extendedProps: {
                                // Almacenar el idEstadoCita real (o 0 si es libre)
                                estado: estadoId,
                                // Estos datos solo existen si no es un slot libre
                                cliente: cita.NombreCliente || 'N/A',
                                servicio: cita.NombreServicio,
                                observaciones: cita.Comentarios || 'Ninguna',
                            },
                            color: getColorByEstado(estadoId),
                        };
                    });
                    successCallback(events);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error, xhr.responseText); 
                    alert('Error de conexión. Consulte la consola para el error de PHP/SQL.');
                    failureCallback(error);
                }
            });
        },
        
        // Muestra detalles de la cita al hacer clic en un evento
        eventClick: function(info) {
            const props = info.event.extendedProps;
            const start = info.event.start;
            const end = info.event.end;

            $('#detalleFechaHora').text(start.toLocaleDateString() + ' ' + 
                                       start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + 
                                       ' - ' + end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));

            $('#detalleServicio').text(props.servicio);
            $('#detalleCliente').text(props.cliente);
            // Usamos la lógica de los colores y textos corregidos
            $('#detalleEstado').text(getEstadoNombre(props.estado))
                               .removeClass()
                               .addClass('badge ' + getBadgeClass(props.estado));
            $('#detalleObservaciones').text(props.observaciones);
            
            var modal = new bootstrap.Modal(document.getElementById('modalDetalleCita'));
            modal.show();
        }

    });

    calendar.render();


    $('#formCrearFranja').on('submit', function(e) {
        e.preventDefault();
        
        const fecha = $('#franjaFecha').val();
        const horaInicio = $('#inputHoraInicio').val();
        const horaFin = $('#inputHoraFin').val();
        const servicioId = $('#selectServicio').val();
        
        const startDateTime = fecha + 'T' + horaInicio + ':00';
        const endDateTime = fecha + 'T' + horaFin + ':00';
        
        if (!servicioId) {
            alert("Debe seleccionar un servicio.");
            return;
        }

        if (horaInicio >= horaFin) {
            alert("La hora de inicio debe ser anterior a la hora de fin.");
            return;
        }

        $.ajax({
            url: 'presentacion/empleado/ajax/gestionarAgenda.php', 
            method: 'POST',
            dataType: 'json', 
            data: {
                action: 'create',
                idEmpleado: idEmpleado,
                start: startDateTime, 
                end: endDateTime,
                servicioId: servicioId
            },
            success: function(response) {
                if (response.success) {
                   
                    alert("Disponibilidad añadida correctamente.");
                    $('#modalCrearFranja').modal('hide');
                    calendar.refetchEvents(); 
                } else {
                    alert("Error al añadir disponibilidad: " + (response.error || 'Error desconocido.'));
                }
            },
            error: function(xhr, status, error) {
                 console.error("Error AJAX:", status, error, xhr.responseText);
                 alert("Error de conexión al registrar la franja. (Error: Consulte la consola)");
            }
        });
    });
});
</script>
</body>
</html>