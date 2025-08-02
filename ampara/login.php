<!-- login.php -->
<?php
session_start(); // Iniciar sesión para poder mostrar mensajes de error

// Si el usuario ya está logueado, redirigirlo a index.php (o dashboard.php)
if (isset($_SESSION['idusuario'])) {
    header('Location: index.php'); // O la página principal de tu aplicación después del login
    exit;
}

$error_message = '';
if (isset($_SESSION['login_error'])) {
    $error_message = $_SESSION['login_error'];
    unset($_SESSION['login_error']); // Limpiar el mensaje de error para que no se muestre de nuevo
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <!-- Asumiendo que usas Bootstrap, ajusta la ruta si es necesario -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f28120; 
            background-color: #012060;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            border-radius: 10px; /* Añadido para consistencia */
        }
        /* Estilos para el logo si la carpeta img está en la raíz */
        .login-card img {
             max-width: 200px; /* Ajustado */
             height: auto;
             margin-bottom: 1rem; /* Espacio debajo del logo */
        }
    </style>
</head>
<body>
    <div class="card login-card shadow">
        <div class="card-body">
            <div class="text-center mb-4">
                <!-- Se asume que la carpeta img está en la raíz del proyecto -->
                <img src="img/logo.webp" alt="Logo de la Empresa">
            </div>
            <h3 class="card-title text-center mb-4">Iniciar sesión</h3>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Ingresar</button>
                </div>
            </form>
            <!-- Opcional: Enlace a registro o recuperación de contraseña -->
            <!--
            <div class="text-center mt-3">
                <a href="registro.php">Registrarse</a> | <a href="recuperar_pass.php">Olvidé mi contraseña</a>
            </div>
            -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
