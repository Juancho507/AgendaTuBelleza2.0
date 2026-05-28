<?php
if (!isset($_SESSION)) {
    session_start();
}

if ($_SESSION["rol"] != "gerente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}


$idGerente = $_SESSION["id"];

$moduloActual = $_GET['modulo'] ?? 'citas';
$datosHistorial = [];
$estructuraTabla = [];
$tituloModulo = ucfirst($moduloActual);
$errorConsulta = false;
$mensajeError = '';

$mapaEstilos = [
    'citas'     => ['color' => 'primary', 'icon' => 'fa-calendar-check'], 
    'clientes'  => ['color' => 'success', 'icon' => 'fa-users'],      
    'empleados' => ['color' => 'warning', 'icon' => 'fa-user-tie'],      
    'pqrs'      => ['color' => 'danger', 'icon' => 'fa-circle-question'], 
    'productos' => ['color' => 'info', 'icon' => 'fa-box-open'],      
    'servicios' => ['color' => 'dark', 'icon' => 'fa-scissors'],       
    'agenda'    => ['color' => 'secondary', 'icon' => 'fa-list-check'],  
];

$estiloActual = $mapaEstilos[$moduloActual] ?? $mapaEstilos['citas']; 
$claseColor = $estiloActual['color'];
$claseIcono = $estiloActual['icon'];


function obtenerDatosDelModulo($modulo) {
    global $errorConsulta, $mensajeError;
    $dao = null;
    $query = '';
    $datos = [];
    $estructura = [];
    $registrosPreparados = [];
    
    $conexion = null;
    
   
    
    try {
        switch ($modulo) {
            case 'citas':
                $datos = Cita::obtenerTodasLasCitas();
                
                $estructura = [
                    ['campo' => 'idCita', 'titulo' => '#'],
                    ['campo' => 'Fecha', 'titulo' => 'Fecha'],
                    ['campo' => 'HoraInicio', 'titulo' => 'Hora'],
                    ['campo' => 'cliente', 'titulo' => 'Cliente'],
                    ['campo' => 'empleado', 'titulo' => 'Empleado'],
                    ['campo' => 'estado', 'titulo' => 'Estado']
                ];
                
                foreach ($datos as $registro) {
                    $registro['detalleHTML'] = generarDetalleHTML($registro, $modulo);
                    $registrosPreparados[] = $registro;
                }
                
                return ['datos' => $registrosPreparados, 'estructura' => $estructura];
                break;
                
            case 'clientes':
                $datos = Cliente::obtenerTodosLosClientes();
                
                $estructura = [
                    ['campo' => 'idCliente', 'titulo' => '#'],
                    ['campo' => 'nombre', 'titulo' => 'Nombre'],
                    ['campo' => 'apellido', 'titulo' => 'Apellido'],
                    ['campo' => 'correo', 'titulo' => 'Correo'],
                    ['campo' => 'telefono', 'titulo' => 'Teléfono'],
                    ['campo' => 'estado', 'titulo' => 'Estado'],
                    ['campo' => 'fecha_registro', 'titulo' => 'Registro']
                ];
                
                $registrosPreparados = [];
                foreach ($datos as $registro) {
                    $registro['detalleHTML'] = generarDetalleHTML($registro, $modulo);
                    $registrosPreparados[] = $registro;
                }
                
                return ['datos' => $registrosPreparados, 'estructura' => $estructura];
                break;
                
            case 'empleados':
                $datos = Empleado::obtenerTodosLosEmpleados();
                
                $estructura = [
                    ['campo' => 'idEmpleado', 'titulo' => '#'],
                    ['campo' => 'nombre', 'titulo' => 'Nombre'],
                    ['campo' => 'apellido', 'titulo' => 'Apellido'],
                    ['campo' => 'correo', 'titulo' => 'Correo'],
                    ['campo' => 'telefono', 'titulo' => 'Teléfono'],
                    ['campo' => 'estado', 'titulo' => 'Estado']
                ];
                
                $registrosPreparados = [];
                foreach ($datos as $registro) {
                    $registro['detalleHTML'] = generarDetalleHTML($registro, $modulo);
                    $registrosPreparados[] = $registro;
                }
                
                return ['datos' => $registrosPreparados, 'estructura' => $estructura];
                break;
                
                
            case 'pqrs':
                $datos = PQRS::obtenerTodasLasPQRS();
                
                $estructura = [
                    ['campo' => 'idPQRS', 'titulo' => '#'],
                    ['campo' => 'Fecha', 'titulo' => 'Fecha'],
                    ['campo' => 'tipo_pqrs', 'titulo' => 'Tipo'],
                    ['campo' => 'cliente', 'titulo' => 'Cliente'],
                    ['campo' => 'empleado_asociado', 'titulo' => 'Empleado/NA'],
                    ['campo' => 'Descripcion', 'titulo' => 'Descripción (corta)']
                ];
                
                $registrosPreparados = [];
                foreach ($datos as $registro) {
                    $registro['Descripcion'] = substr($registro['Descripcion'] ?? '', 0, 40) . (strlen($registro['Descripcion'] ?? '') > 40 ? '...' : '');
                    $registro['detalleHTML'] = generarDetalleHTML($registro, $modulo);
                    $registrosPreparados[] = $registro;
                }
                
                return ['datos' => $registrosPreparados, 'estructura' => $estructura];
                break;
                
            case 'productos':
                $datos = Producto::obtenerTodosLosProductos();
                
                $estructura = [
                    ['campo' => 'idProducto', 'titulo' => '#'],
                    ['campo' => 'nombre', 'titulo' => 'Nombre'],
                    ['campo' => 'descripcion', 'titulo' => 'Descripcion'],
                    ['campo' => 'cantidad', 'titulo' => 'Cantidad']
                    
                ];
                
                $registrosPreparados = [];
                foreach ($datos as $registro) {
                    $registro['detalleHTML'] = generarDetalleHTML($registro, $modulo);
                    $registrosPreparados[] = $registro;
                }
                
                return ['datos' => $registrosPreparados, 'estructura' => $estructura];
                break;
                
            case 'servicios':
                $datos = Servicio::obtenerTodosLosServicios();
                
                $estructura = [
                    ['campo' => 'idServicio', 'titulo' => '#'],
                    ['campo' => 'nombre', 'titulo' => 'Nombre'],
                    ['campo' => 'precio', 'titulo' => 'Precio'],
                    ['campo' => 'producto_asociado', 'titulo' => 'Producto Base'],
                    ['campo' => 'estado', 'titulo' => 'Estado']
                ];
                
                $registrosPreparados = [];
                foreach ($datos as $registro) {
                    $registro['detalleHTML'] = generarDetalleHTML($registro, $modulo);
                    $registrosPreparados[] = $registro;
                }
                
                return ['datos' => $registrosPreparados, 'estructura' => $estructura];
                break;
                
            case 'agenda':
                $datos = Agenda::obtenerAgendaCompleta();
                
                $estructura = [
                    ['campo' => 'idAgenda', 'titulo' => '#'],
                    ['campo' => 'fecha', 'titulo' => 'Fecha'],
                    ['campo' => 'hora_inicio', 'titulo' => 'Inicio'],
                    ['campo' => 'empleado', 'titulo' => 'Empleado'],
                    ['campo' => 'servicio', 'titulo' => 'Servicio'],
                    ['campo' => 'estado_agenda', 'titulo' => 'Estado']
                ];
                
                $registrosPreparados = [];
                foreach ($datos as $registro) {
                    $registro['detalleHTML'] = generarDetalleHTML($registro, $modulo);
                    $registrosPreparados[] = $registro;
                }
                
                return ['datos' => $registrosPreparados, 'estructura' => $estructura];
                break;
                
            default:
                throw new Exception("Módulo no válido.");
        }
        
    } catch (Exception $e) {
        
        $errorConsulta = true;
        $mensajeError = 'Error de BD al consultar ' . $modulo . ': ' . $e->getMessage();
        return ['datos' => [], 'estructura' => []];
    }
    
    return ['datos' => [], 'estructura' => []];
}

function generarDetalleHTML($registro, $modulo) {
    $html = '<dl class="row">';
    foreach ($registro as $clave => $valor) {
        if (!is_numeric($clave) && $clave !== 'detalleHTML' && strlen($valor) < 100) {
            
            $titulo = str_replace(['id', '_'], ['', ' '], ucfirst($clave));
            $titulo = trim($titulo);
            
            $html .= "<dt class='col-sm-3'><strong>" . htmlspecialchars($titulo) . ":</strong></dt>";
            $html .= "<dd class='col-sm-9'>" . htmlspecialchars($valor) . "</dd>";
        }
    }
    $html .= '</dl>';
    return $html;
}

$resultado = obtenerDatosDelModulo($moduloActual);
$datosHistorial = $resultado['datos'];
$estructuraTabla = $resultado['estructura'];
$tituloModulo = ucfirst($moduloActual);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial General - Gerente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
</head>

<body>
<?php
include("presentacion/encabezadoG.php");
include("presentacion/menuGerente.php");
?>

<div class="container mt-4">

    <h2><i class="fa-solid <?php echo $claseIcono; ?>"></i> Historial General de <?php echo $tituloModulo; ?></h2>
    <p class="text-muted">Consulta todo el historial por módulo.</p>
    <hr>

    <ul class="nav nav-tabs" id="modulosTabs">
        <li class="nav-item">
            <a class="nav-link <?php echo ($moduloActual == 'citas') ? 'active text-primary border-primary' : 'text-primary'; ?>" href="?pid=<?php echo base64_encode("presentacion/gerente/historialGerente.php"); ?>&modulo=citas">Citas</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($moduloActual == 'clientes') ? 'active text-success border-success' : 'text-success'; ?>" href="?pid=<?php echo base64_encode("presentacion/gerente/historialGerente.php"); ?>&modulo=clientes">Clientes</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($moduloActual == 'empleados') ? 'active text-warning border-warning' : 'text-warning'; ?>" href="?pid=<?php echo base64_encode("presentacion/gerente/historialGerente.php"); ?>&modulo=empleados">Empleados</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($moduloActual == 'pqrs') ? 'active text-danger border-danger' : 'text-danger'; ?>" href="?pid=<?php echo base64_encode("presentacion/gerente/historialGerente.php"); ?>&modulo=pqrs">PQRS</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($moduloActual == 'productos') ? 'active text-info border-info' : 'text-info'; ?>" href="?pid=<?php echo base64_encode("presentacion/gerente/historialGerente.php"); ?>&modulo=productos">Productos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($moduloActual == 'servicios') ? 'active text-dark border-dark' : 'text-dark'; ?>" href="?pid=<?php echo base64_encode("presentacion/gerente/historialGerente.php"); ?>&modulo=servicios">Servicios</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($moduloActual == 'agenda') ? 'active text-secondary border-secondary' : 'text-secondary'; ?>" href="?pid=<?php echo base64_encode("presentacion/gerente/historialGerente.php"); ?>&modulo=agenda">Agenda</a>
        </li>
    </ul>

    <div class="card shadow mt-3 border-<?php echo $claseColor; ?>">
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-10">
                    <input type="text" id="filtroGeneral" class="form-control" placeholder="Buscar en el historial de <?php echo $tituloModulo; ?>...">
                </div>
                <div class="col-md-2">
                    <a id="btnPDF" href="presentacion/gerente/reportepdf.php?modulo=<?php echo $moduloActual; ?>" target="_blank" class="btn btn-<?php echo $claseColor; ?> w-100">
                        <i class="fa-solid fa-file-pdf"></i> Exportar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="contenedorResultados" class="mt-3">
        <?php if ($errorConsulta): ?>
            <div class="alert alert-danger text-center">
                <i class="fa-solid fa-server"></i> Error de Consulta en <?php echo $tituloModulo; ?>: <?php echo htmlspecialchars($mensajeError); ?>
            </div>
        <?php elseif (count($datosHistorial) == 0): ?>
            <div class="alert alert-info text-center">
                <i class="fa-solid fa-circle-info"></i> No hay registros disponibles para <?php echo $tituloModulo; ?>.
            </div>
        <?php else: ?>
            <h4>Resultados (<?php echo count($datosHistorial); ?>)</h4>
            <div class="table-responsive shadow-sm">
                <table class="table table-hover table-striped" id="tablaGeneral">
                    <thead class="table-<?php echo $claseColor; ?> text-white">
                        <tr>
                            <?php foreach ($estructuraTabla as $columna): ?>
                                <th scope="col"><?php echo htmlspecialchars($columna['titulo']); ?></th>
                            <?php endforeach; ?>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($datosHistorial as $index => $item): 
                            $textoBusqueda = implode(' ', array_map(function($col) use ($item) {
                                return $item[$col['campo']] ?? '';
                            }, $estructuraTabla));
                            
                            $idRegistro = $item[$estructuraTabla[0]['campo']] ?? $index;
                        ?>
                        <tr class="general-item" 
                            data-search="<?php echo strtolower(htmlspecialchars($textoBusqueda)); ?>"
                            data-bs-toggle="modal" 
                            data-bs-target="#modal_<?php echo $moduloActual; ?>_<?php echo $idRegistro; ?>">
                            
                            <?php foreach ($estructuraTabla as $columna): ?>
                                <td><?php echo htmlspecialchars($item[$columna['campo']] ?? '-'); ?></td>
                            <?php endforeach; ?>
                            
                            <td>
                                <button class="btn btn-sm btn-<?php echo $claseColor; ?>" type="button"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modal_<?php echo $moduloActual; ?>_<?php echo $idRegistro; ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="modal_<?php echo $moduloActual; ?>_<?php echo $idRegistro; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-<?php echo $claseColor; ?> text-white">
                                        <h5 class="modal-title">Detalle de <?php echo $tituloModulo; ?> #<?php echo htmlspecialchars($idRegistro); ?></h5>
                                        <button class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php echo $item['detalleHTML']; ?> 
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$("#filtroGeneral").on("input", function() {
    const texto = $(this).val().toLowerCase();

    $(".general-item").each(function() {
        const textoFila = $(this).data("search");
        const coincide = textoFila && textoFila.includes(texto);
        
        $(this).toggle(coincide);
    });
});
</script>

</body>
</html>