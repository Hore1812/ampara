<?php
require_once 'funciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idliquidacion'])) {
    try {
        $eliminado = desactivarLiquidacion($_POST['idliquidacion'], $_SESSION['idemp']);
        
        if ($eliminado) {
            $_SESSION['mensaje_exito'] = "Liquidación eliminada correctamente";
        } else {
            throw new Exception("No se pudo eliminar la liquidación");
        }
    } catch (Exception $e) {
        $_SESSION['mensaje_error'] = "Error al eliminar la liquidación: " . $e->getMessage();
    }
}

header('Location: liquidaciones.php');
exit;
?>