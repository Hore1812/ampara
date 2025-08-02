<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php'; 
require_once 'auth_check.php'; 

$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;
$idtema = isset($_POST['idtema']) ? filter_var($_POST['idtema'], FILTER_VALIDATE_INT) : (isset($_GET['idtema']) ? filter_var($_GET['idtema'], FILTER_VALIDATE_INT) : null);
$editor_id = $_SESSION['idemp'] ?? 0; 

if (!$editor_id) {
    $_SESSION['mensaje_error'] = "Error de sesión. No se pudo identificar al editor.";
    header('Location: temas.php');
    exit;
}

try {
    if ($accion === 'crear') {
        $descripcion = trim($_POST['descripcion'] ?? '');
        $activo = 1; // Por defecto, los nuevos temas se crean activos
        // Si el formulario de registro enviara un campo 'activo', se podría tomar de allí:
        // $activo = isset($_POST['activo']) ? 1 : 0;

        $idencargado = !empty($_POST['idencargado']) ? filter_var($_POST['idencargado'], FILTER_VALIDATE_INT) : null;
        $comentario = trim($_POST['comentario'] ?? '');

        if (empty($descripcion)) {
            $_SESSION['mensaje_error'] = "El campo Descripción es requerido.";
            header('Location: registrar_tema.php');
            exit;
        }
        
        $datos_tema = [
            'descripcion' => $descripcion,
            'idencargado' => $idencargado,
            'comentario' => $comentario,
            'editor' => $editor_id,
            'activo' => $activo 
        ];

        if (registrarTema($datos_tema)) {
            $_SESSION['mensaje_exito'] = "Tema registrado correctamente.";
        } else {
            $_SESSION['mensaje_error'] = "Error al registrar el tema." . ($_SESSION['mensaje_error_detalle'] ?? '');
            unset($_SESSION['mensaje_error_detalle']);
            header('Location: registrar_tema.php');
            exit;
        }

    } elseif ($accion === 'actualizar' && $idtema) {
        $descripcion = trim($_POST['descripcion'] ?? '');
        $idencargado = !empty($_POST['idencargado']) ? filter_var($_POST['idencargado'], FILTER_VALIDATE_INT) : null;
        $comentario = trim($_POST['comentario'] ?? '');
        // Si el formulario de edición tiene un campo 'activo', se toma de allí.
        // Si no, la función actualizarTema en funciones.php debería preservar el estado activo actual si no se le pasa.
        $activo = isset($_POST['activo']) ? 1 : 0; 

        if (empty($descripcion)) {
            $_SESSION['mensaje_error'] = "El campo Descripción es requerido.";
            header('Location: editar_tema.php?id=' . $idtema);
            exit;
        }
        
        $datos_tema = [
            'descripcion' => $descripcion,
            'idencargado' => $idencargado,
            'comentario' => $comentario,
            'activo' => $activo, 
            'editor' => $editor_id 
        ];

        if (actualizarTema($idtema, $datos_tema)) {
            $_SESSION['mensaje_exito'] = "Tema actualizado correctamente.";
        } else {
            $_SESSION['mensaje_error'] = "Error al actualizar el tema o no se realizaron cambios." . ($_SESSION['mensaje_error_detalle'] ?? '');
            unset($_SESSION['mensaje_error_detalle']);
            header('Location: editar_tema.php?id=' . $idtema);
            exit;
        }
    } elseif (($accion === 'desactivar' || $accion === 'activar') && $idtema) {
        $nuevo_estado = ($accion === 'activar') ? 1 : 0;
        if (actualizarEstadoTema($idtema, $nuevo_estado, $editor_id)) {
            $_SESSION['mensaje_exito'] = "Estado del tema actualizado correctamente.";
        } else {
            $_SESSION['mensaje_error'] = "Error al actualizar el estado del tema." . ($_SESSION['mensaje_error_detalle'] ?? '');
            unset($_SESSION['mensaje_error_detalle']);
        }
    } else {
        $_SESSION['mensaje_error'] = "Acción no válida o ID de tema no proporcionado.";
    }

} catch (PDOException $e) {
    error_log("Error de BD en procesar_tema.php: " . $e->getMessage());
    $_SESSION['mensaje_error'] = "Error de base de datos. Por favor, contacte al administrador.";
} catch (Exception $e) {
    error_log("Error general en procesar_tema.php: " . $e->getMessage());
    $_SESSION['mensaje_error'] = "Ocurrió un error inesperado. Por favor, contacte al administrador.";
}

header('Location: temas.php');
exit;
?>