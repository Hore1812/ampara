<?php
require_once '../funciones.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idtema'])) {
    try {
        $encargado = obtenerEncargadoPorTema($_POST['idtema']);
        echo json_encode(['success' => true, 'data' => $encargado]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido o parámetros faltantes']);
}
?>