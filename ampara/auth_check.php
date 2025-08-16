<?php
// Iniciar la sesión si no está ya iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
// Comprobamos la existencia de la variable de sesión que establecimos en login_process.php
if (!isset($_SESSION['idusuario'])) {
    // Si no está logueado, redirigir a la página de login
    // Guardar la URL actual para redirigir después del login (opcional)
    // $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    header("Location: login.php"); // Redirigir a la nueva página de login
    exit;
}

// Opcional: Podrías añadir aquí una comprobación de actividad de sesión
// para cerrar sesiones inactivas después de un tiempo, aunque esto es más avanzado.

// Si llegamos aquí, el usuario está autenticado.
// Puedes acceder a los datos del usuario desde $_SESSION si es necesario.
// Ejemplo: $current_user_id = $_SESSION['idusuario'];
// Ejemplo: $current_username = $_SESSION['nombre_usuario'];
?>
