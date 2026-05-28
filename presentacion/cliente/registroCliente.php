<?php
$exito = false;
$error = false;
$correoDuplicado = false;
$errorEnSubidaFoto = false;
$errorValidacion = 0; 
$mensaje = "";
$claseMensaje = "";
$fotoRuta = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $correo = $_POST["correo"];
    $contraseña = $_POST["contraseña"];
    $telefono = $_POST["telefono"];
    $estado = $_POST["estado"] ?? '1'; 
    $fechaRegistro = date("Y-m-d H:i:s");

    if (empty($nombre) || empty($apellido) || empty($correo) || empty($contraseña) || empty($telefono) || empty($estado)) {
        $errorValidacion = 1;
    }
    
    if ($errorValidacion == 0 && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errorValidacion = 2;
    }
    
    if ($errorValidacion == 0 && !preg_match("/^[0-9]+$/", $telefono)) {
        $errorValidacion = 3;
    }
    
    if ($errorValidacion == 0 && strlen($contraseña) < 6) {
        $errorValidacion = 4;
    }
    
    $extensionesPermitidas = array('jpg', 'jpeg', 'png', 'gif');
    
    if ($errorValidacion == 0) {
        if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] === UPLOAD_ERR_OK) {
            $nombreFotoOriginal = $_FILES["foto"]["name"];
            $rutaTemporal = $_FILES["foto"]["tmp_name"];
            $extension = pathinfo($nombreFotoOriginal, PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($extension), $extensionesPermitidas)) {
                $mensaje = "Formato de imagen no permitido. Solo se aceptan imágenes JPG, PNG o GIF.";
                $claseMensaje = "alert-danger";
                $errorEnSubidaFoto = true;
            } else {
                $nuevoNombreFoto = time() . "." . strtolower($extension);
                $directorioDestino = "imagenes/";
                $rutaServidor = $directorioDestino . $nuevoNombreFoto;
                
                if (move_uploaded_file($rutaTemporal, $rutaServidor)) {
                    $fotoRuta = $rutaServidor;
                } else {
                    $mensaje = "Error al mover el archivo de la foto al servidor (verifique permisos de la carpeta 'imagenes').";
                    $claseMensaje = "alert-danger";
                    $errorEnSubidaFoto = true;
                }
            }
        } else if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] != UPLOAD_ERR_NO_FILE) {
            $mensaje = "Error en la subida de la foto (código: " . $_FILES["foto"]["error"] . ").";
            $claseMensaje = "alert-danger";
            $errorEnSubidaFoto = true;
        }
    }
    
    if ($errorValidacion == 0 && !$errorEnSubidaFoto) {
        require_once "logica/Cliente.php";
        $cliente = new Cliente("", $nombre, $apellido, $correo, $contraseña, $telefono, $estado, $fechaRegistro, $fotoRuta);
        
        if ($cliente->correoExiste()) {
            $correoDuplicado = true;
        } else {
            try {
                $cliente->registrar();
                $exito = true;
                $_POST = [];
            } catch (Exception $e) {
                $error = true;
                if ($fotoRuta != "" && file_exists($fotoRuta)) {
                    unlink($fotoRuta);
                }
                $mensaje = "Error de la DB: " . $e->getMessage();
                $claseMensaje = "alert-danger";
            }
        }
    }
    
    if ($errorValidacion != 0) {
        $claseMensaje = "alert-danger";
        switch ($errorValidacion) {
            case 1:
                $mensaje = "🛑 Todos los campos obligatorios deben estar llenos.";
                break;
            case 2:
                $mensaje = "📧 El formato del correo electrónico es incorrecto.";
                break;
            case 3:
                $mensaje = "📞 El campo Teléfono solo debe contener números.";
                break;
            case 4:
                $mensaje = "🔒 La contraseña debe tener al menos 6 caracteres.";
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Cliente - Agenda tu Belleza</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #D1B3FF 0%, #E8D4FF 100%);
      font-family: 'Segoe UI', sans-serif;
    }

    .btn-registrar {
      background-color: #7E57C2;
      color: white;
      border: none;
      transition: all 0.3s ease;
      box-shadow: 0 3px 6px rgba(0,0,0,0.15);
    }

    .btn-registrar:hover {
      background-color: #9C77E8;
      transform: scale(1.04);
      box-shadow: 0 4px 10px rgba(0,0,0,0.25);
    }

    .card-form {
      background-color: rgba(255,255,255,0.93);
      border-radius: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .titulo {
      color: #7E57C2;
      font-weight: bold;
    }
  </style>
</head>

<body>

  <div class="position-absolute" style="top: 25px; left: 40px;">
    <div class="rounded-circle overflow-hidden shadow-lg" style="width: 100px; height: 100px;">
      <img src="img/logo.png" alt="Logo Agenda tu Belleza" style="width:100%; height:100%; object-fit:cover;">
    </div>
  </div>

  <div class="col-md-8 col-lg-5 p-4 card-form">
    <h2 class="text-center titulo mb-4">Registrar nuevo cliente</h2>
    <form method="POST" enctype="multipart/form-data" autocomplete="off">

      <div class="mb-3">
          <label class="form-label fw-semibold">Nombre *</label>
          <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($_POST["nombre"] ?? "") ?>" autocomplete="off" required>
      </div>

      <div class="mb-3">
          <label class="form-label fw-semibold">Apellido *</label>
           <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($_POST["apellido"] ?? "") ?>" autocomplete="off" required>
      </div>
        
      <div class="mb-3">
          <label class="form-label fw-semibold">Correo electronico *</label>
          <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($_POST["correo"] ?? "") ?>" autocomplete="off" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Contraseña * (Mín. 6 caracteres)</label>
        <input type="password" name="contraseña" class="form-control" autocomplete="new-password" required minlength="6">
      </div>

       <div class="mb-3">
          <label class="form-label fw-semibold">Telefono *</label>
          <input type="tel" name="telefono" class="form-control" value="<?= htmlspecialchars($_POST["telefono"] ?? "") ?>" autocomplete="off" required pattern="[0-9]+" title="Solo ingrese números."> 
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Estado</label>
        <p class="form-control-plaintext fw-bold text-success">Activo</p>
        <input type="hidden" name="estado" value="1">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Foto de perfil (JPG, PNG o GIF)</label>
        <input type="file" name="foto" class="form-control" accept=".jpg, .jpeg, .png, .gif">
      </div>

      <?php 
      if ($exito): ?>
        <div class="alert alert-success text-center mb-3">
          ✅ ¡Cliente registrado exitosamente!
        </div>
      <?php elseif ($correoDuplicado): ?>
        <div class="alert alert-danger text-center mb-3">
          ⚠️ El correo ya está registrado. Intenta con otro.
        </div>
      <?php elseif ($errorValidacion != 0 || $errorEnSubidaFoto || $error): ?>
        <div class="alert <?= $claseMensaje ?> text-center mb-3">
          <?= $mensaje ?>
        </div>
      <?php endif; ?>

      <button type="submit" name="registrarCliente" class="btn btn-registrar w-100 py-2 fw-semibold">
        Registrar
      </button>
    </form>

    <div class="text-center mt-3">
      <a href="?pid=<?php echo base64_encode('presentacion/autenticarse.php'); ?>" 
         class="text-decoration-none fw-semibold" style="color:#7E57C2;">
        ← Volver al inicio
      </a>
    </div>
  </div>

</body>
</html>