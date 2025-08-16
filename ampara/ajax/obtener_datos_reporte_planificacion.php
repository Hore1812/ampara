<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php'; // Ajustar ruta si es necesario
require_once '../funciones.php'; // Ajustar ruta si es necesario

$response = ['success' => false, 'message' => 'Petición no válida.', 'data' => null];

if (!isset($_SESSION['idusuario'])) {
    $response['message'] = 'Acceso denegado. Sesión no iniciada.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idplanificacion = filter_input(INPUT_POST, 'idplanificacion', FILTER_VALIDATE_INT);

    if (empty($idplanificacion)) {
        $response['message'] = 'ID de Planificación no proporcionado.';
        echo json_encode($response);
        exit;
    }

    try {
        global $pdo;
        // La consulta ahora SIEMPRE se filtra por Idplanificacion
        $sql_vista = "SELECT * FROM vista_reporte_planificacion_vs_liquidacion WHERE Idplanificacion = :idplanificacion";
        $params_vista = [':idplanificacion' => $idplanificacion];

        $stmt_vista = $pdo->prepare($sql_vista);
        $stmt_vista->execute($params_vista);
        $resultados_vista = $stmt_vista->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados_vista)) {
            $response['message'] = 'No se encontraron datos para la planificación ID: ' . $idplanificacion;
            // Aún así, devolvemos una estructura de datos válida para que el JS no falle
            $response['data'] = [
                'totales' => ['total_horas_planificadas' => 0, 'total_horas_liquidadas_todos_estados' => 0, 'porcentaje_cumplimiento_general' => 0],
                'grafico_comparativo' => ['labels' => [], 'planificadas' => [], 'liquidadas' => [], 'colores_liquidadas' => []],
                'grafico_distribucion' => ['labels' => [], 'valores' => [], 'colores' => []]
            ];
            $response['success'] = true; // La consulta fue exitosa, aunque no arrojó datos
            echo json_encode($response);
            exit;
        }

        $labels_comparativo = [];
        $datos_planificadas_comparativo = []; // Será el total del plan, repetido por cada estado
        $datos_liquidadas_comparativo = [];
        $colores_barras_liquidadas = []; // Colores para cada barra de estado liquidado
        
        $labels_distribucion = [];
        $valores_distribucion = [];
        $colores_pie_distribucion = [];

        // Colores predefinidos para los estados (puedes expandir esta lista)
        $colores_por_estado = [
            'Programado' => 'rgba(255, 99, 132, 0.7)',  // Rojo
            'En revisión' => 'rgba(255, 206, 86, 0.7)', // Amarillo
            'En proceso' => 'rgba(75, 192, 192, 0.7)', // Verde/Turquesa
            'Completo' => 'rgba(54, 162, 235, 0.7)',  // Azul
            'Sin Liquidaciones' => 'rgba(153, 102, 255, 0.7)',// Morado
            'Default' => 'rgba(201, 203, 207, 0.7)' // Gris para estados no mapeados
        ];
        $color_index = 0;
        $colores_disponibles_ciclo = array_values($colores_por_estado); // Para ciclar si hay más estados que colores definidos

        $total_general_planificadas = 0;
        // total_horas_liquidadas_todos_estados: Suma de HorasLiquidadasPorEstado de TODOS los estados para ESTA planificación
        $total_horas_liquidadas_todos_estados = 0; 

        // Como filtramos por UNA planificación, HorasPlanificadas será la misma en todas las filas de la vista para ese plan.
        // Tomamos el valor de la primera fila.
        $total_general_planificadas = floatval($resultados_vista[0]['HorasPlanificadas']);

        foreach ($resultados_vista as $fila) {
            $estado = $fila['EstadoLiquidacion'] ?: 'Sin Liquidaciones';
            $horas_liquidadas_estado = floatval($fila['HorasLiquidadasPorEstado']);
            
            $total_horas_liquidadas_todos_estados += $horas_liquidadas_estado;

            // Datos para el gráfico de barras
            $labels_comparativo[] = $estado;
            $datos_planificadas_comparativo[] = $total_general_planificadas; // El total del plan se compara con cada estado
            $datos_liquidadas_comparativo[] = $horas_liquidadas_estado;
            $colores_barras_liquidadas[] = $colores_por_estado[$estado] ?? $colores_disponibles_ciclo[$color_index % count($colores_disponibles_ciclo)];

            // Datos para el gráfico de pie (solo si hay horas liquidadas para ese estado)
            if ($horas_liquidadas_estado > 0) {
                $labels_distribucion[] = $estado;
                $valores_distribucion[] = $horas_liquidadas_estado;
                $colores_pie_distribucion[] = $colores_por_estado[$estado] ?? $colores_disponibles_ciclo[$color_index % count($colores_disponibles_ciclo)];
            }
            $color_index++;
        }
        
        $porcentaje_cumplimiento_general = ($total_general_planificadas > 0) ? ($total_horas_liquidadas_todos_estados / $total_general_planificadas) * 100 : 0;

        $response['success'] = true;
        $response['message'] = 'Datos obtenidos correctamente.';
        $response['data'] = [
            'totales' => [
                'total_horas_planificadas' => $total_general_planificadas,
                'total_horas_liquidadas_todos_estados' => $total_horas_liquidadas_todos_estados,
                'porcentaje_cumplimiento_general' => $porcentaje_cumplimiento_general
            ],
            'grafico_comparativo' => [
                'labels' => $labels_comparativo,
                'planificadas' => $datos_planificadas_comparativo, // Esto será un array con el mismo valor repetido
                'liquidadas' => $datos_liquidadas_comparativo,
                'colores_liquidadas' => $colores_barras_liquidadas
            ],
            'grafico_distribucion' => [ // Para el gráfico de pie/dona
                'labels' => $labels_distribucion,
                'valores' => $valores_distribucion,
                'colores' => $colores_pie_distribucion
            ]
        ];

    } catch (PDOException $e) {
        error_log("Error de BD en ajax/obtener_datos_reporte_planificacion.php: " . $e->getMessage() . " SQL: " . ($sql_vista ?? 'N/A'));
        $response['message'] = 'Error de base de datos al obtener los datos del reporte. Código: ' . $e->getCode();
    } catch (Exception $e) {
        error_log("Error general en ajax/obtener_datos_reporte_planificacion.php: " . $e->getMessage());
        $response['message'] = 'Error inesperado al procesar la solicitud.';
    }
}

echo json_encode($response);
?>
