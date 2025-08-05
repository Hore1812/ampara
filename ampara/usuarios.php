<?php
// usuarios.php - The new "Controller"

// Step 1: Bootstrap the application
require_once __DIR__ . '/../vendor/autoload.php';
session_start();
require_once 'auth_check.php';

// Only admins can access this page
if ($_SESSION['tipo_usuario'] != 1) {
    $_SESSION['mensaje_error'] = "Acceso denegado.";
    header('Location: index.php');
    exit;
}

use Ampara\Repositories\UsuarioRepository;
use Ampara\Repositories\EmpleadoRepository;

// Step 2: Handle POST requests
$usuarioRepo = new UsuarioRepository();
$empleadoRepo = new EmpleadoRepository();
$id_usuario_logueado = $_SESSION['idemp'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $idusuario = filter_input(INPUT_POST, 'idusuario', FILTER_VALIDATE_INT);

    try {
        if ($accion === 'registrar' || $accion === 'editar') {
            $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING));
            $idemp = filter_input(INPUT_POST, 'idemp', FILTER_VALIDATE_INT);
            $tipo = filter_input(INPUT_POST, 'tipo', FILTER_VALIDATE_INT);
            $activo = isset($_POST['activo']) ? 1 : 0;

            if (empty($nombre) || empty($idemp) || $tipo === false) {
                throw new Exception("Nombre, empleado y tipo son campos obligatorios.");
            }

            $data = [
                'nombre' => $nombre,
                'idemp' => $idemp,
                'tipo' => $tipo,
                'activo' => $activo,
                'editor' => $id_usuario_logueado
            ];

            if ($accion === 'registrar') {
                $password = $_POST['password'] ?? '';
                if (empty($password) || strlen($password) < 8) {
                    throw new Exception("La contraseña es obligatoria y debe tener al menos 8 caracteres.");
                }
                $data['password'] = password_hash($password, PASSWORD_BCRYPT);
                $usuarioRepo->create($data);
                $_SESSION['mensaje_exito'] = 'Usuario registrado exitosamente.';
            } elseif ($idusuario) {
                $usuarioRepo->update($idusuario, $data);
                $_SESSION['mensaje_exito'] = 'Usuario actualizado exitosamente.';
            }

        } elseif ($accion === 'cambiar_password' && $idusuario) {
            $password = $_POST['password'] ?? '';
            if (empty($password) || strlen($password) < 8) {
                throw new Exception("La nueva contraseña es obligatoria y debe tener al menos 8 caracteres.");
            }
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $usuarioRepo->updatePassword($idusuario, $hashedPassword, $id_usuario_logueado);
            $_SESSION['mensaje_exito'] = 'Contraseña actualizada exitosamente.';

        } elseif (($accion === 'activar' || $accion === 'desactivar') && $idusuario) {
            $nuevo_estado = ($accion === 'activar') ? 1 : 0;
            $usuarioRepo->updateStatus($idusuario, $nuevo_estado, $id_usuario_logueado);
            $_SESSION['mensaje_exito'] = 'Estado del usuario actualizado exitosamente.';
        }
    } catch (Exception $e) {
        $_SESSION['mensaje_error'] = 'Error: ' . $e->getMessage();
    }

    header('Location: usuarios.php');
    exit;
}

// Step 3: Handle GET requests (Prepare data for the view)
$page_title = "Gestión de Usuarios";
$filtro_activo = $_GET['activo'] ?? '';
$filtros = ['activo' => $filtro_activo];

$usuarios = $usuarioRepo->getAll($filtros);
$empleados = $empleadoRepo->findActiveForSelect();

// Step 4: Load the View
require_once 'views/usuarios/index.php';
