<?php
if (isset($_GET["sesion"])) {
    if ($_GET["sesion"] == "false") {
        session_destroy();
    }
}

$error = false;
$pendiente = false; 

if (isset($_POST["autenticarse"])) {
    $correo = $_POST["correo"];
    $contraseña = $_POST["contraseña"];
    
    $gerente = new Gerente("", "", "", $correo, $contraseña);
    if ($gerente->autenticarse()) {
        $_SESSION["id"] = $gerente->getId();
        $_SESSION["rol"] = "gerente";
        header("Location: ?pid=" . base64_encode("presentacion/sesionGerente.php"));
        exit();
    }
    $empleado = new Empleado("", "", "", $correo, $contraseña);
    $resultado = $empleado->autenticarse();
    
    if ($resultado === true) {
        $_SESSION["id"] = $empleado->getId();
        $_SESSION["rol"] = "empleado";
        header("Location: ?pid=" . base64_encode("presentacion/sesionEmpleado.php"));
        exit();
    }
    
    if ($resultado === "inactivo") {
        header("Location: ?pid=" . base64_encode("presentacion/autenticarse.php") . "&cuentaInactiva=1");
        exit();
    }
    
    $cliente = new Cliente("", "", "", $correo, $contraseña);
    if ($cliente->autenticarse()) {
        $_SESSION["id"] = $cliente->getId();
        $_SESSION["rol"] = "cliente";
        header("Location: ?pid=" . base64_encode("presentacion/sesionCliente.php"));
        exit();
    }
    
    if (!$pendiente) {
        $error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agenda tu belleza</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://kit.fontawesome.com/2c36e9b7b1.js" crossorigin="anonymous"></script>

<style>
  body {
    background-color: #ffe6f2; 
    font-family: 'Segoe UI', sans-serif;
    min-height: 100vh;
    position: relative;
    overflow: hidden;
  }

  body::before {
    content: "";
    background-image: url('img/decoracion.png');
    background-size: 400px;
    background-repeat: repeat;
    opacity: 0.15;
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 0;
  }

  .card {
    background-color: #fff;
    border: none;
    border-radius: 1rem;
  }

  .btn-orange {
    background-color: #e67e22;
    color: #fff;
    font-weight: bold;
  }

  .btn-orange:hover {
    background-color: #cf6e1b;
  }

  .rounded-logo {
    width: 280px;
    height: 280px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid #e67e22;
    box-shadow: 0 0 20px rgba(230, 126, 34, 0.5);
  }

  .rounded-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
</style>
</head>

<body>
  <div class="container-fluid d-flex flex-wrap justify-content-center align-items-center min-vh-100 px-3 position-relative" style="z-index: 1;">
    <div class="row w-100 flex-lg-row flex-column justify-content-center align-items-center text-center text-lg-start">

      <div class="col-lg-5 col-12 d-flex flex-column justify-content-center align-items-center px-3 mb-4 mb-lg-0">
        <div class="rounded-logo mb-4">
          <img src="img/logo.png" alt="Logo Agenda tu belleza">
        </div>
        <div class="text-center">
          <p class="fw-bold fs-4 text-dark">Tu estilo, tu momento, <span style="color: #e67e22;">tu belleza.</span></p>
          <ul class="list-unstyled">
            <li class="mb-2">💇‍♀️ Agenda tu cita fácilmente</li>
            <li class="mb-2">💅 Elige a tu estilista favorito</li>
            <li class="mb-2">🕓 Controla tus horarios y servicios</li>
            <li>📍 ¡Disfruta de tu mejor versión!</li>
          </ul>
        </div>
      </div>

      <div class="col-lg-5 col-12 d-flex justify-content-center">
        <div class="card shadow p-4 w-100" style="max-width: 500px; z-index: 2;">
          <h4 class="text-center mb-4">Bienvenido a <span style="color:#e67e22;">Agenda tu belleza</span></h4>

         <?php if (isset($_GET["cuentaInactiva"])): ?>
			<div class="alert alert-warning mt-3 text-center">
    			⚠ Tu cuenta aún no ha sido aprobada por el gerente.
			</div>
		<?php endif; ?>


          <?php if ($error): ?>
              <div class="alert alert-danger text-center">
                  ❌ Correo o contraseña incorrectos.
              </div>
          <?php endif; ?>

          <form method="POST" action="?pid=<?php echo base64_encode('presentacion/autenticarse.php'); ?>">
            <input type="hidden" name="autenticarse" value="1">

            <div class="mb-3 text-start">
              <label for="correo" class="form-label">Correo electrónico</label>
              <input type="email" class="form-control" id="correo" name="correo" required>
            </div>

            <div class="mb-3 text-start">
              <label for="contraseña" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="contraseña" name="contraseña" required>
            </div>

            <button type="submit" class="btn btn-orange w-100">Iniciar Sesión</button>
          </form>

          <div class="text-center mt-3">
            <a href="?pid=<?php echo base64_encode('presentacion/cliente/registroCliente.php'); ?>" style="color: #e67e22; text-decoration: underline;">
              ¿Eres nuevo? Regístrate aquí
            </a>
          </div>

          <div class="text-center mt-3">
            <a href="?pid=<?php echo base64_encode('presentacion/empleado/registroEmpleado.php'); ?>" style="color: #e67e22; text-decoration: underline;">
              ¿Quieres trabajar con nosotros? Regístrate aquí
            </a>
          </div>

        </div>
      </div>

    </div>
  </div>
</body>
</html>
