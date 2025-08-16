<?php
require_once 'funciones.php';

session_start();

if (!isset($_SESSION['idusuario']) || $_SESSION['tipo_usuario'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

if (ejecutarActualizacionDetalles()) {
    echo json_encode(['success' => true, 'message' => 'Los detalles se han actualizado correctamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Hubo un error al ejecutar el procedimiento almacenado.']);
}
?>