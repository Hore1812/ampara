<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php';

if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] != 1) {
    $_SESSION['mensaje_error'] = "Acceso denegado. No tiene permisos para realizar esta acción.";
    header('Location: index.php');
    exit;
}

$id_usuario_logueado = $_SESSION['idemp'] ?? null; 
if (!$id_usuario_logueado) {
    $_SESSION['mensaje_error'] = "Error de sesión: No se pudo identificar al usuario editor.";
    header('Location: planificaciones.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $redirect_url = 'planificaciones.php';
    $idplanificacion_param = null;

    // Para repoblar formularios en caso de error y para la URL de redirección en edición
    if ($accion === 'registrar' || $accion === 'editar') {
        $_SESSION['form_data_planificacion'] = $_POST; // Guardar todo el POST
    }
    if (isset($_POST['idplanificacion'])) {
        $idplanificacion_param = filter_input(INPUT_POST, 'idplanificacion', FILTER_VALIDATE_INT);
    }


    try {
        if ($accion === 'registrar' || $accion === 'editar') {
            // Para la edición, el idcontratocli vendrá de un campo oculto porque el select estará deshabilitado
            // Para el registro, vendrá del select normal.
            $idcontratocli = filter_input(INPUT_POST, 'idcontratocli', FILTER_VALIDATE_INT);
            
            $nombre_planificacion = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH));
            $mes_seleccionado_str = filter_input(INPUT_POST, 'mes_planificado', FILTER_SANITIZE_STRING);
            $anio_seleccionado_str = filter_input(INPUT_POST, 'anio_planificado', FILTER_SANITIZE_STRING);
            
            $horas_planificadas_input = $_POST['horas_planificadas'] ?? '0';
            $horas_planificadas_str = str_replace(',', '.', $horas_planificadas_input);
            $horas_planificadas = filter_var($horas_planificadas_str, FILTER_VALIDATE_FLOAT);
            
            $comentario = trim(filter_input(INPUT_POST, 'comentario', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH));
            $activo = isset($_POST['activo']) && $_POST['activo'] == '1' ? 1 : 0;

            // Determinar la URL de redirección en caso de error de formulario
            $redirect_url_form_error = ($accion === 'registrar') ? 'registrar_planificacion.php' : ($idplanificacion_param ? "editar_planificacion.php?id={$idplanificacion_param}" : 'planificaciones.php');

            if (empty($idcontratocli) || empty($nombre_planificacion) || empty($mes_seleccionado_str) || empty($anio_seleccionado_str) || $horas_planificadas === false || $horas_planificadas < 0) {
                $_SESSION['mensaje_error'] = "Campos obligatorios incompletos o inválidos. Verifique Contrato, Nombre, Mes, Año y Horas.";
                header('Location: ' . $redirect_url_form_error);
                exit;
            }
            if (strlen($nombre_planificacion) > 255) {
                $_SESSION['mensaje_error'] = "El nombre de la planificación no puede exceder los 255 caracteres.";
                header('Location: ' . $redirect_url_form_error);
                exit;
            }
            if (!ctype_digit($mes_seleccionado_str) || !ctype_digit($anio_seleccionado_str) || (int)$mes_seleccionado_str < 1 || (int)$mes_seleccionado_str > 12) {
                 $_SESSION['mensaje_error'] = "Mes o año inválido.";
                 header('Location: ' . $redirect_url_form_error);
                 exit;
            }

            $fechaplan = $anio_seleccionado_str . '-' . str_pad($mes_seleccionado_str, 2, '0', STR_PAD_LEFT) . '-01';
            $date_obj = DateTime::createFromFormat('Y-m-d', $fechaplan);
            if (!$date_obj || $date_obj->format('Y-m-d') !== $fechaplan) {
                $_SESSION['mensaje_error'] = "La fecha de planificación (Mes y Año) no es válida.";
                header('Location: ' . $redirect_url_form_error);
                exit;
            }

            $contrato_info = obtenerContratoClientePorId($idcontratocli);
            if (!$contrato_info || !isset($contrato_info['lider'])) {
                $_SESSION['mensaje_error'] = "No se pudo obtener la información del líder para el contrato seleccionado.";
                header('Location: ' . $redirect_url_form_error);
                exit;
            }
            $lider_id = $contrato_info['lider'];

            $datos_planificacion = [
                'idContratoCliente' => $idcontratocli, // Este es el que se usa en la BD
                'nombreplan' => $nombre_planificacion,
                'fechaplan' => $fechaplan,
                'horasplan' => $horas_planificadas,
                'lider' => $lider_id,
                'comentario' => $comentario ?: null, // Guardar NULL si está vacío
                'activo' => $activo,
                'editor' => $id_usuario_logueado
            ];
            
            if ($accion === 'registrar') {
                // La FK a planificacion en la tabla planificacion no existe, la validación es contra la tabla planificacion directamente
                if (verificarPlanificacionUnica($idcontratocli, $fechaplan)) {
                    $_SESSION['mensaje_error'] = "Ya existe una planificación para el contrato y mes seleccionados.";
                    header('Location: ' . $redirect_url_form_error);
                    exit;
                }
                $id_planificacion_creada = insertarPlanificacion($datos_planificacion);
                if ($id_planificacion_creada) {
                    unset($_SESSION['form_data_planificacion']);
                    $_SESSION['mensaje_exito'] = "Planificación registrada exitosamente con ID: " . $id_planificacion_creada . ".";
                } else {
                    $_SESSION['mensaje_error'] = "Error al registrar la planificación. " . ($_SESSION['mensaje_error_detalle'] ?? '');
                    header('Location: ' . $redirect_url_form_error);
                    exit;
                }
            } elseif ($accion === 'editar') {
                if (empty($idplanificacion_param)) {
                    $_SESSION['mensaje_error'] = "ID de planificación no válido para la edición.";
                    header('Location: ' . $redirect_url);
                    exit;
                }
                // Al editar, idContratoCliente se toma del input hidden, no del select deshabilitado.
                // $datos_planificacion ya tiene el 'idContratoCliente' correcto del POST (del input hidden)
                if (verificarPlanificacionUnica($datos_planificacion['idContratoCliente'], $fechaplan, $idplanificacion_param)) {
                    $_SESSION['mensaje_error'] = "Ya existe otra planificación para el contrato y mes seleccionados.";
                    header('Location: ' . $redirect_url_form_error);
                    exit;
                }
                if (actualizarPlanificacion($idplanificacion_param, $datos_planificacion)) {
                    unset($_SESSION['form_data_planificacion']);
                    $_SESSION['mensaje_exito'] = "Planificación ID: " . $idplanificacion_param . " actualizada exitosamente.";
                } else {
                    $_SESSION['mensaje_error'] = "Error al actualizar la planificación o no se realizaron cambios. " . ($_SESSION['mensaje_error_detalle'] ?? '');
                    header('Location: ' . $redirect_url_form_error);
                    exit;
                }
            }
        } elseif ($accion === 'activar' || $accion === 'desactivar') {
            unset($_SESSION['form_data_planificacion']);
            $idplanificacion_op = filter_input(INPUT_POST, 'idplanificacion', FILTER_VALIDATE_INT);
            if (empty($idplanificacion_op)) {
                 $_SESSION['mensaje_error'] = "ID de planificación no válido para cambiar estado.";
                 header('Location: ' . $redirect_url);
                 exit;
            }
            $nuevo_estado = ($accion === 'activar') ? 1 : 0;
            $texto_accion = ($accion === 'activar') ? 'activada' : 'desactivada';

            if (actualizarEstadoPlanificacion($idplanificacion_op, $nuevo_estado, $id_usuario_logueado)) {
               $_SESSION['mensaje_exito'] = "Planificación $texto_accion exitosamente.";
            } else {
               $_SESSION['mensaje_error'] = "Error al cambiar el estado de la planificación. " . ($_SESSION['mensaje_error_detalle'] ?? '');
            }
        } else {
            unset($_SESSION['form_data_planificacion']);
            $_SESSION['mensaje_error'] = "Acción no reconocida: " . htmlspecialchars($accion);
        }

    } catch (PDOException $e) {
        error_log("Error de BD en procesar_planificacion.php: " . $e->getMessage());
        $_SESSION['mensaje_error'] = "Error de base de datos. Contacte al administrador. Detalle: " . $e->getCode();
        if ($accion === 'registrar' || $accion === 'editar') {
             $redirect_url = $redirect_url_form_error ?? $redirect_url;
        }
    } catch (Exception $e) {
        $_SESSION['mensaje_error'] = "Error general: " . $e->getMessage();
         if ($accion === 'registrar' || $accion === 'editar') {
             $redirect_url = $redirect_url_form_error ?? $redirect_url;
        }
    }
    
    if(isset($_SESSION['mensaje_error_detalle'])) unset($_SESSION['mensaje_error_detalle']);
    if(isset($_SESSION['mensaje_error_flash_planif'])) unset($_SESSION['mensaje_error_flash_planif']);


    header('Location: ' . $redirect_url);
    exit;

} else {
    $_SESSION['mensaje_error'] = "Acceso no permitido.";
    header('Location: planificaciones.php');
    exit;
}

?>
