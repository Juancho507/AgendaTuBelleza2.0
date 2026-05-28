<?php
session_start();
require("logica/Cliente.php");
require("logica/Gerente.php");
require("logica/Empleado.php");
require("logica/Servicio.php");
require("logica/Producto.php");
require("logica/PQRS.php");
require("logica/Cita.php");
require("logica/Estadistica.php");
require("logica/Agenda.php");
require("logica/EstadoCita.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Agenda tu belleza</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<link href="https://use.fontawesome.com/releases/v5.11.1/css/all.css" rel="stylesheet" />
<script src="https://kit.fontawesome.com/14596e32cc.js" crossorigin="anonymous"></script>
  <link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
</head>

<body class="bg-light">

<?php

$paginas_sin_autenticacion = array(
    "presentacion/autenticarse.php",
    "presentacion/cliente/registroCliente.php",
    "presentacion/empleado/registroEmpleado.php"
);


$paginas_con_autenticacion = array(
    "presentacion/cliente/editarCliente.php",
    "presentacion/cliente/eliminarCliente.php",
    "presentacion/cliente/historialCliente.php",
    "presentacion/cliente/registrarPQRS.php",
    "presentacion/cliente/historialpdf.php",
    "presentacion/sesionCliente.php",
    "presentacion/sesionEmpleado.php",
    "presentacion/sesionGerente.php",
    "presentacion/gerente/registrarServicio.php",
    "presentacion/gerente/editarServicio.php",
    "presentacion/gerente/eliminarServicio.php",
    "presentacion/gerente/consultarServicios.php",
    "presentacion/gerente/estadisticasServicios.php",
    "presentacion/gerente/historialServiciospdf.php",
    "presentacion/empleado/editarPerfil.php",
    "presentacion/empleado/asignarCita.php",
    "presentacion/empleado/consultarPQRS.php",
    "presentacion/empleado/consultarAgenda.php",
    "presentacion/empleado/pdfpqrs.php",
    "presentacion/gerente/aceptarEmpleado.php"
    
);


if (!isset($_GET["pid"])) {
    include("presentacion/autenticarse.php");
} else {
    $pid = base64_decode($_GET["pid"]);

    if (in_array($pid, $paginas_sin_autenticacion)) {
        include $pid;
    } else if (in_array($pid, $paginas_con_autenticacion)) {
        if (!isset($_SESSION["id"])) {
            include("presentacion/autenticarse.php");
        } else {
            include $pid;
        }
    } else {
        echo "<div class='container mt-5'><h3 class='text-danger text-center'>Error 404 - Página no encontrada</h3></div>";
    }
}
?>

<footer class="text-center py-3 mt-5 bg-white border-top shadow-sm">
  <small class="text-muted">
    &copy; <?php echo date("Y"); ?> Peluquería AgendaTuBelleza. Todos los derechos reservados.
  </small>
</footer>

</body>
</html>
