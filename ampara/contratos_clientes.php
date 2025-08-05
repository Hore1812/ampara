<?php
// contratos_clientes.php - The new "Controller"

require_once __DIR__ . '/../vendor/autoload.php';
session_start();
require_once 'auth_check.php';

if ($_SESSION['tipo_usuario'] != 1) {
    $_SESSION['mensaje_error'] = "Acceso denegado.";
    header('Location: index.php');
    exit;
}

use Ampara\Repositories\ContratoClienteRepository;
use Ampara\Repositories\ClienteRepository;
use Ampara\Repositories\EmpleadoRepository;

$contratoRepo = new ContratoClienteRepository();
$clienteRepo = new ClienteRepository();
$empleadoRepo = new EmpleadoRepository();
$id_usuario_logueado = $_SESSION['idemp'] ?? 0;

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $idcontratocli = filter_input(INPUT_POST, 'idcontratocli', FILTER_VALIDATE_INT);

    try {
        if ($accion === 'registrar' || $accion === 'editar') {
            $data = [
                'idcliente' => filter_input(INPUT_POST, 'idcliente', FILTER_VALIDATE_INT),
                'lider' => filter_input(INPUT_POST, 'lider', FILTER_VALIDATE_INT),
                'descripcion' => trim($_POST['descripcion']),
                'fechainicio' => trim($_POST['fechainicio']),
                'fechafin' => empty(trim($_POST['fechafin'])) ? null : trim($_POST['fechafin']),
                'horasfijasmes' => filter_input(INPUT_POST, 'horasfijasmes', FILTER_VALIDATE_INT) ?? 0,
                'costohorafija' => filter_var($_POST['costohorafija'], FILTER_VALIDATE_FLOAT) ?? 0.0,
                'mesescontrato' => filter_input(INPUT_POST, 'mesescontrato', FILTER_VALIDATE_INT) ?? 0,
                'totalhorasfijas' => filter_input(INPUT_POST, 'totalhorasfijas', FILTER_VALIDATE_INT) ?? 0,
                'tipobolsa' => trim($_POST['tipobolsa']),
                'costohoraextra' => filter_var($_POST['costohoraextra'], FILTER_VALIDATE_FLOAT) ?? 0.0,
                'montofijomes' => filter_var($_POST['montofijomes'], FILTER_VALIDATE_FLOAT) ?? 0.0,
                'planmontomes' => filter_var($_POST['planmontomes'], FILTER_VALIDATE_FLOAT) ?? 0.0,
                'planhoraextrames' => filter_input(INPUT_POST, 'planhoraextrames', FILTER_VALIDATE_INT) ?? 0,
                'status' => trim($_POST['status']),
                'tipohora' => trim($_POST['tipohora']),
                'activo' => isset($_POST['activo']) ? 1 : 0,
                'editor' => $id_usuario_logueado
            ];

            if (empty($data['idcliente']) || empty($data['lider']) || empty($data['fechainicio'])) {
                throw new Exception("Cliente, Líder y Fecha de Inicio son campos obligatorios.");
            }

            if ($accion === 'registrar') {
                $contratoRepo->create($data);
                $_SESSION['mensaje_exito'] = 'Contrato registrado exitosamente.';
            } elseif ($idcontratocli) {
                $contratoRepo->update($idcontratocli, $data);
                $_SESSION['mensaje_exito'] = 'Contrato actualizado exitosamente.';
            }
        } elseif (($accion === 'activar' || $accion === 'desactivar') && $idcontratocli) {
            $nuevo_estado = ($accion === 'activar') ? 1 : 0;
            $contratoRepo->updateStatus($idcontratocli, $nuevo_estado, $id_usuario_logueado);
            $_SESSION['mensaje_exito'] = 'Estado del contrato actualizado exitosamente.';
        }
    } catch (Exception $e) {
        $_SESSION['mensaje_error'] = 'Error: ' . $e->getMessage();
    }

    header('Location: contratos_clientes.php');
    exit;
}

// Handle GET requests
$page_title = "Gestión de Contratos de Clientes";
$filtro_activo = $_GET['activo'] ?? '1'; // Default to showing active contracts
$filtro_lider = $_GET['id_lider_filtro'] ?? '';

$filtros = [
    'activo' => $filtro_activo,
    'id_lider_filtro' => $filtro_lider
];

$contratos = $contratoRepo->getAll($filtros);
$clientes = $clienteRepo->getAll(['activo' => 1]); // Get active clients for dropdown
$lideres = $empleadoRepo->findAllActive(); // Get active employees for dropdown

// Load the View
require_once 'views/contratos_clientes/index.php';
