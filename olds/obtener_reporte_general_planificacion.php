<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';
require_once '../funciones.php';

$response = [
    'success' => false,
    'message' => 'Petición no válida.',
    'data' => null,
    'debug_sql' => null,
    'debug_params' => null,
    'debug_mes_input' => null
];

if (!isset($_SESSION['idusuario'])) {
    $response['message'] = 'Acceso denegado. Sesión no iniciada.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anio = filter_input(INPUT_POST, 'anio', FILTER_VALIDATE_INT);
    $mes_input = filter_input(INPUT_POST, 'mes', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]);
    $idcliente_filtro = filter_input(INPUT_POST, 'idcliente', FILTER_VALIDATE_INT);

    $response['debug_mes_input'] = $mes_input;

    try {
        global $pdo;
        $sql_vista = "SELECT * FROM vista_reporte_planificacion_vs_liquidacion WHERE 1=1";
        $params_vista = [];

        if (!empty($anio)) {
            $sql_vista .= " AND AnioPlan = :anio";
            $params_vista[':anio'] = $anio;
        }

        error_log("Valor de mes_input antes del if: " . var_export($mes_input, true));
        if (!empty($mes_input)) {
            $sql_vista .= " AND MesPlanNumerico = :mes";
            $params_vista[':mes'] = (int)$mes_input;
            error_log("Filtro de mes añadido. SQL: " . $sql_vista);
        }
        
        if (!empty($idcliente_filtro)) {
            $stmt_contratos = $pdo->prepare("SELECT idcontratocli FROM contratocliente WHERE idcliente = :idcliente");
            $stmt_contratos->execute([':idcliente' => $idcliente_filtro]);
            $contratos_ids = $stmt_contratos->fetchAll(PDO::FETCH_COLUMN);
        
            if ($contratos_ids) {
                $in_placeholders = implode(',', array_fill(0, count($contratos_ids), '?'));
                $sql_vista .= " AND idContratoCliente IN ($in_placeholders)";
                
                $placeholders = [];
                foreach ($contratos_ids as $key => $id) {
                    $placeholder = ":idcontrato{$key}";
                    $placeholders[] = $placeholder;
                    $params_vista[$placeholder] = $id;
                }
                $sql_vista = str_replace($in_placeholders, implode(',', $placeholders), $sql_vista);
            } else {
                $sql_vista .= " AND 1=0"; 
            }
        }
        
        // Guardar consulta y parámetros para depuración ANTES de ejecutar
        $response['debug_sql'] = $sql_vista;
        $response['debug_params'] = $params_vista;

        $stmt_vista = $pdo->prepare($sql_vista);
        $stmt_vista->execute($params_vista);
        $resultados_vista = $stmt_vista->fetchAll(PDO::FETCH_ASSOC);

        $default_data_structure = [
            'kpis' => [
                'total_planificadas' => 0, 
                'total_liquidadas_completas' => 0, 
                'total_liquidadas_todos_estados' => 0, 
                'porcentaje_cumplimiento' => 0,
                'porcentaje_cumplimiento_todos_estados' => 0 
            ],
            'por_cliente' => ['labels' => [], 'datasets' => []],
            'por_estado_general' => ['labels' => [], 'valores' => [], 'colores' => []],
            'lista_estados' => []
        ];

        if (empty($resultados_vista)) {
            $response['message'] = 'No se encontraron datos para los filtros seleccionados (debug).'; // Mensaje ajustado para depuración
            $response['data'] = $default_data_structure;
            $response['success'] = true;
            // No hacer exit aquí para permitir que el segundo fetch se ejecute
        }
        
        $kpis = $default_data_structure['kpis'];
        $data_por_cliente_agrupada = [];
        $data_por_estado_general = [];
        $todos_los_estados_presentes = [];
        $ids_planificacion_procesados_kpi = [];
        $ids_planificacion_procesados_cliente = [];

        foreach($resultados_vista as $fila) {
            $idplan = $fila['Idplanificacion'];
            $cliente_nombre = $fila['NombreCliente'];
            $estado_liquidacion = $fila['EstadoLiquidacion'] ?: 'Sin Liquidaciones';
            $horas_planificadas_fila = floatval($fila['HorasPlanificadas']);
            $horas_liquidadas_estado_fila = floatval($fila['HorasLiquidadasPorEstado']);

            if(!in_array($estado_liquidacion, $todos_los_estados_presentes)){
                $todos_los_estados_presentes[] = $estado_liquidacion;
            }

            if (!in_array($idplan, $ids_planificacion_procesados_kpi)) {
                $kpis['total_planificadas'] += $horas_planificadas_fila;
                $ids_planificacion_procesados_kpi[] = $idplan;
            }

            $kpis['total_liquidadas_todos_estados'] += $horas_liquidadas_estado_fila;
            if ($estado_liquidacion == 'Completo') {
                $kpis['total_liquidadas_completas'] += $horas_liquidadas_estado_fila;
            }

            if (!isset($data_por_cliente_agrupada[$cliente_nombre])) {
                $data_por_cliente_agrupada[$cliente_nombre] = ['planificadas' => 0, 'liquidadas_por_estado' => []];
            }
            
            $clave_cliente_plan = $cliente_nombre . '_' . $idplan;
            if(!in_array($clave_cliente_plan, $ids_planificacion_procesados_cliente)){
                $data_por_cliente_agrupada[$cliente_nombre]['planificadas'] += $horas_planificadas_fila;
                $ids_planificacion_procesados_cliente[] = $clave_cliente_plan;
            }
            
            if (!isset($data_por_cliente_agrupada[$cliente_nombre]['liquidadas_por_estado'][$estado_liquidacion])) {
                $data_por_cliente_agrupada[$cliente_nombre]['liquidadas_por_estado'][$estado_liquidacion] = 0;
            }
            $data_por_cliente_agrupada[$cliente_nombre]['liquidadas_por_estado'][$estado_liquidacion] += $horas_liquidadas_estado_fila;

            if (!isset($data_por_estado_general[$estado_liquidacion])) {
                $data_por_estado_general[$estado_liquidacion] = 0;
            }
            $data_por_estado_general[$estado_liquidacion] += $horas_liquidadas_estado_fila;
        }

        if ($kpis['total_planificadas'] > 0) {
            $kpis['porcentaje_cumplimiento'] = ($kpis['total_liquidadas_completas'] / $kpis['total_planificadas']) * 100;
            $kpis['porcentaje_cumplimiento_todos_estados'] = ($kpis['total_liquidadas_todos_estados'] / $kpis['total_planificadas']) * 100;
        }
        
        sort($todos_los_estados_presentes);

        $colores_estado_map = [
            'Completo' => 'rgba(75, 192, 192, 0.7)', 'En proceso' => 'rgba(54, 162, 235, 0.7)',
            'En revisión' => 'rgba(255, 206, 86, 0.7)', 'Programado' => 'rgba(255, 99, 132, 0.7)',
            'Sin Liquidaciones' => 'rgba(153, 102, 255, 0.7)', 'Default' => 'rgba(201, 203, 207, 0.7)'
        ];
        $colores_ciclo_disponibles = array_values($colores_estado_map);
        $color_idx_estado = 0;

        $chart_por_cliente_labels = array_keys($data_por_cliente_agrupada);
        $chart_por_cliente_datasets = [];
        $planificadas_data_cliente = [];
        foreach ($chart_por_cliente_labels as $cn) {
            $planificadas_data_cliente[] = $data_por_cliente_agrupada[$cn]['planificadas'];
        }
        $chart_por_cliente_datasets[] = [
            'label' => 'Horas Planificadas', 'data' => $planificadas_data_cliente,
            'backgroundColor' => 'rgba(128, 128, 128, 0.5)', 'borderColor' => 'rgba(128, 128, 128, 1)',
            'borderWidth' => 1, 'stack' => 'stack_planificadas_cliente'
        ];

        foreach ($todos_los_estados_presentes as $estado) {
            $liquidadas_data_estado = [];
            foreach ($chart_por_cliente_labels as $cliente_nombre) {
                $liquidadas_data_estado[] = $data_por_cliente_agrupada[$cliente_nombre]['liquidadas_por_estado'][$estado] ?? 0;
            }
            $color_actual = $colores_estado_map[$estado] ?? $colores_ciclo_disponibles[$color_idx_estado % count($colores_ciclo_disponibles)];
            $chart_por_cliente_datasets[] = [
                'label' => 'Liq. - ' . $estado, 'data' => $liquidadas_data_estado,
                'backgroundColor' => $color_actual, 'borderColor' => str_replace('0.7', '1', $color_actual),
                'borderWidth' => 1, 'stack' => 'stack_liquidadas_cliente'
            ];
            $color_idx_estado++;
        }
        
        $final_chart_por_cliente = ['labels' => $chart_por_cliente_labels, 'datasets' => $chart_por_cliente_datasets];

        $chart_por_estado_general = ['labels' => [], 'valores' => [], 'colores' => []];
        $color_idx_pie = 0;
        ksort($data_por_estado_general); 
        foreach($data_por_estado_general as $estado => $valor){
            $chart_por_estado_general['labels'][] = $estado;
            $chart_por_estado_general['valores'][] = $valor;
            $chart_por_estado_general['colores'][] = $colores_estado_map[$estado] ?? $colores_ciclo_disponibles[$color_idx_pie % count($colores_ciclo_disponibles)];
            $color_idx_pie++;
        }

        $response['success'] = true;
        $response['message'] = 'Datos del reporte general obtenidos.';
        $response['data'] = [
            'kpis' => $kpis,
            'por_cliente' => $final_chart_por_cliente,
            'por_estado_general' => $chart_por_estado_general,
            'lista_estados' => $todos_los_estados_presentes
        ];
        error_log("Datos enviados: " . json_encode($response['data']));

    } catch (PDOException $e) {
        error_log("Error de BD en ajax/obtener_reporte_general_planificacion.php: " . $e->getMessage());
        $response['message'] = 'Error de base de datos al generar el reporte. Código: ' . $e->getCode();
        $response['data'] = $default_data_structure;
        $response['debug_error_pdo'] = $e->getMessage();
    } catch (Exception $e) {
        error_log("Error general en ajax/obtener_reporte_general_planificacion.php: " . $e->getMessage());
        $response['message'] = 'Error inesperado al procesar la solicitud.';
        $response['data'] = $default_data_structure;
        $response['debug_error_general'] = $e->getMessage();
    }
}

echo json_encode($response);
?>
