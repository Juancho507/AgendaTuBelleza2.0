<?php
if (!isset($_SESSION)) {
    session_start();
}
if ($_SESSION["rol"] != "cliente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

$error = 0;
$id = $_SESSION["id"];
require_once "logica/Cliente.php"; 
$cliente = new Cliente($id);
$cliente->consultar();

if (isset($_POST["editar"])) {
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $correo = $_POST["correo"];
    $claveNueva = $_POST["contraseña"];
    $telefono = $_POST["telefono"];
    $estado = $_POST["estado"];
    
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
    $rutaServidor = $cliente->getFoto();
    
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
    
    if ($error == 0) {
        
        $claveFinal = $cliente->getContraseña();
        if (!empty($claveNueva)) {
            $claveFinal = md5($claveNueva);
        }
        
        
        if ($foto != "") {
            $nuevoNombre = time() . "." . $extension;
            $rutaServidor = "imagenes/" . $nuevoNombre;
            if (copy($rutaLocal, $rutaServidor)) {
                if ($cliente->getFoto() != "" && $cliente->getFoto() != $rutaServidor) {
                   
                    $rutaFotoAnterior = __DIR__ . "/../../" . $cliente->getFoto();
                    if (file_exists($rutaFotoAnterior) && strpos($cliente->getFoto(), 'imagenes/') === 0) {
                        unlink($rutaFotoAnterior);
                    }
                }
            } else {
                $error = 5;
            }
        }
        
        if ($error == 0) {
            try {
                $clienteActualizado = new Cliente(
                    $id,
                    $nombre,
                    $apellido,
                    $correo,
                    $claveFinal,
                    $telefono,
                    $cliente->getEstado(),
                    $cliente->getFechaRegistro(),
                    $cliente->getGerente(),
                    $rutaServidor
                    );
                $clienteActualizado->actualizar();
               
                $cliente = $clienteActualizado;
            } catch (Exception $e) {
                $error = 6;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil - Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php
include("presentacion/encabezadoC.php");
include("presentacion/menuCliente.php");
if (isset($_SESSION['error_eliminacion'])) {
    $mensaje_alerta = $_SESSION['error_eliminacion'];
    
    $clase_alerta = strpos($mensaje_alerta, '🚫') !== false ? 'alert-danger' : 'alert-warning';
    
    echo "<div class='container mt-4'><div class='row justify-content-center'><div class='col-md-6'><div class='alert {$clase_alerta} text-center'>{$mensaje_alerta}</div></div></div></div>";
    
    unset($_SESSION['error_eliminacion']);
}
?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mb-4"> 
                <div class="card-header bg-primary text-white">
                    <h4>Editar Perfil</h4>
                </div>
                <div class="card-body">
                    <?php
                    
                   
                    if (isset($_POST["editar"]) && $error == 0) {
                        echo "<div class='alert alert-success'>✅ Datos actualizados correctamente.</div>";
                    } elseif (isset($_POST["editar"]) && $error != 0) {
                        $mensaje = "Ocurrió un error al actualizar la información.";
                        switch ($error) {
                            case 1: $mensaje = "🛑 Todos los campos obligatorios deben estar llenos."; break;
                            case 2: $mensaje = "📧 El formato del correo electrónico es incorrecto."; break;
                            case 3: $mensaje = "📞 El campo Teléfono solo debe contener números."; break;
                            case 4: $mensaje = "🖼️ Error con la foto: el archivo debe ser JPG, PNG o GIF y no exceder los 5MB."; break;
                            case 5: $mensaje = "📂 Error al subir la foto al servidor (posibles problemas de permisos)."; break;
                            case 6: $mensaje = "💾 Error de base de datos al guardar la información."; break;
                            default: $mensaje = "Ocurrió un error desconocido. Intente nuevamente.";
                        }
                        echo "<div class='alert alert-danger'>{$mensaje}</div>";
                    }
                    ?>
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($cliente->getNombre()); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellido *</label>
                            <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($cliente->getApellido()); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo *</label>
                            <input type="email" name="correo" class="form-control" value="<?php echo htmlspecialchars($cliente->getCorreo()); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña (dejar en blanco si no deseas cambiarla)</label>
                            <input type="password" name="contraseña" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono *</label>
                            <input type="tel" name="telefono" class="form-control" value="<?php echo htmlspecialchars($cliente->getTelefono()); ?>" required pattern="[0-9]+" title="Ingrese solo números.">
                        </div>
                      	<div class="mb-3">
    						<label class="form-label">Estado de la Cuenta</label>
   							<input type="text" class="form-control" value="<?php echo $cliente->getEstado() == 1 ? 'Activo' : 'Inactivo (Acción administrativa)'; ?>" disabled>
    						<input type="hidden" name="estado" value="<?php echo $cliente->getEstado(); ?>"> 
						</div>

                        <div class="mb-3 text-center">
                            <?php
                            
                            if ($cliente->getFoto() != "" && file_exists($cliente->getFoto())) {
                                echo "<img src='" . $cliente->getFoto() . "' height='150' class='rounded-circle mb-2' />";
                            } else {
                                echo "<p>No hay foto actual.</p>";
                            }
                            ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Foto Nueva (Max 5MB, JPG/PNG/GIF)</label>
                            <input type="file" name="foto" class="form-control" accept=".jpg, .jpeg, .png, .gif">
                        </div>

                        <button type="submit" name="editar" class="btn btn-primary">Guardar Cambios</button>
                    </form>

                   <hr>
<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmarEliminarModal">
    Eliminar Cuenta
</button>

<form id="formEliminarCliente" method="post" action="?pid=<?php echo base64_encode("presentacion/cliente/eliminarCliente.php"); ?>">
    <input type="hidden" name="eliminar_confirmado" value="true">
</form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmarEliminarModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalLabel">⚠️ Confirmar Eliminación de Cuenta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>Estás a punto de inactivar tu cuenta permanentemente.</p>
                <p class="fw-bold text-danger">Esta acción es irreversible y solo se permite si NO tienes citas ACTIVAS o PENDIENTES.</p>
                <p>¿Estás seguro de continuar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                
                <button type="button" class="btn btn-danger" onclick="document.getElementById('formEliminarCliente').submit()">
                    Sí, Eliminar Cuenta
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>