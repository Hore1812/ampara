<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php'; 
require_once 'auth_check.php'; 

$accion = $_POST['accion'] ?? null;
$idempleado = isset($_POST['idempleado']) ? filter_var($_POST['idempleado'], FILTER_VALIDATE_INT) : null;
$editor = $_SESSION['idemp'] ?? 0;

try {
    if ($accion === 'activar' || $accion === 'desactivar') {
        if (!$idempleado) {
            throw new Exception("ID de empleado no proporcionado para la acción de estado.");
        }
        if ($accion === 'activar') {
            if (activarEmpleado($idempleado, $editor)) {
                $_SESSION['mensaje_exito'] = "Empleado activado correctamente.";
            } else {
                $_SESSION['mensaje_error'] = "Error al activar el empleado.";
            }
        } else { // desactivar
            if (desactivarEmpleado($idempleado, $editor)) {
                $_SESSION['mensaje_exito'] = "Empleado desactivado correctamente.";
            } else {
                $_SESSION['mensaje_error'] = "Error al desactivar el empleado.";
            }
        }
    } elseif ($accion === 'crear' || $accion === 'actualizar') {
        // Recolección y validación de datos...
        $datos_empleado = [
            'nombres' => $_POST['nombres'] ?? null, 'paterno' => $_POST['paterno'] ?? null, 'materno' => $_POST['materno'] ?? null,
            'nombrecorto' => $_POST['nombrecorto'] ?? null, 'dni' => $_POST['dni'] ?? null, 'nacimiento' => $_POST['nacimiento'] ?? null,
            'lugarnacimiento' => $_POST['lugarnacimiento'] ?? null, 'domicilio' => $_POST['domicilio'] ?? null,
            'estadocivil' => $_POST['estadocivil'] ?? null, 'correopersonal' => $_POST['correopersonal'] ?? null,
            'correocorporativo' => $_POST['correocorporativo'] ?? null, 'telcelular' => $_POST['telcelular'] ?? null,
            'telfijo' => $_POST['telfijo'] ?? null, 'area' => $_POST['area'] ?? null, 'cargo' => $_POST['cargo'] ?? null,
            'horasmeta' => filter_var($_POST['horasmeta'] ?? 30, FILTER_VALIDATE_INT),
            'derechohabiente' => $_POST['derechohabiente'] ?? null,
            'cantidadhijos' => filter_var($_POST['cantidadhijos'] ?? 0, FILTER_VALIDATE_INT),
            'contactoemergencia' => $_POST['contactoemergencia'] ?? null, 'nivelestudios' => $_POST['nivelestudios'] ?? null,
            'regimenpension' => $_POST['regimenpension'] ?? null, 'fondopension' => $_POST['fondopension'] ?? null,
            'cussp' => $_POST['cussp'] ?? null, 'modalidad' => $_POST['modalidad'] ?? null,
            'activo' => isset($_POST['activo']) ? 1 : 0, 'editor' => $editor
        ];
        // --- MANEJO DE SUBIDA DE FOTO ---
        $nombre_archivo_foto_final = $_POST['rutafoto_actual'] ?? 'img/fotos/empleados/default_avatar.png';
        if (isset($_FILES['rutafoto']) && $_FILES['rutafoto']['error'] == UPLOAD_ERR_OK) {
            $directorio_fotos = "img/fotos/empleados/";
            if (!is_dir($directorio_fotos)) {
                mkdir($directorio_fotos, 0777, true);
            }
            
            $nombre_archivo_original = basename($_FILES['rutafoto']['name']);
            $ruta_destino_final = $directorio_fotos . $nombre_archivo_original;
            
            // Mover el archivo, sobreescribiendo si ya existe.
            if (move_uploaded_file($_FILES['rutafoto']['tmp_name'], $ruta_destino_final)) {
                $nombre_archivo_foto_final = $ruta_destino_final;
            } else {
                $_SESSION['mensaje_error'] = "Error al mover el archivo de la foto.";
                // Redirigir para evitar que el resto del script se ejecute con datos incorrectos
                header('Location: ' . ($accion === 'crear' ? 'registrar_empleado.php' : 'editar_empleado.php?id=' . $idempleado));
                exit;
            }
        }
        $datos_empleado['rutafoto'] = $nombre_archivo_foto_final;


        if ($accion === 'crear') {
            if (registrarEmpleado($datos_empleado)) {
                $_SESSION['mensaje_exito'] = "Empleado registrado con éxito.";
            } else {
                $_SESSION['mensaje_error'] = "Error al registrar empleado.";
            }
        } else { // actualizar
            if (actualizarEmpleado($idempleado, $datos_empleado)) {
                $_SESSION['mensaje_exito'] = "Empleado actualizado con éxito.";
            } else {
                $_SESSION['mensaje_error'] = "Error al actualizar o no se realizaron cambios.";
            }
        }
    } else {
        $_SESSION['mensaje_error'] = "Acción no reconocida.";
    }
} catch (Exception $e) {
    error_log("Error en procesar_empleado.php: " . $e->getMessage());
    $_SESSION['mensaje_error'] = "Ocurrió un error inesperado.";
}

header('Location: empleados.php');
exit;
?>
