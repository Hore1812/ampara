<?php
require_once '../funciones.php';
require_once '../conexion.php'; // Necesario para usar $pdo

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idliquidacion'])) {
    try {
        $idLiquidacion = $_POST['idliquidacion'];
        
        // Obtener los colaboradores como antes
        $colaboradores = obtenerColaboradoresPorLiquidacion($idLiquidacion);
        
        // Obtener el total de horas de la liquidación
        $stmt_horas = $pdo->prepare("SELECT cantidahoras FROM liquidacion WHERE idliquidacion = ?");
        $stmt_horas->execute([$idLiquidacion]);
        $liquidacion_info = $stmt_horas->fetch(PDO::FETCH_ASSOC);
        
        $total_horas = $liquidacion_info ? $liquidacion_info['cantidahoras'] : 0;

        // Devolver todo en la respuesta JSON
        echo json_encode([
            'success' => true, 
            'data' => $colaboradores,
            'total_horas' => $total_horas
        ]);

    } catch (Exception $e) {
        error_log("Error en obtener_colaboradores.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ocurrió un error al obtener los datos.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido o parámetros faltantes']);
}
?>