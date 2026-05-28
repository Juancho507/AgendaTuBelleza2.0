<?php
ob_start();

if (!isset($_SESSION)) {
    session_start();
}
require_once(__DIR__ . "/../../fpdf/fpdf.php");
require_once(__DIR__ . "/../../logica/Cita.php");
require_once(__DIR__ . "/../../logica/Cliente.php");
require_once(__DIR__ . "/../../logica/Empleado.php");
require_once(__DIR__ . "/../../logica/PQRS.php");
require_once(__DIR__ . "/../../logica/Producto.php");
require_once(__DIR__ . "/../../logica/Servicio.php");
require_once(__DIR__ . "/../../logica/Agenda.php");


if ($_SESSION["rol"] != "gerente") {
    die("Acceso no autorizado.");
}

$modulo = $_GET['modulo'] ?? null;

if (!$modulo) {
    die("Módulo de reporte no especificado.");
}

$datos_list = [];
$titulo_reporte = ucfirst($modulo);
$columnas = [];
$orientacion = 'L';
$ancho_pagina = ($orientacion == 'L') ? 277 : 190;
$celda_altura = 6;

try {
    switch ($modulo) {
        case 'citas':
            $datos_list = Cita::obtenerTodasLasCitas();
            $titulo_reporte = "Historial General de Citas";
            $columnas = [
                ['titulo' => '#', 'campo' => 'idCita', 'ancho' => 15, 'align' => 'C'],
                ['titulo' => 'Fecha', 'campo' => 'Fecha', 'ancho' => 25, 'align' => 'C'],
                ['titulo' => 'Hora', 'campo' => 'HoraInicio', 'ancho' => 20, 'align' => 'C'],
                ['titulo' => 'Cliente', 'campo' => 'cliente', 'ancho' => 60, 'align' => 'L'],
                ['titulo' => 'Empleado', 'campo' => 'empleado', 'ancho' => 60, 'align' => 'L'],
                ['titulo' => 'Servicio', 'campo' => 'servicio', 'ancho' => 60, 'align' => 'L', 'limite' => 35], 
                ['titulo' => 'Estado', 'campo' => 'estado', 'ancho' => 37, 'align' => 'C'],
            ];
            break;
            
        case 'clientes':
            $datos_list = Cliente::obtenerTodosLosClientes();
            $titulo_reporte = "Historial General de Clientes";
            $columnas = [
                ['titulo' => '#', 'campo' => 'idCliente', 'ancho' => 15, 'align' => 'C'],
                ['titulo' => 'Nombre Completo', 'campo' => 'nombre_completo', 'ancho' => 80, 'align' => 'L'],
                ['titulo' => 'Correo', 'campo' => 'correo', 'ancho' => 70, 'align' => 'L'],
                ['titulo' => 'Teléfono', 'campo' => 'telefono', 'ancho' => 35, 'align' => 'C'],
                ['titulo' => 'Estado', 'campo' => 'estado', 'ancho' => 35, 'align' => 'C'],
                ['titulo' => 'Registro', 'campo' => 'fecha_registro', 'ancho' => 42, 'align' => 'C'],
            ];
            break;
            
        case 'empleados':
            $datos_list = Empleado::obtenerTodosLosEmpleados();
            $titulo_reporte = "Historial General de Empleados";
            $columnas = [
                ['titulo' => '#', 'campo' => 'idEmpleado', 'ancho' => 15, 'align' => 'C'],
                ['titulo' => 'Nombre Completo', 'campo' => 'nombre_completo', 'ancho' => 70, 'align' => 'L'],
                ['titulo' => 'Correo', 'campo' => 'correo', 'ancho' => 60, 'align' => 'L'],
                ['titulo' => 'Teléfono', 'campo' => 'telefono', 'ancho' => 35, 'align' => 'C'],
                ['titulo' => 'Salario', 'campo' => 'salario', 'ancho' => 30, 'align' => 'R'],
                ['titulo' => 'Horario', 'campo' => 'horario', 'ancho' => 30, 'align' => 'C'],
                ['titulo' => 'Estado', 'campo' => 'estado', 'ancho' => 37, 'align' => 'C'],
            ];
            break;
            
        case 'pqrs':
            $datos_list = PQRS::obtenerTodasLasPQRS();
            $titulo_reporte = "Historial General de PQRS";
            $columnas = [
                ['titulo' => '#', 'campo' => 'idPQRS', 'ancho' => 15, 'align' => 'C'],
                ['titulo' => 'Fecha', 'campo' => 'Fecha', 'ancho' => 35, 'align' => 'C'],
                ['titulo' => 'Tipo', 'campo' => 'tipo_pqrs', 'ancho' => 25, 'align' => 'L'],
                ['titulo' => 'Cliente', 'campo' => 'cliente', 'ancho' => 50, 'align' => 'L'],
                ['titulo' => 'Empleado Asoc.', 'campo' => 'empleado_asociado', 'ancho' => 50, 'align' => 'L'],
                ['titulo' => 'Descripción', 'campo' => 'Descripcion', 'ancho' => 102, 'align' => 'L', 'limite' => 60], // Se aplica límite
            ];
            break;
            
        case 'productos':
            $orientacion = 'P';
            $ancho_pagina = 190;
            $datos_list = Producto::obtenerTodosLosProductos();
            $titulo_reporte = "Historial General de Productos";
            $columnas = [
                ['titulo' => '#', 'campo' => 'idProducto', 'ancho' => 15, 'align' => 'C'],
                ['titulo' => 'Nombre', 'campo' => 'nombre', 'ancho' => 50, 'align' => 'L'],
                ['titulo' => 'Descripción', 'campo' => 'descripcion', 'ancho' => 90, 'align' => 'L', 'limite' => 50], // Se aplica límite
                ['titulo' => 'Cantidad', 'campo' => 'cantidad', 'ancho' => 35, 'align' => 'C'],
            ];
            break;
            
        case 'servicios':
            $datos_list = Servicio::obtenerTodosLosServicios();
            $titulo_reporte = "Historial General de Servicios";
            $columnas = [
                ['titulo' => '#', 'campo' => 'idServicio', 'ancho' => 15, 'align' => 'C'],
                ['titulo' => 'Nombre', 'campo' => 'nombre', 'ancho' => 60, 'align' => 'L'],
                ['titulo' => 'Descripción', 'campo' => 'descripcion', 'ancho' => 90, 'align' => 'L', 'limite' => 50], // Se aplica límite
                ['titulo' => 'Precio', 'campo' => 'precio', 'ancho' => 30, 'align' => 'R'],
                ['titulo' => 'Producto Base', 'campo' => 'producto_asociado', 'ancho' => 52, 'align' => 'L'],
                ['titulo' => 'Estado', 'campo' => 'estado', 'ancho' => 30, 'align' => 'C'],
            ];
            break;
            
        case 'agenda':
            $datos_list = Agenda::obtenerAgendaCompleta();
            $titulo_reporte = "Historial General de Agenda";
            $columnas = [
                ['titulo' => '#', 'campo' => 'idAgenda', 'ancho' => 15, 'align' => 'C'],
                ['titulo' => 'Fecha', 'campo' => 'fecha', 'ancho' => 30, 'align' => 'C'],
                ['titulo' => 'Inicio', 'campo' => 'hora_inicio', 'ancho' => 20, 'align' => 'C'],
                ['titulo' => 'Fin', 'campo' => 'hora_fin', 'ancho' => 20, 'align' => 'C'],
                ['titulo' => 'Empleado', 'campo' => 'empleado', 'ancho' => 70, 'align' => 'L'],
                ['titulo' => 'Servicio', 'campo' => 'servicio', 'ancho' => 70, 'align' => 'L'],
                ['titulo' => 'Estado', 'campo' => 'estado_agenda', 'ancho' => 52, 'align' => 'C'],
            ];
            break;
            
        default:
            die("Módulo '" . htmlspecialchars($modulo) . "' no es un tipo de reporte válido.");
    }
    
} catch (Exception $e) {
    die("Error al consultar la base de datos para el reporte de {$modulo}: " . $e->getMessage());
}


$pdf = new FPDF();
$pdf->AddPage($orientacion);
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 15);

$pdf->SetY(10);
$pdf->SetFont("Arial", "B", 20);
$pdf->SetTextColor(30, 30, 100);
$pdf->Cell(0, 15, utf8_decode($titulo_reporte), 0, 1, "C");

$pdf->Ln(5);
$pdf->SetFont("Arial", "I", 12);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 7, utf8_decode("Generado por: Gerente"), 0, 1, "L");
$pdf->Cell(0, 7, utf8_decode("Generado el: ") . date("Y-m-d H:i:s"), 0, 1, "L");
$pdf->Cell(0, 7, utf8_decode("Total de Registros Encontrados: ") . count($datos_list), 0, 1, "L");

$pdf->Ln(10);

$pdf->SetFont("Arial", "B", 14);
$pdf->SetFillColor(150, 180, 255);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 10, utf8_decode(" Listado Detallado"), 0, 1, "L", true);
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0);

if (empty($datos_list)) {
    $pdf->SetFont("Arial", "I", 12);
    $pdf->Cell(0, 10, utf8_decode("No se encontraron registros para el módulo '") . utf8_decode($modulo) . utf8_decode("'."), 0, 1, "C");
} else {
    
    $anchoTotal = array_sum(array_column($columnas, 'ancho'));
    $diferencia = $ancho_pagina - $anchoTotal;
    $ajuste_por_columna = $diferencia / count($columnas);
    
    $pdf->SetFont("Arial", "B", 10);
    $pdf->SetFillColor(230, 230, 250);
    
    foreach ($columnas as $col) {
        $ancho_ajustado = $col['ancho'] + $ajuste_por_columna;
        $pdf->Cell($ancho_ajustado, 8, utf8_decode($col['titulo']), 1, 0, 'C', true);
    }
    $pdf->Ln();
    
    $pdf->SetFont("Arial", "", 9);
    $fill = false;
    
    foreach ($datos_list as $item) {
        $pdf->SetFillColor(245, 245, 245);
        
        foreach ($columnas as $col) {
            $ancho_ajustado = $col['ancho'] + $ajuste_por_columna;
            $valor = $item[$col['campo']] ?? 'N/A';
            $limite = $col['limite'] ?? null;
            
            if ($modulo === 'clientes' && $col['campo'] === 'nombre_completo') {
                $valor = ($item['nombre'] ?? '') . ' ' . ($item['apellido'] ?? '');
            }
            if ($modulo === 'empleados' && $col['campo'] === 'nombre_completo') {
                $valor = ($item['nombre'] ?? '') . ' ' . ($item['apellido'] ?? '');
            }
           
            if ($modulo === 'productos' && $col['campo'] === 'cantidad') {
                $valor = $item['cantidad'] ?? $item['stock'] ?? 'N/A';
            }
            if ($limite) {
                if (strlen($valor) > $limite) {
                    $valor = substr($valor, 0, $limite) . '...';
                }
            }
            
            $pdf->Cell($ancho_ajustado, $celda_altura, utf8_decode($valor), 1, 0, $col['align'], $fill);
        }
        
        $pdf->Ln();
        $fill = !$fill;
    }
}

ob_end_clean();
$pdf->Output("I", "Reporte_" . $modulo . "_" . date("Ymd") . ".pdf");
exit();
?>