<?php
require_once '../funciones.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipohora'])) {
    try {
        $clientes = obtenerClientesPorTipoHora($_POST['tipohora']);
        echo json_encode(['success' => true, 'data' => $clientes]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido o parámetros faltantes']);
}
?>