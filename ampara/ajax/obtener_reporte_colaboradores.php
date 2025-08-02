<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';
require_once '../funciones.php';

$response = [
    'success' => false,
    'message' => 'Petición no válida.',
    'data' => null
];

if (!isset($_SESSION['idusuario'])) {
    $response['message'] = 'Acceso denegado. Sesión no iniciada.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anio = filter_input(INPUT_POST, 'anio', FILTER_VALIDATE_INT);
    $mes = filter_input(INPUT_POST, 'mes', FILTER_VALIDATE_INT);

    try {
        global $pdo;
        
        $sql = "SELECT NombreColaborador, HorasMeta, HorasCompletadas, PorcentajeCumplimiento 
                FROM vista_progreso_colaborador_vs_meta 
                WHERE 1=1";
        
        $params = [];
        if (!empty($anio)) {
            $sql .= " AND Anio = :anio";
            $params[':anio'] = $anio;
        }
        if (!empty($mes)) {
            $sql .= " AND Mes = :mes";
            $params[':mes'] = $mes;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            $response['message'] = 'No se encontraron datos de colaboradores para los filtros seleccionados.';
            $response['success'] = true;
            $response['data'] = [];
            echo json_encode($response);
            exit;
        }

        $response['success'] = true;
        $response['message'] = 'Datos de colaboradores obtenidos correctamente.';
        $response['data'] = $resultados;

    } catch (PDOException $e) {
        error_log("Error de BD en ajax/obtener_reporte_colaboradores.php: " . $e->getMessage());
        $response['message'] = 'Error de base de datos al generar el reporte de colaboradores.';
    } catch (Exception $e) {
        error_log("Error general en ajax/obtener_reporte_colaboradores.php: " . $e->getMessage());
        $response['message'] = 'Error inesperado al procesar la solicitud.';
    }
}

echo json_encode($response);
?>
