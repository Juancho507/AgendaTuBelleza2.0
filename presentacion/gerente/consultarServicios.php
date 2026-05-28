<?php

if (!isset($_SESSION) || $_SESSION["rol"] != "gerente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}
$servicios = Servicio::consultarServiciosCompletos();

$servicioDetalle = null;
$idServicioDetalle = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idServicioDetalle > 0) {
    $servicio = new Servicio($idServicioDetalle);
    $servicioDetalle = $servicio->consultarDetalle();
}

?>

<?php
include("presentacion/encabezadoG.php");
include("presentacion/menuGerente.php");
?>

<div class="container mt-5">

    <?php if ($servicioDetalle): ?>
        <div class="row">
            <div class="col-12">
                <h2 class="text-info mb-4"><i class="fa-solid fa-file-invoice"></i> Detalle del Servicio: <?php echo htmlspecialchars($servicioDetalle['Nombre']); ?></h2>
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">Información Completa</div>
                    <div class="card-body">
                        <p class="fw-bold">Descripción Completa:</p>
                        <p><?php echo nl2br(htmlspecialchars($servicioDetalle['DescripcionCompleta'])); ?></p>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Precio:</strong> $<?php echo number_format($servicioDetalle['Precio'], 0, ',', '.'); ?></p>
                                </div>
                            <div class="col-md-6">
                                <p><strong>Estado:</strong> <span class="badge <?php echo ($servicioDetalle['Estado'] == 1) ? 'bg-success' : 'bg-danger'; ?>"><?php echo ($servicioDetalle['Estado'] == 1) ? 'Activo' : 'Inactivo'; ?></span></p>
                            </div>
                        </div>
                        <hr>
                        <p class="fw-bold">Producto/Material Asociado:</p>
                        <?php
                        if (!empty($servicioDetalle['NombreProducto'])): ?>
                            <p class="text-primary fw-bold">
                                <?php echo htmlspecialchars($servicioDetalle['NombreProducto']); ?> 
                                <small class="text-muted">(ID: <?php echo htmlspecialchars($servicioDetalle['Producto_idProducto']); ?>)</small>
                            </p>
                        <?php else: ?>
                            <p class="text-muted">No hay un producto específico asociado a este servicio.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-end">
                        <a href="?pid=<?php echo base64_encode("presentacion/gerente/consultarServicios.php"); ?>" class="btn btn-primary">
                            <i class="fa-solid fa-arrow-left"></i> Volver al Listado
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <h2 class="text-primary mb-4"><i class="fa-solid fa-clipboard-list"></i> Listado de Servicios</h2>
            </div>
            
            <div class="col-12 mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-10">
                        <input type="text" id="filtroServicio" class="form-control" placeholder="Buscar por Nombre o Descripción..." value="">
                    </div>
                    <div class="col-md-2">
                         <a href="presentacion/gerente/historialServiciospdf.php" target="_blank" class="btn btn-danger w-100">
                            <i class="fa-solid fa-file-pdf"></i> Exportar PDF
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        Resultados (<?php echo count($servicios); ?>)
                    </div>
                    <div class="card-body">
                        <?php if (empty($servicios)): ?>
                            <div class="alert alert-info text-center" role="alert">
                                No hay servicios registrados actualmente.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle" id="tablaServicios">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Precio</th>
                                            <th>Estado</th>
                                            <th>Detalle</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($servicios as $s): ?>
                                        <tr class="fila-servicio">
                                            <td class="idServicio"><?php echo $s['idServicio']; ?></td>
                                            <td>
                                                <strong class="nombreServicio"><?php echo htmlspecialchars($s['Nombre']); ?></strong>
                                                <small class="d-block text-muted text-truncate descripcionServicio"><?php echo htmlspecialchars($s['DescripcionBreve']); ?></small>
                                            </td>
                                            <td class="precioServicio">$<?php echo number_format($s['Precio'], 0, ',', '.'); ?></td>
                                            <td class="estadoServicio">
                                                <span class="badge <?php echo ($s['Estado'] == 1) ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo ($s['Estado'] == 1) ? 'Activo' : 'Inactivo'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?pid=<?php echo base64_encode("presentacion/gerente/consultarServicios.php") . "&id={$s['idServicio']}"; ?>" 
                                                   class="btn btn-sm btn-outline-info">
                                                    <i class="fa-solid fa-eye"></i> Ver
                                                </a>
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
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

function resaltar(texto, palabras) {
  const palabrasValidas = palabras.filter(p => p.trim() !== ""); 
  
  if (palabrasValidas.length === 0) return texto;

  const regexString = palabrasValidas
    .map(p => p.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'))
    .join('|');
    
  const re = new RegExp("(" + regexString + ")", "gi");
  
  return texto.replace(re, "<strong>$1</strong>");
}

function aplicarFiltroServicios() {
  const filtro = $("#filtroServicio").val().toLowerCase().trim();
  const palabras = filtro.split(/\s+/).filter(p => p.length > 0); 

  const filas = $("#tablaServicios tbody tr");

  filas.each(function () {
    const fila = $(this);
    
    const nombreElement = fila.find(".nombreServicio");
    const descElement = fila.find(".descripcionServicio");
    const estadoElement = fila.find(".estadoServicio span");

    const nombreOriginal = nombreElement.text();
    const descOriginal = descElement.text();
    const estadoOriginal = estadoElement.text();

    const textoCompleto = (nombreOriginal + " " + descOriginal + " " + estadoOriginal).toLowerCase();

    const coincide = palabras.every(p => textoCompleto.includes(p));

    if (coincide) {
      fila.show();
      
      nombreElement.html(resaltar(nombreOriginal, palabras));
      descElement.html(resaltar(descOriginal, palabras));
      estadoElement.html(resaltar(estadoOriginal, palabras));
      
    } else {
      fila.hide();
      
      nombreElement.text(nombreOriginal); 
      descElement.text(descOriginal);
      estadoElement.text(estadoOriginal);
    }
  });
}

$("#filtroServicio").on("input", aplicarFiltroServicios);

$(document).ready(function() {
    aplicarFiltroServicios(); 
});
</script>