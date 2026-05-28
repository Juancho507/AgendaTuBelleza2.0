<?php

if (!isset($_SESSION)) {
    session_start();
}
if ($_SESSION["rol"] != "cliente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

require_once("logica/PQRS.php");
require_once("logica/Empleado.php");

$idCliente = $_SESSION["id"];
$exito = false;
$error = false;
$mensajeError = ""; 

$tiposPQRS = PQRS::consultarTiposPQRS();
$empleados = Empleado::consultarTodos();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['registrarPQRS'])) {
    
    $descripcion = $_POST['descripcion'];
    $tipo = $_POST['tipoPQRS'];
    $empleadoId = empty($_POST['empleado']) ? NULL : $_POST['empleado'];
    $rutaServidorEvidencia = NULL; 
    if (isset($_FILES['evidencia']) && $_FILES['evidencia']['error'] === 0) {
        
        $foto = $_FILES["evidencia"]["name"];
        $rutaLocal = $_FILES["evidencia"]["tmp_name"];
        $extension = pathinfo($foto, PATHINFO_EXTENSION);
        $tam = $_FILES["evidencia"]["size"];
        
        $extensionesPermitidas = array('jpg', 'jpeg', 'png', 'gif');
        $limiteTamano = 5 * 1024 * 1024; 
        if (!in_array(strtolower($extension), $extensionesPermitidas) || $tam > $limiteTamano) {
            $error = true;
            $mensajeError = "🖼️ Error con el archivo: debe ser JPG, PNG o GIF y no exceder los 5MB.";
        }
        
        if (!$error) {
            $nuevoNombre = time() . "_" . $idCliente . "." . $extension;
            $carpetaDestino = "imagenes/";
            $rutaServidorEvidencia = $carpetaDestino . $nuevoNombre;
            
            if (!copy($rutaLocal, $rutaServidorEvidencia)) {
                $error = true;
                $mensajeError = "📂 Error al subir el archivo de evidencia. Verifique los permisos de la carpeta 'evidencias/'.";
            }
        }
    }
  
    if (!$error) {
        $pqrs = new PQRS(
            "",
            descripcion: $descripcion,
            fecha: "",
            tipoPQRS: $tipo,
            cliente: $idCliente,
            gerente: 1,
            empleado: $empleadoId,
            evidencia: $rutaServidorEvidencia 
            );
        
        try {
            $pqrs->registrar();
            $exito = true;
        } catch (Exception $e) {
            $error = true;
            $mensajeError = "💾 Error de base de datos al guardar el registro.";
           
        }
    }
}
?>

<?php 
include("presentacion/encabezadoC.php"); 
include("presentacion/menuCliente.php"); 
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-danger text-white">
            <h4 class="mb-0">Registrar Petición, Queja, Reclamo o Sugerencia (PQRS)</h4>
        </div>
        <div class="card-body">
            
            <?php 
            if ($exito): 
                echo "<div class='alert alert-success text-center'>✅ PQRS registrado con éxito. Pronto te contactaremos.</div>";
            elseif ($error): 
                $msg = $mensajeError ?: "❌ Error al registrar PQRS. Inténtalo de nuevo.";
                echo "<div class='alert alert-danger text-center'>{$msg}</div>";
            endif; 
            ?>

            <div class="alert alert-info small">
                <p class="fw-bold mb-1">¿Qué es una PQRS?</p>
                <ul>
                    <li><strong>Petición (P):</strong> Solicitud de información, consulta o requerimiento.</li>
                    <li><strong>Queja (Q):</strong> Expresión de insatisfacción respecto al servicio o la atención.</li>
                    <li><strong>Reclamo (R):</strong> Oposición o controversia respecto a un hecho particular (ej. cobro, daño).</li>
                    <li><strong>Sugerencia (S):</strong> Propuesta para mejorar el servicio o procesos.</li>
                </ul>
            </div>
            
            <form method="POST" enctype="multipart/form-data"> 
                
                <div class="mb-3">
                    <label for="tipoPQRS" class="form-label">Tipo de Solicitud *</label>
                    <select class="form-select" id="tipoPQRS" name="tipoPQRS" required>
                        <option value="">Selecciona el tipo...</option>
                        <?php foreach ($tiposPQRS as $id => $nombre): ?>
                            <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($nombre); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="empleado" class="form-label">Empleado Relacionado (Opcional)</label>
                    <select class="form-select" id="empleado" name="empleado">
                        <option value="">--- Ningún Empleado (General) ---</option>
                        <?php foreach ($empleados as $e): ?>
                            <option value="<?php echo $e['idEmpleado']; ?>"><?php echo htmlspecialchars($e['NombreCompleto']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Detalle (Descripción) *</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="evidencia" class="form-label">Evidencia (Foto Opcional - Max 5MB, JPG/PNG/GIF)</label>
                    <input type="file" class="form-control" id="evidencia" name="evidencia" accept="image/*">
                </div>
                
                <p class="text-muted small">Tu solicitud será asignada automáticamente al Gerente Principal para su seguimiento.</p>

                <button type="submit" name="registrarPQRS" class="btn btn-danger w-100 mt-3">Enviar PQRS</button>
            </form>
        </div>
    </div>
</div>