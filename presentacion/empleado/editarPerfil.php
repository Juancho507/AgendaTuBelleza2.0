<?php
if (!isset($_SESSION)) {
    session_start();
}
if ($_SESSION["rol"] != "empleado") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

$error = 0;
$id = $_SESSION["id"];


$empleado = new Empleado($id);
$empleado->consultar();

$serviciosDisponibles = Servicio::consultarActivos(); 
$serviciosOfrecidos = $empleado->consultarServiciosOfrecidos();

if (isset($_POST["editar"])) {
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $correo = $_POST["correo"];
    $claveNueva = $_POST["contraseña"];
    $telefono = $_POST["telefono"];
    
    
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($telefono)) {
        $error = 1;
    }
    
    if ($error == 0 && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 2;
    }
    if ($error == 0 && !preg_match("/^[0-9]+$/", $telefono)) {
        $error = 3;
    }
    
    $foto = $_FILES["foto"]["name"];
    $rutaServidor = $empleado->getFoto();
   
    
    if ($foto != "") {
        $tam = $_FILES["foto"]["size"];
        $rutaLocal = $_FILES["foto"]["tmp_name"];
        $extension = pathinfo($foto, PATHINFO_EXTENSION);
        $extensionesPermitidas = array('jpg', 'jpeg', 'png', 'gif');
        $limiteTamano = 5 * 1024 * 1024;
        
        if ($error == 0 && (!in_array(strtolower($extension), $extensionesPermitidas) || $tam > $limiteTamano)) {
            $error = 4;
        }
    }
    
    $serviciosMarcados = isset($_POST["servicios"]) ? $_POST["servicios"] : [];
    
    if ($error == 0) {
        
        $claveFinal = $empleado->getContraseña();
        if (!empty($claveNueva)) {
            $claveFinal = md5($claveNueva);
        }
        
        if ($foto != "") {
            $nuevoNombre = time() . "." . $extension;
            $rutaServidor = "imagenes/" . $nuevoNombre;
            
            if (copy($rutaLocal, $rutaServidor)) {
                if ($empleado->getFoto() != "" && $empleado->getFoto() != $rutaServidor) {
                    $rutaFotoAnterior = __DIR__ . "/../../" . $empleado->getFoto();
                    if (file_exists($rutaFotoAnterior) && strpos($empleado->getFoto(), 'imagenes/') === 0) {
                        unlink($rutaFotoAnterior);
                    }
                }
            } else {
                $error = 5;
            }
        }
        
        if ($error == 0) {
            try {
                $empleadoActualizado = new Empleado(
                    $id,
                    $nombre,
                    $apellido,
                    $correo,
                    $claveFinal,
                    $telefono,
                    $empleado->getEstado(),
                    $empleado->getSalario(),
                    $empleado->getHorario(),
                    $empleado->getGerente(),
                    $rutaServidor
                    );
                
                $empleadoActualizado->actualizarPerfilCompleto();
                
                $empleadoActualizado->actualizarServiciosOfrecidos($serviciosMarcados);
              
                
                $empleado = $empleadoActualizado;
                
                $serviciosOfrecidos = $empleado->consultarServiciosOfrecidos();
                
            } catch (Exception $e) {
                $error = 6;
            }
        }
    }
}

$empleado->consultar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil - Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<?php
include("presentacion/encabezadoE.php");
include("presentacion/menuEmpleado.php");

?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mb-4 border-success shadow-lg"> 
                <div class="card-header bg-success text-white">
                    <h4><i class="fa-solid fa-user-tie"></i> Editar Mi Perfil</h4>
                </div>
                <div class="card-body">
                    <?php
                    
                    if (isset($_POST["editar"])) {
                        if ($error == 0) {
                            echo "<div class='alert alert-success'>✅ Datos y foto actualizados correctamente.</div>";
                        } else {
                            $mensaje = "Ocurrió un error al actualizar la información.";
                            switch ($error) {
                                case 1: $mensaje = "🛑 Todos los campos obligatorios deben estar llenos."; break;
                                case 2: $mensaje = "📧 El formato del correo electrónico es incorrecto."; break;
                                case 3: $mensaje = "📞 El campo Teléfono solo debe contener números."; break;
                                case 4: $mensaje = "🖼️ Error con la foto: el archivo debe ser JPG, PNG o GIF y no exceder los 5MB."; break;
                                case 5: $mensaje = "📂 Error al subir la foto al servidor (posibles problemas de permisos en la carpeta 'imagenes/')."; break;
                                case 6: $mensaje = "💾 Error de base de datos al guardar la información."; break;
                                default: $mensaje = "Ocurrió un error desconocido. Intente nuevamente.";
                            }
                            echo "<div class='alert alert-danger'>{$mensaje}</div>";
                        }
                    }
                    ?>
                    <form method="post" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre *</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($empleado->getNombre()); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Apellido *</label>
                            <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($empleado->getApellido()); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Correo *</label>
                            <input type="email" name="correo" class="form-control" value="<?php echo htmlspecialchars($empleado->getCorreo()); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nueva Contraseña (dejar en blanco si no deseas cambiarla)</label>
                            <input type="password" name="contraseña" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Teléfono *</label>
                            <input type="tel" name="telefono" class="form-control" value="<?php echo htmlspecialchars($empleado->getTelefono()); ?>" required pattern="[0-9]+" title="Ingrese solo números.">
                        </div>
                      	
                        <hr>
                        
                        <p class="fw-bold text-success mb-2"><i class="fa-solid fa-lock"></i> Información Administrativa (Solo Lectura):</p>
                        
                        <div class="mb-3">
    						<label class="form-label">Estado de la Cuenta</label>
   							<input type="text" class="form-control" value="<?php echo $empleado->getEstado() == 1 ? 'Activo' : 'Inactivo'; ?>" disabled>
						</div>
                        <div class="mb-3">
    						<label class="form-label">Salario</label>
   							<input type="text" class="form-control" value="$<?php echo number_format($empleado->getSalario(), 0, ',', '.'); ?>" disabled>
						</div>
                        <div class="mb-3">
    						<label class="form-label">Horario</label>
   							<input type="text" class="form-control" value="<?php echo htmlspecialchars($empleado->getHorario()); ?>" disabled>
						</div>
                        
                        <hr>
                        
                        <p class="fw-bold text-success mb-2"><i class="fa-solid fa-list-check"></i> Servicios que ofrezco:</p>

                        <div class="mb-3 border p-3 rounded bg-light">
                            <label class="form-label fw-bold mb-3">Marca los servicios que puedes realizar:</label>
                            
                            <div class="row">
                                <?php 
                                foreach ($serviciosDisponibles as $servicio) {
                                    $id = $servicio['idServicio'];
                                    $nombre = htmlspecialchars($servicio['nombre']);
                                    $checked = in_array($id, $serviciosOfrecidos) ? 'checked' : ''; 
                                ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="servicios[]" 
                                               value="<?php echo $id; ?>" id="servicio_<?php echo $id; ?>" <?php echo $checked; ?>>
                                        <label class="form-check-label" for="servicio_<?php echo $id; ?>">
                                            <?php echo $nombre; ?>
                                        </label>
                                    </div>
                                </div>
                                <?php
                                }
                                if (empty($serviciosDisponibles)) {
                                    echo "<p class='text-danger'>⚠️ No hay servicios activos para seleccionar.</p>";
                                }
                                ?>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3 text-center">
                            <?php                           
                            if ($empleado->getFoto() != "" && file_exists($empleado->getFoto())) {
                                echo "<img src='" . htmlspecialchars($empleado->getFoto()) . "' height='150' class='rounded-circle mb-2' />";
                            } else {
                                echo "<p>No hay foto actual.</p>";
                            }
                            ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subir Nueva Foto (Max 5MB, JPG/PNG/GIF)</label>
                            <input type="file" name="foto" class="form-control" accept=".jpg, .jpeg, .png, .gif">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="editar" class="btn btn-success">
                                <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                    
                    </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>