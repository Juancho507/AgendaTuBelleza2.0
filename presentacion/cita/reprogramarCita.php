<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "cliente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

$idCliente = $_SESSION["id"];

$cita = new Cita();
$citas = $cita->consultarReprogramables($idCliente);
$franjas = $cita->consultarFranjasLibres();

if (isset($_POST["accion"]) && $_POST["accion"] == "reprogramar") {
    
    $idCita = $_POST["idCita"];
    $nuevaAgenda = $_POST["nuevaAgenda"];
    $fechaOriginal = $_POST["fechaOriginal"];
    $horaOriginal = $_POST["horaOriginal"];
    
    $fechaHoraOriginal = $fechaOriginal . " " . $horaOriginal;
    $fechaOriginalDT = new DateTime($fechaHoraOriginal);
    $fechaActual = new DateTime();
    
    $diferencia = $fechaActual->diff($fechaOriginalDT);
    $horasRestantes = ($diferencia->days * 24) + $diferencia->h;
    
    if ($fechaOriginalDT < $fechaActual) {
        echo "<script>
            alert('❌ No se puede reprogramar una cita vencida');
            history.back();
        </script>";
        exit();
    }
    
    if ($horasRestantes < 24) {
        echo "<script>
            alert('❌ La cita solo puede reprogramarse con mínimo 24 horas de anticipación');
            history.back();
        </script>";
        exit();
    }
    
    try {
        $c = new Cita($idCita);
        $c->reprogramar($nuevaAgenda);
        
        echo "<script>
            alert('✅ Cita reprogramada correctamente');
            location.href='?pid=" . base64_encode("presentacion/cita/reprogramarCita.php") . "';
        </script>";
        exit();
        
    } catch (Exception $e) {
        echo "<script>
            alert('❌ Error en la base de datos al reprogramar la cita');
            history.back();
        </script>";
        exit();
    }
}
?>

<?php include("presentacion/encabezadoC.php"); ?>
<?php include("presentacion/menuCliente.php"); ?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Reprogramar Cita</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
<h4 class="mb-4">Mis Citas Reprogramables</h4>

<?php if ($citas->num_rows == 0) { ?>
<div class="alert alert-info">
    🔔 No tiene citas activas o pendientes.
</div>
<?php } ?>

<?php while ($c = $citas->fetch_assoc()) { ?>
<div class="card mb-2 shadow-sm">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <b><?= $c["servicio"] ?></b> | 
            <?= $c["empleado"] ?> | 
            <?= $c["Fecha"] ?> <?= $c["HoraInicio"] ?>
        </div>
        <button class="btn btn-primary btn-sm"
        onclick="ver(
            <?= $c['idCita'] ?>,
            '<?= $c['servicio'] ?>',
            '<?= $c['empleado'] ?>',
            '<?= $c['Fecha'] ?>',
            '<?= $c['HoraInicio'] ?>'
        )">
        Ver / Reprogramar
        </button>
    </div>
</div>
<?php } ?>
</div>

<div id="panel" class="position-fixed top-0 end-0 bg-white p-4 shadow"
style="width:400px;display:none;height:100%;z-index:9999">

<h5 class="mb-3">Detalle de la Cita</h5>
<p id="detalle" class="fw-bold"></p>

<form method="POST">
    <input type="hidden" name="accion" value="reprogramar">
    <input type="hidden" name="idCita" id="idCita">
    <input type="hidden" name="fechaOriginal" id="fechaOriginal">
    <input type="hidden" name="horaOriginal" id="horaOriginal">

    <label class="form-label">Nueva franja horaria</label>
    <select name="nuevaAgenda" class="form-select mb-3" required>
    <?php while ($f = $franjas->fetch_assoc()) { ?>
        <option value="<?= $f["idAgenda"] ?>">
            <?= $f["servicio"] ?> | <?= $f["empleado"] ?> | <?= $f["Fecha"] ?> <?= $f["HoraInicio"] ?>
        </option>
    <?php } ?>
    </select>

    <button class="btn btn-success w-100">✅ Reprogramar</button>
    <button type="button" onclick="cerrar()" class="btn btn-secondary w-100 mt-2">❌ Cancelar</button>
</form>
</div>

<script>
function ver(id,s,e,f,h){
    document.getElementById("panel").style.display="block";
    document.getElementById("idCita").value = id;
    document.getElementById("fechaOriginal").value = f;
    document.getElementById("horaOriginal").value = h;
    document.getElementById("detalle").innerHTML = s+" | "+e+" | "+f+" "+h;
}

function cerrar(){
    document.getElementById("panel").style.display="none";
}
</script>

</body>
</html>
