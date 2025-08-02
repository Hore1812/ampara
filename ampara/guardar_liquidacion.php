<?php
session_start(); // Asegurar que la sesión esté iniciada para acceder a $_SESSION['idemp']
require_once 'funciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Añadir el idemp del usuario actual (editor) a los datos
        if (!isset($_SESSION['idemp'])) {
            // Esto no debería ocurrir si el usuario está logueado.
            // Manejar como un error o redirigir al login.
            throw new Exception("ID de usuario no encontrado en la sesión. Por favor, inicie sesión de nuevo.");
        }

        $datos = [
            'fecha' => $_POST['fecha'],
            'asunto' => $_POST['asunto'],
            'tema' => $_POST['tema'],
            'motivo' => $_POST['motivo'],
            'tipohora' => $_POST['tipohora'],
            'acargode' => $_POST['acargode'],
            'lider' => $_POST['lider'],
            'cantidahoras' => $_POST['cantidahoras'],
            'estado' => $_POST['estado'],
            'idcontratocli' => $_POST['cliente'],
            'editor' => $_SESSION['idemp'], // <-- NUEVO: Añadir el editor desde la sesión
            'colaboradores' => []
        ];
        
        if ($datos['estado'] == 'Completo' && isset($_POST['colaboradores'])) {
            $totalPorcentaje = 0;
            
            foreach ($_POST['colaboradores'] as $colab) {
                if (!empty($colab['id']) && !empty($colab['porcentaje'])) {
                    $datos['colaboradores'][] = [
                        'id' => $colab['id'],
                        'porcentaje' => $colab['porcentaje'],
                        'comentario' => $colab['comentario'] ?? ''
                    ];
                    $totalPorcentaje += (int)$colab['porcentaje']; // Asegurar que es numérico
                }
            }
            
            if ($totalPorcentaje != 100 && !empty($datos['colaboradores'])) { // Solo validar si hay colaboradores
                throw new Exception("La suma total de porcentajes de los colaboradores debe ser exactamente 100%. Actual: $totalPorcentaje%");
            }
        }
        
        $idLiquidacion = registrarLiquidacion($datos);
        
        // $_SESSION['mensaje_exito'] se establecerá en la página de listado por el JS
        // Aquí solo preparamos el mensaje para el JS.
        // No, mejor seguir con el plan original: el script de procesamiento setea la sesión
        // y la página de destino (o la misma página si hay error) la muestra.
        $_SESSION['mensaje_exito'] = "Liquidación registrada correctamente con ID: $idLiquidacion";
        header('Location: liquidaciones.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['mensaje_error'] = "Error al registrar la liquidación: " . $e->getMessage();
        header('Location: registrar_liquidacion.php'); // Redirige de vuelta al formulario
        exit;
    }
} else {
    // Si no es POST, redirigir (esto ya estaba)
    header('Location: registrar_liquidacion.php');
    exit;
}
?>
