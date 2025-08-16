<?php
session_start(); // Es necesario iniciar la sesión para poder acceder a sus variables y destruirla

require 'conexion.php'; // Conexión a la BD

// Verificar si tenemos un ID de log de sesión para actualizar
if (isset($_SESSION['id_sesion_log_db'])) {
    $id_sesion_log_db = $_SESSION['id_sesion_log_db'];
    $timestamp_fin = date("Y-m-d H:i:s"); // Momento actual

    try {
        // Primero, obtenemos el timestamp_inicio para calcular la duración
        $stmt_get_inicio = $pdo->prepare("SELECT timestamp_inicio FROM sesiones_log WHERE id = :id_sesion_log_db");
        $stmt_get_inicio->bindParam(':id_sesion_log_db', $id_sesion_log_db, PDO::PARAM_INT);
        $stmt_get_inicio->execute();
        $sesion_data = $stmt_get_inicio->fetch(PDO::FETCH_ASSOC);

        $duracion_segundos = null;
        if ($sesion_data && isset($sesion_data['timestamp_inicio'])) {
            $fecha_inicio = new DateTime($sesion_data['timestamp_inicio']);
            $fecha_fin = new DateTime($timestamp_fin);
            $diferencia = $fecha_fin->getTimestamp() - $fecha_inicio->getTimestamp();
            $duracion_segundos = $diferencia;
        }

        // Actualizar el registro en sesiones_log
        $ip_address_fin = $_SERVER['REMOTE_ADDR']; // O usar una función más robusta si es necesario

        $stmt_update_log = $pdo->prepare("
            UPDATE sesiones_log 
            SET 
                timestamp_fin = :timestamp_fin, 
                duracion_segundos = :duracion_segundos,
                ip_address_fin = :ip_address_fin
            WHERE id = :id_sesion_log_db
        ");
        $stmt_update_log->bindParam(':timestamp_fin', $timestamp_fin);
        $stmt_update_log->bindParam(':duracion_segundos', $duracion_segundos, PDO::PARAM_INT);
        $stmt_update_log->bindParam(':ip_address_fin', $ip_address_fin);
        $stmt_update_log->bindParam(':id_sesion_log_db', $id_sesion_log_db, PDO::PARAM_INT);
        $stmt_update_log->execute();

    } catch (PDOException $e) {
        // En un entorno de producción, esto debería registrarse en un archivo de log
        error_log("Error en logout.php al actualizar sesiones_log: " . $e->getMessage());
        // No es crítico para el logout en sí, así que continuamos con la destrucción de la sesión
    }
}

// Destruir todas las variables de sesión.
$_SESSION = array();

// Si se desea destruir la sesión completamente, borre también la cookie de sesión.
// Nota: ¡Esto destruirá la sesión, y no la información de la sesión!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión.
session_destroy();

// Redirigir a la página de login
//header("Location: login.php");
// header("Location: login.php");
header("Location: ../inicio.php");
exit;
?>
