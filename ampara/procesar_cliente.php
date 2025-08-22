<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php';
// require_once 'auth_check.php'; // Optional: Uncomment if direct access to this script should be protected

$id_usuario_editor = $_SESSION['idusuario'] ?? 1; // Default to 1 if not set

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $redirect_url = 'clientes.php'; // Always redirect back to the clients list

    try {
        if ($accion === 'activar' || $accion === 'desactivar') {
            $idcliente = filter_input(INPUT_POST, 'idcliente', FILTER_VALIDATE_INT);

            if (empty($idcliente)) {
                $_SESSION['mensaje_error'] = "ID de cliente no válido.";
                header('Location: ' . $redirect_url);
                exit;
            }

            $nuevo_estado = ($accion === 'activar') ? 1 : 0;
            $texto_accion = ($accion === 'activar') ? 'activado' : 'desactivado';

            // This function will be checked/created in the next step
            if (actualizarEstadoCliente($idcliente, $nuevo_estado, $id_usuario_editor)) {
                $_SESSION['mensaje_exito'] = "Cliente $texto_accion exitosamente.";
            } else {
                $_SESSION['mensaje_error'] = "Error al cambiar el estado del cliente.";
            }
        } elseif ($accion === 'registrar' || $accion === 'crear' || $accion === 'editar' || $accion === 'actualizar') {
            $idcliente = filter_input(INPUT_POST, 'idcliente', FILTER_VALIDATE_INT);

            // Basic validation
            if (empty($_POST['razonsocial']) || empty($_POST['nombrecomercial']) || empty($_POST['ruc'])) {
                $_SESSION['mensaje_error'] = "Los campos Razón Social, Nombre Comercial y RUC son obligatorios.";
                header('Location: ' . ($idcliente ? "editar_cliente.php?id=$idcliente" : 'registrar_cliente.php'));
                exit;
            }

            $datos_cliente = [
                'razonsocial' => trim($_POST['razonsocial']),
                'nombrecomercial' => trim($_POST['nombrecomercial']),
                'ruc' => trim($_POST['ruc']),
                'direccion' => trim($_POST['direccion']),
                'telefono' => trim($_POST['telefono']),
                'sitioweb' => trim($_POST['sitioweb']),
                'representante' => trim($_POST['representante']),
                'telrepresentante' => trim($_POST['telrepresentante']),
                'correorepre' => trim($_POST['correorepre']),
                'gerente' => trim($_POST['gerente']),
                'telgerente' => trim($_POST['telgerente']),
                'correogerente' => trim($_POST['correogerente']),
                'activo' => isset($_POST['activo']) ? 1 : 0,
                'editor' => $id_usuario_editor
            ];

            if ($accion === 'editar' || $accion === 'actualizar') {
                if (!$idcliente) {
                    $_SESSION['mensaje_error'] = "ID de cliente no válido para actualizar.";
                    header('Location: clientes.php');
                    exit;
                }
                if (actualizarCliente($idcliente, $datos_cliente)) {
                    $_SESSION['mensaje_exito'] = "Cliente actualizado exitosamente.";
                } else {
                    $_SESSION['mensaje_error'] = "Error al actualizar el cliente o no se realizaron cambios.";
                }
                $redirect_url = "clientes.php?highlight_id=$idcliente";
            } elseif ($accion === 'registrar' || $accion === 'crear') {
                $nuevo_id = registrarCliente($datos_cliente);
                if ($nuevo_id) {
                    $_SESSION['mensaje_exito'] = "Cliente registrado exitosamente con ID: " . $nuevo_id;
                    $redirect_url = "clientes.php?highlight_id=$nuevo_id";
                } else {
                    $_SESSION['mensaje_error'] = "Error al registrar el nuevo cliente.";
                    $redirect_url = 'registrar_cliente.php';
                }
            }

        } else {
            $_SESSION['mensaje_error'] = "Acción no reconocida.";
        }
    } catch (Exception $e) {
        error_log("Error en procesar_cliente.php: " . $e->getMessage());
        $_SESSION['mensaje_error'] = "Ocurrió un error inesperado. Por favor, contacte al administrador.";
    }

    header('Location: ' . $redirect_url);
    exit;

} else {
    // Prevent GET access
    $_SESSION['mensaje_error'] = "Acceso no permitido.";
    header('Location: clientes.php');
    exit;
}
?>
