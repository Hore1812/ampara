<?php
session_start(); // Asegurar que la sesión esté iniciada
require_once 'conexion.php'; // Incluir si es necesario para $pdo o constantes de BD, aunque las funciones ya lo hacen.
require_once 'funciones.php'; 
// require_once 'auth_check.php'; // Descomentar si se necesita autenticación aquí también

$id_usuario_editor = $_SESSION['idusuario'] ?? ($_SESSION['idemp'] ?? 1); // Usar idusuario o idemp, o 1 por defecto

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    // Guardar datos del POST en sesión ANTES para repoblar en caso de error
    if ($accion === 'registrar' || $accion === 'editar') {
        $_SESSION['form_data_planificacion'] = $_POST; 
    }

    $redirect_url = 'planificaciones.php'; 
    $idplanificacion_for_redirect = null;

    if ($accion === 'editar' && isset($_POST['idplanificacion'])) {
         $idplanificacion_for_redirect = filter_input(INPUT_POST, 'idplanificacion', FILTER_VALIDATE_INT);
    }

    try {
        if ($accion === 'registrar' || $accion === 'editar') {
            $idcontratocli = filter_input(INPUT_POST, 'idcontratocli', FILTER_VALIDATE_INT);
            $nombre_planificacion = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
            $mes_seleccionado = filter_input(INPUT_POST, 'mes_planificado', FILTER_SANITIZE_STRING); 
            $anio_seleccionado = filter_input(INPUT_POST, 'anio_planificado', FILTER_SANITIZE_STRING);
            $horas_planificadas_str = str_replace(',', '.', $_POST['horas_planificadas'] ?? '0');
            $horas_planificadas = filter_var($horas_planificadas_str, FILTER_VALIDATE_FLOAT);
            $activo = isset($_POST['activo']) ? 1 : 0;

            if ($accion === 'registrar') {
                $redirect_url_form_error = 'registrar_planificacion.php';
            } else { 
                if (!$idplanificacion_for_redirect && isset($_SESSION['form_data_planificacion']['idplanificacion'])) {
                    $idplanificacion_for_redirect = (int)$_SESSION['form_data_planificacion']['idplanificacion'];
                }
                $redirect_url_form_error = $idplanificacion_for_redirect ? "editar_planificacion.php?id={$idplanificacion_for_redirect}" : 'planificaciones.php';
            }

            if (empty($idcontratocli) || empty($nombre_planificacion) || empty($mes_seleccionado) || empty($anio_seleccionado) || $horas_planificadas === false || $horas_planificadas < 0) {
                $_SESSION['mensaje_error'] = "Todos los campos marcados con * son obligatorios y deben ser válidos. Verifique las horas.";
                $_SESSION['mensaje_error_flash_planif'] = true; 
                header('Location: ' . $redirect_url_form_error);
                exit;
            }
            if (strlen($nombre_planificacion) > 150) {
                $_SESSION['mensaje_error'] = "El nombre de la planificación no puede exceder los 150 caracteres.";
                $_SESSION['mensaje_error_flash_planif'] = true;
                header('Location: ' . $redirect_url_form_error);
                exit;
            }

            $fecha_mes_planificado = $anio_seleccionado . '-' . $mes_seleccionado . '-01';
            $date_obj = DateTime::createFromFormat('Y-m-d', $fecha_mes_planificado);
            if (!$date_obj || $date_obj->format('Y-m-d') !== $fecha_mes_planificado) {
                $_SESSION['mensaje_error'] = "El mes y año seleccionados no forman una fecha válida.";
                $_SESSION['mensaje_error_flash_planif'] = true;
                header('Location: ' . $redirect_url_form_error);
                exit;
            }

            $datos_planificacion = [
                'idcontratocli' => $idcontratocli,
                'nombre' => $nombre_planificacion,
                'mes' => $fecha_mes_planificado,
                'horas_planificadas' => $horas_planificadas,
                'activo' => $activo,
                'editor' => $id_usuario_editor 
            ];

            if ($accion === 'registrar') {
                if (verificarPlanificacionUnica($idcontratocli, $fecha_mes_planificado)) {
                    $_SESSION['mensaje_error'] = "Ya existe una planificación para el contrato y mes seleccionados.";
                    $_SESSION['mensaje_error_flash_planif'] = true;
                    header('Location: ' . $redirect_url_form_error);
                    exit;
                }
                $nueva_id = insertarPlanificacion($datos_planificacion);
                if ($nueva_id) {
                    unset($_SESSION['form_data_planificacion']); 
                    $_SESSION['mensaje_exito'] = "Planificación registrada exitosamente con ID: " . $nueva_id . ".";
                } else {
                    $_SESSION['mensaje_error'] = "Error al registrar la planificación. " . ($_SESSION['mensaje_error_detalle'] ?? '');
                    unset($_SESSION['mensaje_error_detalle']);
                    $_SESSION['mensaje_error_flash_planif'] = true;
                    header('Location: ' . $redirect_url_form_error);
                    exit;
                }
            } else { // ($accion === 'editar')
                if (empty($idplanificacion_for_redirect)) {
                    $_SESSION['mensaje_error'] = "ID de planificación no válido para la edición.";
                    $_SESSION['mensaje_error_flash_planif'] = true; 
                    header('Location: ' . $redirect_url_form_error); 
                    exit;
                }
                if (verificarPlanificacionUnica($idcontratocli, $fecha_mes_planificado, $idplanificacion_for_redirect)) {
                    $_SESSION['mensaje_error'] = "Ya existe otra planificación para el contrato y mes seleccionados.";
                    $_SESSION['mensaje_error_flash_planif'] = true;
                    header('Location: ' . $redirect_url_form_error);
                    exit;
                }

                if (actualizarPlanificacion($idplanificacion_for_redirect, $datos_planificacion)) {
                    unset($_SESSION['form_data_planificacion']);
                    $_SESSION['mensaje_exito'] = "Planificación ID: " . $idplanificacion_for_redirect . " actualizada exitosamente.";
                } else {
                    $_SESSION['mensaje_error'] = "Error al actualizar la planificación o no se realizaron cambios. " . ($_SESSION['mensaje_error_detalle'] ?? '');
                    unset($_SESSION['mensaje_error_detalle']);
                    $_SESSION['mensaje_error_flash_planif'] = true;
                    header('Location: ' . $redirect_url_form_error);
                    exit;
                }
            }
        } elseif ($accion === 'activar' || $accion === 'desactivar') {
            unset($_SESSION['form_data_planificacion']); 
            $idplanificacion_op = filter_input(INPUT_POST, 'idplanificacion', FILTER_VALIDATE_INT);
            if (empty($idplanificacion_op)) {
                 $_SESSION['mensaje_error'] = "ID de planificación no válido.";
                 header('Location: ' . $redirect_url);
                 exit;
            }
            $nuevo_estado = ($accion === 'activar') ? 1 : 0;
            $texto_accion = ($accion === 'activar') ? 'activada' : 'desactivada';

            if (actualizarEstadoPlanificacion($idplanificacion_op, $nuevo_estado, $id_usuario_editor)) {
               $_SESSION['mensaje_exito'] = "Planificación $texto_accion exitosamente.";
            } else {
               $_SESSION['mensaje_error'] = "Error al cambiar el estado de la planificación. " . ($_SESSION['mensaje_error_detalle'] ?? '');
               unset($_SESSION['mensaje_error_detalle']);
            }
        } else {
            unset($_SESSION['form_data_planificacion']); 
            $_SESSION['mensaje_error'] = "Acción no reconocida.";
        }

    } catch (PDOException $e) {
        error_log("Error de BD en procesar_planificacion.php: " . $e->getMessage());
        $_SESSION['mensaje_error'] = "Error de base de datos. Por favor, contacte al administrador.";
        if ($accion === 'registrar' || $accion === 'editar') {
             $_SESSION['mensaje_error_flash_planif'] = true;
             $redirect_url = $redirect_url_form_error ?? $redirect_url; // Asegurar que se redirija al form
        }
    } catch (Exception $e) { // Captura excepciones generales (como las que lanzamos manualmente)
        $_SESSION['mensaje_error'] = "Error: " . $e->getMessage();
         if ($accion === 'registrar' || $accion === 'editar') {
             $_SESSION['mensaje_error_flash_planif'] = true;
             $redirect_url = $redirect_url_form_error ?? $redirect_url;
        }
    }
    
    unset($_SESSION['mensaje_error_detalle']);

    header('Location: ' . $redirect_url);
    exit;

} else {
    $_SESSION['mensaje_error'] = "Acceso no permitido.";
    header('Location: planificaciones.php');
    exit;
}
?>