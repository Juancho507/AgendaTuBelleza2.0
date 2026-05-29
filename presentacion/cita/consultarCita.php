<?php
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "cliente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}


$idCliente = $_SESSION["id"];

$filter_q = trim($_GET['q'] ?? '');
$filter_servicio = trim($_GET['servicio'] ?? '');
$filter_empleado = trim($_GET['empleado'] ?? '');
$filter_estado = trim($_GET['estado'] ?? '');
$filter_from = trim($_GET['from'] ?? '');
$filter_to = trim($_GET['to'] ?? '');

$errorCarga = null;
$historial = [];

try {
    $historial = Cita::obtenerHistorialCitas($idCliente); 
    if (!is_array($historial)) {
        $errorCarga = "Respuesta inesperada del servidor al cargar el historial.";
        $historial = [];
    }
} catch (Exception $e) {
    $errorCarga = "Error de conexión o en la base de datos: " . $e->getMessage();
    $historial = [];
}

function filtrarCita(array $c) {
    global $filter_q, $filter_servicio, $filter_empleado, $filter_estado, $filter_from, $filter_to;

    if ($filter_q !== '') {
        $q = mb_strtolower($filter_q);
        $hay = (mb_stripos($c['NombreServicio'] ?? '', $q) !== false)
            || (mb_stripos($c['NombreEmpleado'] ?? '', $q) !== false)
            || (mb_stripos($c['comentarios'] ?? '', $q) !== false);
        if (!$hay) return false;
    }

    if ($filter_servicio !== '' && isset($c['NombreServicio'])) {
        if (mb_strtolower($c['NombreServicio']) !== mb_strtolower($filter_servicio)) return false;
    }

    if ($filter_empleado !== '' && isset($c['NombreEmpleado'])) {
        if (mb_strtolower($c['NombreEmpleado']) !== mb_strtolower($filter_empleado)) return false;
    }

    if ($filter_estado !== '' && isset($c['EstadoNombre'])) {
        if (mb_strtolower($c['EstadoNombre']) !== mb_strtolower($filter_estado)) return false;
    }

    if ($filter_from !== '') {
        $from = DateTime::createFromFormat('Y-m-d', $filter_from);
        $cFecha = isset($c['fecha']) ? DateTime::createFromFormat('Y-m-d', $c['fecha']) : null;
        if ($from && $cFecha && $cFecha < $from) return false;
    }
    if ($filter_to !== '') {
        $to = DateTime::createFromFormat('Y-m-d', $filter_to);
        $cFecha = isset($c['fecha']) ? DateTime::createFromFormat('Y-m-d', $c['fecha']) : null;
        if ($to && $cFecha && $cFecha > $to) return false;
    }

    return true;
}

function formatDateReadable($date) {
    if (!$date) return '';
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d) return htmlspecialchars($date);
    return $d->format('d/m/Y');
}
function safe($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function estadoBadgeClass($estadoId = null, $estadoNombre = '') {
    $estadoId = intval($estadoId);
    switch ($estadoId) {
        case 1: return 'badge bg-success';       
        case 2: return 'badge bg-primary';       
        case 3: return 'badge bg-secondary';    
        case 4: return 'badge bg-danger';       
        case 5: return 'badge bg-dark';         
        case 6: return 'badge bg-warning text-dark'; 
        default:
            $n = mb_strtolower($estadoNombre);
            if (strpos($n, 'pend') !== false) return 'badge bg-warning text-dark';
            if (strpos($n, 'act') !== false) return 'badge bg-success';
            if (strpos($n, 'final') !== false || strpos($n, 'complet') !== false) return 'badge bg-secondary';
            if (strpos($n, 'cancel') !== false) return 'badge bg-danger';
            return 'badge bg-info';
    }
}

$lista = [];
foreach ($historial as $row) {
    if (isset($row['Fecha']) && !isset($row['fecha'])) $row['fecha'] = $row['Fecha'];
    if (isset($row['HoraInicio']) && !isset($row['hora_inicio'])) $row['hora_inicio'] = $row['HoraInicio'];
    if (isset($row['EstadoNombre']) && !isset($row['EstadoNombre'])) $row['EstadoNombre'] = $row['EstadoNombre'];
    $lista[] = $row;
}

$filtrado = array_filter($lista, 'filtrarCita');
usort($filtrado, function($a, $b) {
    $fa = ($a['fecha'] ?? '') . ' ' . ($a['hora_inicio'] ?? '');
    $fb = ($b['fecha'] ?? '') . ' ' . ($b['hora_inicio'] ?? '');
    return strcmp($fa, $fb);
});
?>
<?php include("presentacion/encabezadoC.php"); ?>
<?php include("presentacion/menuCliente.php"); ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consultar Historial de Citas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #panelDetalle { position: fixed; right: 0; top: 0; height: 100%; width: 420px; background: #fff; z-index: 1050; box-shadow: -4px 0 12px rgba(0,0,0,.08); display: none; padding: 18px; overflow-y: auto; }
        .estado-pill { min-width: 90px; display: inline-block; text-align: center; }
        .lista-cita { cursor: pointer; }
        .filtro-row .form-control, .filtro-row .form-select { height: calc(1.5em + .75rem + 2px); }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">
    <div class="card shadow-sm">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Historial de Citas</h5>
                <small class="text-muted">Solo lectura · Actualizado automáticamente por el sistema</small>
            </div>

            <?php if ($errorCarga): ?>
                <div class="alert alert-danger">
                    <strong>Error:</strong> <?= safe($errorCarga) ?>
                </div>
            <?php endif; ?>

            <form method="get" class="row g-2 filtro-row mb-3">
                <div class="col-md-4">
                    <input name="q" value="<?= safe($filter_q) ?>" class="form-control" placeholder="Buscar (servicio, empleado, texto)">
                </div>
                <div class="col-md-2">
                    <input name="from" value="<?= safe($filter_from) ?>" class="form-control" type="date" placeholder="Desde">
                </div>
                <div class="col-md-2">
                    <input name="to" value="<?= safe($filter_to) ?>" class="form-control" type="date" placeholder="Hasta">
                </div>
                <div class="col-md-2">
                    <input name="empleado" value="<?= safe($filter_empleado) ?>" class="form-control" placeholder="Empleado (exacto)">
                </div>
                <div class="col-md-2">
                    <input name="servicio" value="<?= safe($filter_servicio) ?>" class="form-control" placeholder="Servicio (exacto)">
                </div>

                <div class="col-12 d-flex gap-2 mt-1">
                    <select name="estado" class="form-select w-auto">
                        <option value="">-- Estado (todos) --</option>
                        <option <?= $filter_estado==='activa' ? 'selected':'' ?> value="activa">Activa</option>
                        <option <?= $filter_estado==='pendiente' ? 'selected':'' ?> value="pendiente">Pendiente</option>
                        <option <?= $filter_estado==='en curso' ? 'selected':'' ?> value="en curso">En curso</option>
                        <option <?= $filter_estado==='finalizada' ? 'selected':'' ?> value="finalizada">Finalizada</option>
                        <option <?= $filter_estado==='cancelada' ? 'selected':'' ?> value="cancelada">Cancelada</option>
                        <option <?= $filter_estado==='no asistio' ? 'selected':'' ?> value="no asistio">No asistió</option>
                    </select>
                    <button class="btn btn-primary" type="submit">Aplicar</button>
                    <a class="btn btn-outline-secondary" href="?">Limpiar</a>
                </div>
            </form>

            <div class="list-group">
                <?php if (empty($filtrado)): ?>
                    <div class="alert alert-info mb-0">No se encontraron registros que coincidan con los filtros.</div>
                <?php else: ?>
                    <?php foreach ($filtrado as $r): 
                        $fecha = $r['fecha'] ?? $r['Fecha'] ?? '';
                        $hora = $r['hora_inicio'] ?? $r['HoraInicio'] ?? '';
                        $duracion = $r['DuracionMinutos'] ?? ($r['Duracion'] ?? '');
                        $estadoNombre = $r['EstadoNombre'] ?? $r['estado'] ?? ($r['Estado'] ?? '');
                        $estadoId = $r['estadoId'] ?? $r['EstadoId'] ?? null;
                        $id = $r['id'] ?? $r['idCita'] ?? ($r['idCita'] ?? '');
                        ?>
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center lista-cita"
                             data-det='<?php
                                 $det = [
                                     'id' => $id,
                                     'servicio' => $r['NombreServicio'] ?? $r['servicio'] ?? '',
                                     'empleado' => $r['NombreEmpleado'] ?? $r['empleado'] ?? '',
                                     'fecha' => $fecha,
                                     'hora' => $hora,
                                     'duracion' => $duracion,
                                     'estado' => $estadoNombre,
                                     'comentarios' => $r['comentarios'] ?? $r['Comentarios'] ?? '',
                                     'observaciones' => $r['observaciones'] ?? $r['Observaciones'] ?? '',
                                     'idAgenda' => $r['idAgenda'] ?? $r['idAgenda'] ?? ''
                                 ];
                                 echo htmlspecialchars(json_encode($det), ENT_QUOTES, 'UTF-8');
                             ?>'>
                            <div>
                                <div class="fw-bold"><?= safe($r['NombreServicio'] ?? $r['servicio'] ?? 'Servicio desconocido') ?></div>
                                <div class="small text-muted"><?= safe($r['NombreEmpleado'] ?? $r['empleado'] ?? 'Empleado desconocido') ?> · <?= formatDateReadable($fecha) ?> <?= safe(substr($hora,0,5)) ?></div>
                            </div>
                            <div class="text-end">
                                <div class="<?= estadoBadgeClass($estadoId, $estadoNombre) ?> estado-pill"><?= safe($estadoNombre) ?></div>
                                <div class="small text-muted mt-1"><?= $duracion ? safe($duracion . " min") : '' ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<div id="panelDetalle" role="dialog" aria-hidden="true">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Detalle de la cita</h5>
        <button class="btn btn-sm btn-outline-secondary" onclick="cerrarPanel()">Cerrar</button>
    </div>

    <div id="contenidoDetalle">
        <p class="text-muted">Selecciona una cita para ver el detalle.</p>
    </div>
</div>

<script>
(function(){
    const items = document.querySelectorAll('.lista-cita');
    const panel = document.getElementById('panelDetalle');
    const contenido = document.getElementById('contenidoDetalle');

    function abrirPanel(data) {
        let html = '';
        html += '<dl class="row">';
        html += '<dt class="col-4">Servicio</dt><dd class="col-8">' + escapeHtml(data.servicio) + '</dd>';
        html += '<dt class="col-4">Empleado</dt><dd class="col-8">' + escapeHtml(data.empleado) + '</dd>';
        html += '<dt class="col-4">Fecha</dt><dd class="col-8">' + escapeHtml(formatDate(data.fecha)) + '</dd>';
        html += '<dt class="col-4">Hora</dt><dd class="col-8">' + escapeHtml(data.hora) + '</dd>';
        if (data.duracion) html += '<dt class="col-4">Duración</dt><dd class="col-8">' + escapeHtml(data.duracion + ' min') + '</dd>';
        html += '<dt class="col-4">Estado</dt><dd class="col-8">' + escapeHtml(data.estado) + '</dd>';
        if (data.comentarios) html += '<dt class="col-4">Comentarios</dt><dd class="col-8">' + escapeHtml(data.comentarios) + '</dd>';
        if (data.observaciones) html += '<dt class="col-4">Observaciones</dt><dd class="col-8">' + escapeHtml(data.observaciones) + '</dd>';
        html += '</dl>';

        contenido.innerHTML = html;
        panel.style.display = 'block';
    }

    items.forEach(it => {
        it.addEventListener('click', function(){
            const raw = this.getAttribute('data-det');
            try {
                const obj = JSON.parse(raw);
                abrirPanel(obj);
            } catch(e) {
                abrirPanel({servicio: 'Error al leer detalle'});
            }
        });
    });

    window.cerrarPanel = function() {
        panel.style.display = 'none';
    };

    // util helpers
    function escapeHtml(str) {
        if (!str && str !== 0) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    function formatDate(d){
        if (!d) return '';
        // espera YYYY-MM-DD -> devolver dd/mm/YYYY
        const parts = d.split('-');
        if (parts.length === 3) return parts[2] + '/' + parts[1] + '/' + parts[0];
        return d;
    }
})();
</script>

</body>
</html>
