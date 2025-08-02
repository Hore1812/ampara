<?php
session_start(); // Asegurar que la sesión esté iniciada para acceder a $_SESSION['idemp']
require_once 'funciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idliquidacion'])) {
    try {
        // Añadir el idemp del usuario actual (editor) a los datos
        if (!isset($_SESSION['idemp'])) {
            // Esto no debería ocurrir si el usuario está logueado.
            throw new Exception("ID de usuario no encontrado en la sesión. Por favor, inicie sesión de nuevo.");
        }
        $idLiquidacionFromPost = $_POST['idliquidacion']; // Guardar para la redirección en caso de error

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
            
            // Solo validar si realmente hay colaboradores definidos, 
            // ya que si el array está vacío, el total será 0 y podría dar un falso error.
            if (!empty($datos['colaboradores']) && $totalPorcentaje != 100) {
                throw new Exception("La suma total de porcentajes de los colaboradores debe ser exactamente 100%. Actual: $totalPorcentaje%");
            }
        }
        
        $actualizado = actualizarLiquidacion($idLiquidacionFromPost, $datos);
        
        if ($actualizado) {
            $_SESSION['mensaje_exito'] = "Liquidación ID: $idLiquidacionFromPost actualizada correctamente.";
        } else {
            // La función actualizarLiquidacion podría devolver false sin lanzar una excepción
            // si, por ejemplo, no se afectaron filas pero la consulta fue exitosa.
            // O podría lanzar una excepción si la consulta falla, que sería capturada abajo.
            // Considera si quieres un mensaje más específico o si la excepción es suficiente.
            throw new Exception("No se pudo actualizar la liquidación ID: $idLiquidacionFromPost. Es posible que no hubiera cambios que guardar o ocurrió un error.");
        }
        
        header('Location: liquidaciones.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['mensaje_error'] = "Error al actualizar la liquidación ID: $idLiquidacionFromPost. " . $e->getMessage();
        header('Location: editar_liquidacion.php?id=' . $idLiquidacionFromPost); // Redirige de vuelta al formulario de edición
        exit;
    }
} else {
    // Si no es POST o no está el idliquidacion, redirigir
    $_SESSION['mensaje_error'] = "Acceso no válido para actualizar liquidación.";
    header('Location: liquidaciones.php');
    exit;
}
?>
