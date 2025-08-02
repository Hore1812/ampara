<?php
header('Content-Type: application/json');
session_start();
require_once 'conexion.php'; // Ajustar ruta si es necesario
require_once 'funciones.php'; // Ajustar ruta si es necesario

$response = ['success' => false, 'message' => 'Error desconocido.'];

// Verificar que el usuario esté logueado (opcional aquí, pero buena práctica)
if (!isset($_SESSION['idusuario'])) {
    $response['message'] = 'Acceso no autorizado.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idplanificacion'])) {
    $idplanificacion = filter_input(INPUT_POST, 'idplanificacion', FILTER_VALIDATE_INT);

    if ($idplanificacion) {
        try {
            $datos_completos = obtenerDetallesCompletosPlanificacion($idplanificacion);

            if ($datos_completos && $datos_completos['planificacion']) {
                $response['success'] = true;
                $response['data'] = $datos_completos;
                $response['message'] = 'Datos obtenidos correctamente.';
            } else {
                $response['message'] = $_SESSION['mensaje_error_detalle'] ?? 'No se encontraron datos para la planificación solicitada o ocurrió un error.';
                unset($_SESSION['mensaje_error_detalle']);
            }
        } catch (PDOException $e) {
            error_log("Error de BD en ajax/obtener_detalles_planificacion_completa.php: " . $e->getMessage());
            $response['message'] = 'Error de base de datos al obtener los detalles.';
        } catch (Exception $e) {
            error_log("Error general en ajax/obtener_detalles_planificacion_completa.php: " . $e->getMessage());
            $response['message'] = 'Error general al procesar la solicitud: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'ID de planificación no válido o no proporcionado.';
    }
} else {
    $response['message'] = 'Método de solicitud no válido.';
}

echo json_encode($response);
?>
