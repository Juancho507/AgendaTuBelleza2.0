<?php
if (!isset($_SESSION)) {
    session_start();
}
if ($_SESSION["rol"] != "gerente") {
    header("Location: ?pid=" . base64_encode("presentacion/noAutorizado.php"));
    exit();
}

$error = 0;
$id = $_SESSION["id"];

require_once "logica/Gerente.php";
$gerente = new Gerente($id);
$gerente->consultar();

if (isset($_POST["editar"])) {
    
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $correo = $_POST["correo"];
    $telefono = $_POST["telefono"];
    $claveNueva = $_POST["contraseña"];
    
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($telefono)) {
        $error = 1;
    }
    
    if ($error == 0 && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 2;
    }
    
    if ($error == 0 && !preg_match("/^[0-9]+$/", $telefono)) {
        $error = 3;
    }
    
    if ($error == 0) {
        
        $claveFinal = $gerente->getContraseña();
        if (!empty($claveNueva)) {
            $claveFinal = md5($claveNueva);
        }
        
        try {
            $gerenteActualizado = new Gerente(
                $id,
                $nombre,
                $apellido,
                $correo,
                $claveFinal,
                $telefono
                );
            
            $gerenteActualizado->actualizar();
            $gerente = $gerenteActualizado;
            
        } catch (Exception $e) {
            $error = 4;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil - Gerente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<?php
include("presentacion/encabezadoG.php");
include("presentacion/menuGerente.php");
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card mb-4 bg-dark text-white"> 
                <div class="card-header bg-secondary text-white">
                    <h4>Editar Perfil del Gerente</h4>
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
                            case 4: $mensaje = "💾 Error de base de datos al guardar la información."; break;
                        }

                        echo "<div class='alert alert-danger'>{$mensaje}</div>";
                    }
                    ?>

                   <form method="post" onsubmit="return confirmarCambios();">
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" class="form-control"
                                value="<?php echo htmlspecialchars($gerente->getNombre()); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Apellido *</label>
                            <input type="text" name="apellido" class="form-control"
                                value="<?php echo htmlspecialchars($gerente->getApellido()); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Correo *</label>
                            <input type="email" name="correo" class="form-control"
                                value="<?php echo htmlspecialchars($gerente->getCorreo()); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña (opcional)</label>
                            <input type="password" name="contraseña" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono *</label>
                            <input type="tel" name="telefono" class="form-control"
                                value="<?php echo htmlspecialchars($gerente->getTelefono()); ?>"
                                required pattern="[0-9]+" title="Ingrese solo números.">
                        </div>

                        <button type="submit" name="editar" class="btn btn-primary">Guardar Cambios</button>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
<script>
function confirmarCambios() {
    return confirm("¿Estás seguro de que deseas guardar los cambios?");
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
