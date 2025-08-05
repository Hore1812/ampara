<?php
// clientes.php - The new "Controller"

require_once __DIR__ . '/../vendor/autoload.php';
session_start();
require_once 'auth_check.php';

// Only admins can access this page
if ($_SESSION['tipo_usuario'] != 1) {
    $_SESSION['mensaje_error'] = "Acceso denegado.";
    header('Location: index.php');
    exit;
}

use Ampara\Repositories\ClienteRepository;

$clienteRepo = new ClienteRepository();
$id_usuario_logueado = $_SESSION['idemp'] ?? 0;

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $idcliente = filter_input(INPUT_POST, 'idcliente', FILTER_VALIDATE_INT);

    try {
        if ($accion === 'registrar' || $accion === 'editar') {
            $data = [
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
                'editor' => $id_usuario_logueado
            ];

            if (empty($data['razonsocial']) || empty($data['nombrecomercial']) || empty($data['ruc'])) {
                throw new Exception("Razón Social, Nombre Comercial y RUC son campos obligatorios.");
            }

            if ($accion === 'registrar') {
                $clienteRepo->create($data);
                $_SESSION['mensaje_exito'] = 'Cliente registrado exitosamente.';
            } elseif ($idcliente) {
                $clienteRepo->update($idcliente, $data);
                $_SESSION['mensaje_exito'] = 'Cliente actualizado exitosamente.';
            }
        } elseif (($accion === 'activar' || $accion === 'desactivar') && $idcliente) {
            $nuevo_estado = ($accion === 'activar') ? 1 : 0;
            $clienteRepo->updateStatus($idcliente, $nuevo_estado, $id_usuario_logueado);
            $_SESSION['mensaje_exito'] = 'Estado del cliente actualizado exitosamente.';
        }
    } catch (Exception $e) {
        $_SESSION['mensaje_error'] = 'Error: ' . $e->getMessage();
    }

    header('Location: clientes.php');
    exit;
}

// Handle GET requests
$page_title = "Gestión de Clientes";
$filtro_activo = $_GET['activo'] ?? '';
$filtros = ['activo' => $filtro_activo];

$clientes = $clienteRepo->getAll($filtros);

// Load the View
require_once 'views/clientes/index.php';
