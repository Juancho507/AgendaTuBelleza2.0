<?php
$exito = false;
$error = false;
$correoDuplicado = false;
$errorEnSubidaFoto = false;
$errorEnSubidaHoja = false;
$errorValidacion = 0;
$mensaje = "";
$claseMensaje = "";
$fotoRuta = "";
$hojaRuta = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $correo = $_POST["correo"];
    $contraseña = $_POST["contraseña"];
    $telefono = $_POST["telefono"];
    $estado = $_POST["estado"] ?? '0';
    $salario = $_POST["salario"] ?? "0";
    $horario = $_POST["horario"] ?? "";
    $servicios = $_POST["servicios"] ?? [];
    $fechaRegistro = date("Y-m-d H:i:s");

   
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($contraseña) || empty($telefono)) {
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

    $extensionesPermitidasImg = array('jpg', 'jpeg', 'png', 'gif');

    if ($errorValidacion == 0) {
        if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] === UPLOAD_ERR_OK) {
            $nombreFotoOriginal = $_FILES["foto"]["name"];
            $rutaTemporal = $_FILES["foto"]["tmp_name"];
            $extension = pathinfo($nombreFotoOriginal, PATHINFO_EXTENSION);

            if (!in_array(strtolower($extension), $extensionesPermitidasImg)) {
                $mensaje = "Formato de imagen no permitido. Solo se aceptan imágenes JPG, PNG o GIF.";
                $claseMensaje = "alert-danger";
                $errorEnSubidaFoto = true;
            } else {
                $nuevoNombreFoto = time() . "_foto." . strtolower($extension);
                $directorioDestino = "imagenes/";
                if (!file_exists($directorioDestino)) {
                    mkdir($directorioDestino, 0777, true);
                }
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

    $extensionesPermitidasDocs = array('pdf', 'doc', 'docx');

    if ($errorValidacion == 0) {
        if (isset($_FILES["HojaDeVida"]) && $_FILES["HojaDeVida"]["error"] === UPLOAD_ERR_OK) {
            $nombreHojaOriginal = $_FILES["HojaDeVida"]["name"];
            $rutaTemporalHoja = $_FILES["HojaDeVida"]["tmp_name"];
            $extensionHoja = pathinfo($nombreHojaOriginal, PATHINFO_EXTENSION);

            if (!in_array(strtolower($extensionHoja), $extensionesPermitidasDocs)) {
                $mensaje = "Formato de Hoja de Vida no permitido. Solo se aceptan PDF, DOC o DOCX.";
                $claseMensaje = "alert-danger";
                $errorEnSubidaHoja = true;
            } else {
                $nuevoNombreHoja = time() . "_hv." . strtolower($extensionHoja);
                $directorioDestinoHoja = "imagenes/"; 
                if (!file_exists($directorioDestinoHoja)) {
                    mkdir($directorioDestinoHoja, 0777, true);
                }
                $rutaServidorHoja = $directorioDestinoHoja . $nuevoNombreHoja;

                if (move_uploaded_file($rutaTemporalHoja, $rutaServidorHoja)) {
                    $hojaRuta = $rutaServidorHoja;
                } else {
                    $mensaje = "Error al mover el archivo de la Hoja de Vida al servidor (verifique permisos de la carpeta 'imagenes').";
                    $claseMensaje = "alert-danger";
                    $errorEnSubidaHoja = true;
                }
            }
        } else if (isset($_FILES["HojaDeVida"]) && $_FILES["HojaDeVida"]["error"] != UPLOAD_ERR_NO_FILE) {
            $mensaje = "Error en la subida de la Hoja de Vida (código: " . $_FILES["HojaDeVida"]["error"] . ").";
            $claseMensaje = "alert-danger";
            $errorEnSubidaHoja = true;
        }
    }

    if ($errorValidacion == 0 && !$errorEnSubidaFoto && !$errorEnSubidaHoja) {
        $gerentePorDefecto = 1;

        $empleado = new Empleado(
            "", 
            $nombre,
            $apellido,
            $correo,
            $contraseña,
            $telefono,
            $estado,
            $salario,
            $horario,
            $gerentePorDefecto,
            $fotoRuta,
            $hojaRuta 
        );

        if ($empleado->correoExiste()) {
            $correoDuplicado = true;
            if ($fotoRuta != "" && file_exists($fotoRuta)) unlink($fotoRuta);
            if ($hojaRuta != "" && file_exists($hojaRuta)) unlink($hojaRuta);
        } else {
            try {
                $empleado->registrar();

                $exito = true;
                $_POST = [];
            } catch (Exception $e) {
                $error = true;
                if ($fotoRuta != "" && file_exists($fotoRuta)) unlink($fotoRuta);
                if ($hojaRuta != "" && file_exists($hojaRuta)) unlink($hojaRuta);

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
  <title>Registrar Empleado - Agenda tu Belleza</title>
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

  <div class="col-md-9 col-lg-6 p-4 card-form">
    <h2 class="text-center titulo mb-4">Registro de solicitante - Empleado</h2>
    <form method="POST" enctype="multipart/form-data" autocomplete="off">

      <div class="row">
        <div class="mb-3 col-md-6">
          <label class="form-label fw-semibold">Nombre *</label>
          <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($_POST["nombre"] ?? "") ?>" autocomplete="off" required>
        </div>

        <div class="mb-3 col-md-6">
          <label class="form-label fw-semibold">Apellido *</label>
          <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($_POST["apellido"] ?? "") ?>" autocomplete="off" required>
        </div>
      </div>

      <div class="mb-3">
          <label class="form-label fw-semibold">Correo electrónico *</label>
          <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($_POST["correo"] ?? "") ?>" autocomplete="off" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Contraseña * (Mín. 6 caracteres)</label>
        <input type="password" name="contraseña" class="form-control" autocomplete="new-password" required minlength="6">
      </div>

       <div class="mb-3">
          <label class="form-label fw-semibold">Teléfono *</label>
          <input type="tel" name="telefono" class="form-control" value="<?= htmlspecialchars($_POST["telefono"] ?? "") ?>" autocomplete="off" required pattern="[0-9]+" title="Solo ingrese números."> 
      </div>

      <input type="hidden" name="estado" value="0">

      <div class="row">
        <div class="mb-3 col-md-6">
            <label class="form-label fw-semibold">Salario (asignado por gerente)</label>
            <input type="text" name="salario" class="form-control" value="<?= htmlspecialchars($_POST["salario"] ?? "") ?>" disabled>
        </div>

        <div class="mb-3 col-md-6">
            <label class="form-label fw-semibold">Horario (asignado por gerente)</label>
            <input type="text" name="horario" class="form-control" value="<?= htmlspecialchars($_POST["horario"] ?? "") ?>" disabled>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Foto de perfil (JPG, PNG o GIF)</label>
        <input type="file" name="foto" class="form-control" accept=".jpg, .jpeg, .png, .gif">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Anexe su Hoja de Vida (PDF, DOC, DOCX). Aquí incluya Experiencia, certificaciones, etc.</label>
        <input type="file" name="HojaDeVida" class="form-control" accept=".pdf, .doc, .docx">
      </div>

      <?php 
      if ($exito): ?>
        <div class="alert alert-success text-center mb-3">
          ✅ Registro enviado. Espere unos minutos y vuelva a ingresar para verificar su registro.
        </div>
      <?php elseif ($correoDuplicado): ?>
        <div class="alert alert-danger text-center mb-3">
          ⚠️ El correo ya está registrado. Intenta con otro.
        </div>
      <?php elseif ($errorValidacion != 0 || $errorEnSubidaFoto || $errorEnSubidaHoja || $error): ?>
        <div class="alert <?= $claseMensaje ?> text-center mb-3">
          <?= $mensaje ?>
        </div>
      <?php endif; ?>

      <button type="submit" name="registrarEmpleado" class="btn btn-registrar w-100 py-2 fw-semibold">
        Guardar solicitud
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
