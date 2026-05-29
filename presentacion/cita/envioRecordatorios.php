<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

$idCliente = $_SESSION["id"];

try {
    $cita = new Cita();
    $citas = $cita->obtenerHistorialCitas($idCliente);
    
    if (!$citas || !is_array($citas)) {
        $errorDB = "❌ Error de conexión con la base de datos.";
    }
    
} catch (Exception $e) {
    $errorDB = "❌ Error del servidor al consultar las citas.";
}
?>

<?php include("presentacion/encabezadoC.php"); ?>
<?php include("presentacion/menuCliente.php"); ?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Historial de Citas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
    <h4 class="mb-4">📜 Historial de Mis Citas</h4>

    <?php if (isset($errorDB)) { ?>
        <div class="alert alert-danger text-center">
            <?= $errorDB ?>
        </div>

    <?php } else if (count($citas) == 0) { ?>

        <div class="alert alert-warning text-center">
            📭 No tienes citas registradas en el sistema.
        </div>

    <?php } else { ?>

        <?php foreach ($citas as $c) { 

            $color = "secondary";
            if ($c["estadoId"] == 1) $color = "primary";      
            if ($c["estadoId"] == 2) $color = "warning";      
            if ($c["estadoId"] == 3) $color = "success";       
            if ($c["estadoId"] == 4) $color = "danger";     
            if ($c["estadoId"] == 5) $color = "dark";       
        ?>

        <div class="card mb-3 shadow-sm border-start border-4 border-<?= $color ?>">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <b><?= $c["NombreServicio"] ?></b><br>
                    👤 <?= $c["NombreEmpleado"] ?><br>
                    📅 <?= $c["fecha"] ?> ⏰ <?= $c["hora_inicio"] ?><br>
                    🏷 Estado: <span class="badge bg-<?= $color ?>"><?= $c["EstadoNombre"] ?></span>
                </div>

                <button class="btn btn-outline-primary btn-sm"
                onclick="verDetalle(
                    '<?= $c["NombreServicio"] ?>',
                    '<?= $c["NombreEmpleado"] ?>',
                    '<?= $c["fecha"] ?>',
                    '<?= $c["hora_inicio"] ?>',
                    '<?= $c["EstadoNombre"] ?>',
                    '<?= $c["comentarios"] ?>'
                )">
                Ver detalle
                </button>
            </div>
        </div>

        <?php } ?>

    <?php } ?>
</div>
<div id="panel" class="position-fixed top-0 end-0 bg-white p-4 shadow"
style="width:400px;display:none;height:100%;z-index:9999">

<h5 class="mb-3">📝 Detalle de la Cita</h5>

<p id="detalle"></p>

<button type="button" onclick="cerrar()" class="btn btn-secondary w-100 mt-3">Cerrar</button>
</div>

<script>
function verDetalle(servicio, empleado, fecha, hora, estado, comentarios) {
    document.getElementById("panel").style.display = "block";

    document.getElementById("detalle").innerHTML = `
        <b>Servicio:</b> ${servicio}<br>
        <b>Empleado:</b> ${empleado}<br>
        <b>Fecha:</b> ${fecha}<br>
        <b>Hora:</b> ${hora}<br>
        <b>Estado:</b> ${estado}<br><br>
        <b>Observaciones:</b><br>
        ${comentarios ? comentarios : "Sin observaciones"}
    `;
}

function cerrar(){
    document.getElementById("panel").style.display = "none";
}
</script>

</body>
</html>
