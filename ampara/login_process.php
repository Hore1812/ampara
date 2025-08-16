<!-- login_process.php -->
<?php
session_start(); // Iniciar la sesión al principio de todo

require 'conexion.php'; // Conexión a la BD

$mensajeError = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $mensajeError = "Por favor, ingresa tu nombre de usuario y contraseña.";
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        try {
            // Buscar al usuario en la base de datos
            // MODIFICACIÓN: Incluir idemp y tipo en la consulta inicial
            $stmt = $pdo->prepare("SELECT idusuario, nombre, password, activo, idemp, tipo FROM usuario WHERE nombre = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if ($user['activo'] != 1) {
                    $mensajeError = "Tu cuenta no está activa. Contacta al administrador.";
                } else if (password_verify($password, $user['password'])) {
                    // Contraseña correcta y usuario activo
                    $_SESSION['idusuario'] = $user['idusuario'];
                    $_SESSION['nombre_usuario'] = $user['nombre'];
                    // MODIFICACIÓN: Guardar idemp y tipo_usuario en la sesión
                    $_SESSION['idemp'] = $user['idemp'];
                    $_SESSION['tipo_usuario'] = $user['tipo'];
                    
                    $session_php_id = session_id();
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                    
                    // Asumiendo que la tabla sesiones_log existe y tiene las columnas mencionadas
                    // Si la tabla no existe, esta parte causará un error.
                    try {
                        $stmt_log_insert = $pdo->prepare("
                            INSERT INTO sesiones_log 
                                (idusuario, session_php_id, ip_address_inicio, timestamp_inicio) 
                            VALUES 
                                (:idusuario, :session_php_id, :ip_address, NOW())
                        ");
                        $stmt_log_insert->bindParam(':idusuario', $user['idusuario']);
                        $stmt_log_insert->bindParam(':session_php_id', $session_php_id);
                        $stmt_log_insert->bindParam(':ip_address', $ip_address);
                        $stmt_log_insert->execute();
                        $_SESSION['id_sesion_log_db'] = $pdo->lastInsertId();
                    } catch (PDOException $e) {
                        // Loguear este error específico pero no detener el login
                        error_log("Error al insertar en sesiones_log: " . $e->getMessage());
                        // No se establece $_SESSION['id_sesion_log_db'] si falla
                    }

                    // MODIFICACIÓN: Redirigir a index.php en la raíz
                    header("Location: index.php"); 
                    exit;
                } else {
                    $mensajeError = "Nombre de usuario o contraseña incorrectos.";
                }
            } else {
                $mensajeError = "Nombre de usuario o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            error_log("Error en login_process.php: " . $e->getMessage());
            $mensajeError = "Ocurrió un error en el servidor. Inténtalo más tarde.";
        }
    }
}

if (!empty($mensajeError)) {
    $_SESSION['login_error'] = $mensajeError;
    header("Location: login.php"); 
    exit;
}
?>
