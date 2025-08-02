<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php'; 
require_once 'auth_check.php'; 

// Descomenta estas líneas SOLO TEMPORALMENTE para depurar si el error persiste
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;
$idusuario = isset($_POST['idusuario']) ? filter_var($_POST['idusuario'], FILTER_VALIDATE_INT) : (isset($_GET['idusuario']) ? filter_var($_GET['idusuario'], FILTER_VALIDATE_INT) : null);

// Para la acción cambiar_password, el idusuario puede venir como idusuario_cp del formulario del modal
if ($accion === 'cambiar_password' && empty($idusuario) && isset($_POST['idusuario_cp'])) {
    $idusuario = filter_var($_POST['idusuario_cp'], FILTER_VALIDATE_INT);
}

$editor_id = $_SESSION['idemp'] ?? 0; 

if (!$editor_id && $accion !== 'login_process_related_action_if_any') { // Excluir acciones que no necesiten editor
    if ($accion === 'cambiar_password') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "Error de sesión. No se pudo identificar al editor."]);
        exit;
    }
    $_SESSION['mensaje_error'] = "Error de sesión. No se pudo identificar al editor.";
    header('Location: usuarios.php');
    exit;
}

try {
    if ($accion === 'crear') {
        $nombre = trim($_POST['nombre'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmar_password = $_POST['confirmar_password'] ?? '';
        $tipo = filter_var($_POST['tipo'] ?? null, FILTER_VALIDATE_INT);
        $idemp = filter_var($_POST['idemp'] ?? null, FILTER_VALIDATE_INT);
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($nombre) || empty($password) || empty($confirmar_password) || $tipo === false || $idemp === false) {
            $_SESSION['mensaje_error'] = "Todos los campos marcados con * son requeridos.";
            header('Location: registrar_usuario.php');
            exit;
        }
        if (strlen($password) < 6) { 
            $_SESSION['mensaje_error'] = "La contraseña debe tener al menos 6 caracteres.";
            header('Location: registrar_usuario.php');
            exit;
        }
        if ($password !== $confirmar_password) {
            $_SESSION['mensaje_error'] = "Las contraseñas no coinciden.";
            header('Location: registrar_usuario.php');
            exit;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $datos_usuario = [
            'nombre' => $nombre,
            'password' => $hashed_password,
            'tipo' => $tipo,
            'idemp' => $idemp,
            'activo' => $activo,
            'editor' => $editor_id 
        ];

        if (registrarUsuario($datos_usuario)) {
            $_SESSION['mensaje_exito'] = "Usuario registrado correctamente.";
        } else {
            $_SESSION['mensaje_error'] = "Error al registrar el usuario." . ($_SESSION['mensaje_error_detalle'] ?? '');
            unset($_SESSION['mensaje_error_detalle']);
            header('Location: registrar_usuario.php');
            exit;
        }
        // Redirección para 'crear' se hace al final si no es acción AJAX

    } elseif ($accion === 'actualizar' && $idusuario) {
        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = filter_var($_POST['tipo'] ?? null, FILTER_VALIDATE_INT);
        $idemp = filter_var($_POST['idemp'] ?? null, FILTER_VALIDATE_INT);
        $activo = isset($_POST['activo']) ? 1 : 0;

        if (empty($nombre) || $tipo === false || $idemp === false) {
            $_SESSION['mensaje_error'] = "Los campos Nombre, Tipo e Empleado Asociado son requeridos.";
            header('Location: editar_usuario.php?id=' . $idusuario);
            exit;
        }
        
        $datos_usuario = [
            'nombre' => $nombre,
            'tipo' => $tipo,
            'idemp' => $idemp,
            'activo' => $activo,
            'editor' => $editor_id 
        ];

        if (actualizarUsuario($idusuario, $datos_usuario)) {
            $_SESSION['mensaje_exito'] = "Usuario actualizado correctamente.";
        } else {
            $_SESSION['mensaje_error'] = "Error al actualizar el usuario o no se realizaron cambios." . ($_SESSION['mensaje_error_detalle'] ?? '');
            unset($_SESSION['mensaje_error_detalle']);
            header('Location: editar_usuario.php?id=' . $idusuario);
            exit;
        }
        // Redirección para 'actualizar' se hace al final si no es acción AJAX

    } elseif ($accion === 'cambiar_password' && $idusuario) {
        header('Content-Type: application/json'); // Asegurar que esto se envíe primero
        $nueva_password = $_POST['nueva_password'] ?? '';
        $confirmar_nueva_password = $_POST['confirmar_nueva_password'] ?? '';

        if (empty($nueva_password) || empty($confirmar_nueva_password)) {
            echo json_encode(['success' => false, 'message' => "Ambos campos de contraseña son requeridos."]);
            exit;
        }
        if (strlen($nueva_password) < 6) {
            echo json_encode(['success' => false, 'message' => "La nueva contraseña debe tener al menos 6 caracteres."]);
            exit;
        }
        if ($nueva_password !== $confirmar_nueva_password) {
            echo json_encode(['success' => false, 'message' => "Las nuevas contraseñas no coinciden."]);
            exit;
        }

        $hashed_password = password_hash($nueva_password, PASSWORD_DEFAULT);
        if (actualizarPasswordUsuario($idusuario, $hashed_password, $editor_id)) {
            echo json_encode(['success' => true, 'message' => "Contraseña actualizada correctamente."]);
            exit;
        } else {
            $error_msg = "Error al actualizar la contraseña.";
            if(isset($_SESSION['mensaje_error_detalle'])){
                $error_msg .= " " . $_SESSION['mensaje_error_detalle'];
                unset($_SESSION['mensaje_error_detalle']);
            }
            echo json_encode(['success' => false, 'message' => $error_msg]);
            exit;
        }

    } elseif (($accion === 'desactivar' || $accion === 'activar') && $idusuario) {
        $nuevo_estado = ($accion === 'activar') ? 1 : 0;
        if (actualizarEstadoUsuario($idusuario, $nuevo_estado, $editor_id)) {
            $_SESSION['mensaje_exito'] = "Estado del usuario actualizado correctamente.";
        } else {
            $_SESSION['mensaje_error'] = "Error al actualizar el estado del usuario." . ($_SESSION['mensaje_error_detalle'] ?? '');
            unset($_SESSION['mensaje_error_detalle']);
        }
        // Redirección para 'activar/desactivar' se hace al final
    } else {
        $_SESSION['mensaje_error'] = "Acción no válida o ID de usuario no proporcionado.";
    }

} catch (PDOException $e) {
    error_log("Error de BD en procesar_usuario.php: " . $e->getMessage());
    if ($accion === 'cambiar_password') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "Error de base de datos al cambiar contraseña."]);
        exit;
    } else {
        $_SESSION['mensaje_error'] = "Error de base de datos. Por favor, contacte al administrador.";
    }
} catch (Exception $e) {
    error_log("Error general en procesar_usuario.php: " . $e->getMessage());
     if ($accion === 'cambiar_password') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "Ocurrió un error inesperado al cambiar contraseña."]);
        exit;
    } else {
        $_SESSION['mensaje_error'] = "Ocurrió un error inesperado. Por favor, contacte al administrador.";
    }
}

// Redirección final para acciones que no son AJAX
if ($accion !== 'cambiar_password') {
    header('Location: usuarios.php');
    exit;
}
?>