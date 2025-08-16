<?php
require_once '../funciones.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idliquidacion'])) {
    try {
        $colaboradores = obtenerColaboradoresPorLiquidacion($_POST['idliquidacion']);
        echo json_encode(['success' => true, 'data' => $colaboradores]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido o parámetros faltantes']);
}
?>