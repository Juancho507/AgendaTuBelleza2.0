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

$pqrs_list = $empleado->consultarPQRS();
$nombreEmpleado = htmlspecialchars($empleado->getNombre() . ' ' . $empleado->getApellido());

$promedio = "N/A";

if (isset($pqrs_list['error'])) {
    $errorConsulta = true;
    $mensajeError = $pqrs_list['error'];
    $pqrs_list = [];
} else {
    $errorConsulta = false;
    $mensajeError = '';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consultar Retroalimentación (PQRS)</title>
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
    <h2><i class="fa-solid fa-comments"></i> Mi Retroalimentación (PQRS)</h2>
    <p class="text-muted">Mostrando las quejas, reclamos y sugerencias dirigidas a <?php echo $nombreEmpleado; ?>.</p>
    
    <hr>

    <?php if ($errorConsulta): ?>
        <div class="alert alert-danger text-center">
            <i class="fa-solid fa-server"></i> Error de Conexión: <?php echo htmlspecialchars($mensajeError); ?>
            <p class="mt-2"><button class="btn btn-danger" onclick="window.location.reload();">Reintentar Consulta</button></p>
        </div>
    <?php elseif (count($pqrs_list) == 0): ?>
        <div class="alert alert-info text-center">
            <i class="fa-solid fa-comment-slash"></i> No hay PQRS disponibles para este empleado.
        </div>
    <?php else: ?>
        
        <div class="row mb-4">
            
            <div class="col-md-4">
                <div class="card text-center bg-light shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Promedio General </h5>
                        <h1 class="display-4 text-warning"><?php echo $promedio; ?></h1>
                        <p class="card-text text-muted">La tabla PQRS no incluye calificación directa.</p>
                        <p class="card-text text-muted">Basado en <?php echo count($pqrs_list); ?> registros</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow h-100">
                    <div class="card-header bg-primary text-white">
                        Opciones de Búsqueda y Reporte
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-10">
                                <input type="text" id="filtroPQRS" class="form-control" placeholder="Buscar por Cliente, Descripción o Tipo de PQRS..." value="">
                            </div>
                            <div class="col-md-2">
                                 <a href="presentacion/empleado/pdfpqrs.php?empleado=<?php echo $idEmpleado; ?>" target="_blank" class="btn btn-danger w-100">
                                    <i class="fa-solid fa-file-pdf"></i> Exportar PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <h4>Resultados (<?php echo count($pqrs_list); ?>)</h4>
                <div class="list-group" id="listaPQRS">
                    <?php foreach ($pqrs_list as $pqrs): 
                        $claseColor = 'list-group-item-light';
                        $tipo = trim(strtolower($pqrs['TipoPQRSNombre']));
                        
                        switch ($tipo) {
                            case 'queja':
                                $claseColor = 'list-group-item-danger'; 
                                break;
                            case 'reclamo':
                                $claseColor = 'list-group-item-warning'; 
                                break;
                            case 'sugerencia':
                                $claseColor = 'list-group-item-success'; 
                                break;
                            case 'peticion':
                                $claseColor = 'list-group-item-info';
                                break;                 
                            default:
                                $claseColor = 'list-group-item-light'; 
                                break;
                        }
                    ?>
                    <a href="#" class="list-group-item list-group-item-action flex-column align-items-start mb-2 shadow-sm pqrs-item <?php echo $claseColor; ?>"
                       data-tipo="<?php echo strtolower(htmlspecialchars($pqrs['TipoPQRSNombre'])); ?>"
                       data-cliente="<?php echo strtolower(htmlspecialchars($pqrs['ClienteNombre'])); ?>"
                       data-bs-toggle="modal" data-bs-target="#modalPQRS_<?php echo $pqrs['idPQRS']; ?>">
                        
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">
                                <span class="badge bg-dark me-2 tipo-pqrs"><?php echo htmlspecialchars($pqrs['TipoPQRSNombre']); ?></span>
                                Cliente: <span class="cliente-pqrs"><?php echo htmlspecialchars($pqrs['ClienteNombre']); ?></span>
                            </h5>
                            <small class="text-muted fecha-pqrs"><?php echo htmlspecialchars($pqrs['Fecha']); ?></small>
                        </div>
                        <p class="mb-1 text-truncate descripcion-pqrs">Descripción:<?php echo htmlspecialchars($pqrs['Descripcion']); ?></p>
                        <small class="text-info">
                            <?php if (!empty($pqrs['Evidencia'])): ?>
                                <i class="fa-solid fa-paperclip"></i> Evidencia Adjunta
                            <?php else: ?>
                                <i class="fa-solid fa-comment"></i> Solo Comentario
                            <?php endif; ?>
                        </small>
                    </a>
                    
                    <div class="modal fade" id="modalPQRS_<?php echo $pqrs['idPQRS']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-dark text-white">
                                    <h5 class="modal-title">Detalle de <?php echo htmlspecialchars($pqrs['TipoPQRSNombre']); ?> #<?php echo $pqrs['idPQRS']; ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pqrs['ClienteNombre']); ?></p>
                                    <p><strong>Fecha:</strong> <?php echo htmlspecialchars($pqrs['Fecha']); ?></p>
                                    <p><strong>Tipo:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($pqrs['TipoPQRSNombre']); ?></span></p>
                                    <hr>
                                    <p class="fw-bold">Descripción / Comentario:</p>
                                    <blockquote class="blockquote bg-light p-3 rounded">
                                        <?php echo nl2br(htmlspecialchars($pqrs['Descripcion'])); ?>
                                    </blockquote>
                                    
                                    <?php if (!empty($pqrs['Evidencia'])): ?>
                                    <p class="mt-3 fw-bold">Evidencia:</p>
                                    <div class="text-center border p-2">
                                        <a href="<?php echo htmlspecialchars($pqrs['Evidencia']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-download"></i> Ver/Descargar Evidencia
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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


function aplicarFiltroPQRS() {
  const filtro = $("#filtroPQRS").val().toLowerCase().trim();
  const palabras = filtro.split(/\s+/).filter(p => p.length > 0); 

  const items = $("#listaPQRS .pqrs-item");

  items.each(function () {
    const item = $(this);
    
   
    const tipoElement = item.find(".tipo-pqrs");
    const clienteElement = item.find(".cliente-pqrs");
    const descElement = item.find(".descripcion-pqrs");

    
    const tipoOriginal = tipoElement.text();
    const clienteOriginal = clienteElement.text();
    const descOriginal = descElement.text();

   
    const textoCompleto = (tipoOriginal + " " + clienteOriginal + " " + descOriginal).toLowerCase();

  
    const coincide = palabras.every(p => textoCompleto.includes(p));

    if (coincide) {
      item.show();
      
     
      tipoElement.html(resaltar(tipoOriginal, palabras));
      clienteElement.html(resaltar(clienteOriginal, palabras));
      descElement.html(resaltar(descOriginal, palabras));
      
    } else {
      item.hide();
      
     
      tipoElement.text(tipoOriginal);
      clienteElement.text(clienteOriginal);
      descElement.text(descOriginal);
    }
  });
}

$("#filtroPQRS").on("input", aplicarFiltroPQRS);

$(document).ready(function() {
    aplicarFiltroPQRS(); 
});
</script>

</body>
</html>