<?php
require_once '../funciones.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idcolaborador'])) {
    try {
    $anio = $_POST['anio'] ?? null;
    $mes = $_POST['mes'] ?? null;
    $clienteIdcon = $_POST['clienteIdcon'] ?? null; // <--- Leer el nuevo parámetro cliente   
    $historico = obtenerHistoricoColaborador($_POST['idcolaborador'], $anio, $mes, $clienteIdcon ); // <--- Pasar clienteidcon a la función
    echo json_encode(['success' => true, 'data' => $historico]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido o parámetros faltantes']);
}
?>
