<?php

if (!isset($_SESSION)) {
    session_start();
}
if ($_SESSION["rol"] != "cliente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

require_once("logica/Cliente.php");
require_once("logica/PQRS.php");

$idCliente = $_SESSION["id"];

$cliente = new Cliente($idCliente);
$historialCitas = $cliente->consultarHistorialCitas();

$historialPQRS = PQRS::consultarHistorialPorCliente($idCliente);
?>

<?php include("presentacion/encabezadoC.php");  ?>
<?php include("presentacion/menuCliente.php"); ?>

<div class="container mt-5">
    
    <div class="card shadow mb-5">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Historial de Citas</h4>
        </div>
        <div class="card-body">
            <?php if (empty($historialCitas)): ?>
                <div class="alert alert-info text-center">
                    Aún no tienes citas registradas en tu historial.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th># Cita</th>
                                <th>Fecha</th>
                                <th>Hora Ini.</th>
                                <th>Hora Fin</th>
                                <th>Servicio</th>
                                <th>Empleado</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historialCitas as $cita): ?>
                                <tr>
                                    <td><?php echo $cita['idCita']; ?></td>
                                    <td><?php echo $cita['Fecha']; ?></td>
                                    <td><?php echo substr($cita['HoraInicio'], 0, 5); ?></td>
                                    <td><?php echo substr($cita['HoraFin'], 0, 5); ?></td>
                                    <td><?php echo $cita['Servicio']; ?></td>
                                    <td><?php echo $cita['Empleado']; ?></td>
                                    <?php 
                                        
                                        $clase_badge = 'secondary';
                                        if ($cita['Estado'] == 'Activa' || $cita['Estado'] == 'En Curso') {
                                            $clase_badge = 'success';
                                        } elseif ($cita['Estado'] == 'Cancelada' || $cita['Estado'] == 'No Asistió') {
                                            $clase_badge = 'danger';
                                        } elseif ($cita['Estado'] == 'Finalizada') {
                                            $clase_badge = 'primary';
                                        }
                                    ?>
                                    <td><span class="badge bg-<?php echo $clase_badge; ?>"><?php echo $cita['Estado']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <hr class="my-5"> 

    <div class="card shadow">
        <div class="card-header bg-danger text-white">
            <h4 class="mb-0">Historial de PQRS</h4>
        </div>
        <div class="card-body">
            <?php if (empty($historialPQRS)): ?>
                <div class="alert alert-info text-center">
                    Aún no has registrado ninguna Petición, Queja, Reclamo o Sugerencia.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th># PQRS</th>
                                <th>Tipo</th>
                                <th>Fecha de Envío</th>
                                <th>Descripción</th>
                                <th>Empleado Relac.</th>
                                <th>Evidencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historialPQRS as $pqrs): ?>
                                <tr>
                                    <td><?php echo $pqrs['idPQRS']; ?></td>
                                    <?php
                                        $clase_pqrs_badge = 'secondary';
                                        switch ($pqrs['TipoPQRS']) {
                                            case 'Peticion':
                                                $clase_pqrs_badge = 'info'; 
                                                break;
                                            case 'Queja':
                                                $clase_pqrs_badge = 'warning'; 
                                                break;
                                            case 'Reclamo':
                                                $clase_pqrs_badge = 'danger'; 
                                                break;
                                            case 'Sugerencia':
                                                $clase_pqrs_badge = 'success'; 
                                                break;
                                            default:
                                                $clase_pqrs_badge = 'secondary'; 
                                                break;
                                        }
                                    ?>
                                    <td><span class="badge bg-<?php echo $clase_pqrs_badge; ?>"><?php echo $pqrs['TipoPQRS']; ?></span></td>
                                    <td><?php echo $pqrs['Fecha']; ?></td>
                                    <td><?php echo substr($pqrs['Descripcion'], 0, 50) . '...'; ?></td>
                                    <td><?php echo $pqrs['Empleado']; ?></td>
                                    <td>
                                        <?php if (!empty($pqrs['Evidencia'])): ?>
                                            <a href="<?php echo $pqrs['Evidencia']; ?>" target="_blank" class="btn btn-sm btn-info">Ver</a>
                                        <?php else: ?>
                                            N/A
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
    <a href="?pid=<?php echo base64_encode("presentacion/cliente/historialpdf.php"); ?>" class="btn btn-secondary mt-3">
    Generar PDF del Historial
</a>
</div>