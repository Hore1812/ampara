<?php
// temas.php - The new "Controller"

// Step 1: Bootstrap the application
require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoloader
session_start();
require_once 'auth_check.php'; // Existing auth check

// Use the new namespaced classes
use Ampara\Repositories\TemaRepository;
use Ampara\Repositories\EmpleadoRepository;

// Step 2: Handle POST requests (logic from procesar_tema.php is now here)
$temaRepo = new TemaRepository();
$empleadoRepo = new EmpleadoRepository();
$id_usuario_logueado = $_SESSION['idemp'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $idtema = filter_input(INPUT_POST, 'idtema', FILTER_VALIDATE_INT);

    try {
        if ($accion === 'registrar' || $accion === 'editar') {
            $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING));
            $idencargado = filter_input(INPUT_POST, 'idencargado', FILTER_VALIDATE_INT);
            $comentario = trim(filter_input(INPUT_POST, 'comentario', FILTER_SANITIZE_STRING));
            $activo = isset($_POST['activo']) ? 1 : 0;

            if (empty($descripcion)) {
                throw new Exception("La descripción es obligatoria.");
            }

            $data = [
                'descripcion' => $descripcion,
                'idencargado' => $idencargado,
                'comentario' => $comentario,
                'activo' => $activo,
                'editor' => $id_usuario_logueado
            ];

            if ($accion === 'registrar') {
                $temaRepo->create($data);
                $_SESSION['mensaje_exito'] = 'Tema registrado exitosamente.';
            } elseif ($idtema) {
                $temaRepo->update($idtema, $data);
                $_SESSION['mensaje_exito'] = 'Tema actualizado exitosamente.';
            }

        } elseif (($accion === 'activar' || $accion === 'desactivar') && $idtema) {
            $nuevo_estado = ($accion === 'activar') ? 1 : 0;
            $temaRepo->updateStatus($idtema, $nuevo_estado, $id_usuario_logueado);
            $_SESSION['mensaje_exito'] = 'Estado del tema actualizado exitosamente.';
        }
    } catch (Exception $e) {
        $_SESSION['mensaje_error'] = 'Error: ' . $e->getMessage();
    }

    // Redirect to the same page to prevent form resubmission
    header('Location: temas.php');
    exit;
}


// Step 3: Handle GET requests (Prepare data for the view)
$page_title = "Gestión de Temas";

// Get filter values from GET request
$filtro_descripcion = $_GET['descripcion'] ?? '';
$filtro_encargado = $_GET['idencargado'] ?? '';
$filtro_activo = $_GET['activo'] ?? '';

$filtros = [
    'descripcion' => $filtro_descripcion,
    'idencargado' => $filtro_encargado,
    'activo' => $filtro_activo
];

// Fetch data using repositories
$temas = $temaRepo->getAll($filtros);
// Use the new method to get employees with their theme counts for the filter
$empleados = $empleadoRepo->findActiveWithTemaCount();

// Step 4: Load the View
// The view file will have access to all variables defined above ($page_title, $temas, $empleados, etc.)
require_once 'views/temas/index.php';
