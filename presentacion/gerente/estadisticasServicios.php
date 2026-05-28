<?php

if (!isset($_SESSION) || $_SESSION["rol"] != "gerente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

require_once(__DIR__ . "/../../logica/Estadistica.php"); 

$estadistica = new Estadistica();

$totalCitas = $estadistica->totalCitasAtendidas();

$totalServiciosRegistrados = $estadistica->totalServiciosRegistrados();

$serviciosTop = $estadistica->serviciosMasSolicitados();

$ingresosServicios = $estadistica->ingresosEstimadosPorServicio(); 

?>

<?php 
include("presentacion/encabezadoG.php");
include("presentacion/menuGerente.php");
?>

<div class="container mt-5">
    <h2 class="text-primary mb-5"><i class="fa-solid fa-chart-line"></i> Dashboard de Estadísticas de Servicios</h2>

    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <h5 class="card-title">Citas Atendidas</h5>
                    <p class="card-text fs-2"><?php echo $totalCitas; ?></p>
                    <small>Total de servicios completados.</small>
                </div>
            </div>
        </div>
       <div class="col-md-4">
            <div class="card text-white bg-info shadow">
                <div class="card-body">
                    <h5 class="card-title">Servicios Registrados</h5>
                    <p class="card-text fs-2"><?php echo $totalServiciosRegistrados; ?></p> 
                    <small>Diferentes tipos de servicios ofrecidos.</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning shadow">
                <div class="card-body">
                    <h5 class="card-title">Ingresos Estimados (Top)</h5>
                    <?php 
                        $ingresoTotal = array_sum(array_column($ingresosServicios, 'IngresosEstimados'));
                    ?>
                    <p class="card-text fs-2">$<?php echo number_format($ingresoTotal, 0, ',', '.'); ?></p>
                    <small>Suma total de ingresos por citas atendidas.</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    Servicios Más Solicitados (Citas Completadas)
                </div>
                <div class="card-body">
                    <div id="graficaSolicitud" style="width: 100%; height: 350px;"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    Ingresos Estimados por Servicio
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Ingreso Estimado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ingresosServicios as $i): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($i['Servicio']); ?></td>
                                    <td>$<?php echo number_format($i['IngresosEstimados'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawCharts);

function drawCharts() {
    drawServiciosTopChart();
}


function drawServiciosTopChart() {
    var data = google.visualization.arrayToDataTable([
        ['Servicio', 'Total de Citas'],
        <?php 
        foreach ($serviciosTop as $d): ?>
            ['<?= htmlspecialchars($d['Servicio']) ?>', <?= $d['TotalCitas'] ?>],
        <?php endforeach; ?>
    ]);

    var options = {
        title: 'Distribución de los 5 servicios más populares',
        pieHole: 0.4, // Gráfico de Donut
        legend: { position: 'right', alignment: 'center' },
        chartArea: { width: '85%', height: '85%' }
    };

    var chart = new google.visualization.PieChart(document.getElementById('graficaSolicitud'));
    chart.draw(data, options);
}

</script>