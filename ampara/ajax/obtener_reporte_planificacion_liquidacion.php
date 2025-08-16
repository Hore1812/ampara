<?php
require_once '../conexion.php';

header('Content-Type: application/json');

// Sanitización de Parámetros
$anio = filter_input(INPUT_POST, 'anio', FILTER_VALIDATE_INT, ['options' => ['default' => date('Y')]]);
$mes = filter_input(INPUT_POST, 'mes', FILTER_VALIDATE_INT);
$idCliente = filter_input(INPUT_POST, 'idcliente', FILTER_VALIDATE_INT);

$response = ['success' => false, 'message' => 'No se encontraron datos para los filtros seleccionados.', 'data' => null];

try {
    // 1. Obtener todos los planes para el período y cliente (si se especifica)
    $params_planes = ['anio' => $anio];
    $sql_planes = "
        SELECT
            p.idContratoCliente,
            p.horasplan,
            CONCAT(c.nombrecomercial, ' - ', cc.descripcion) as nombre_contrato_cliente
        FROM planificacion p
        JOIN contratocliente cc ON p.idContratoCliente = cc.idcontratocli
        JOIN cliente c ON cc.idcliente = c.idcliente
        WHERE YEAR(p.fechaplan) = :anio";
    
    if ($mes) {
        $sql_planes .= " AND MONTH(p.fechaplan) = :mes";
        $params_planes['mes'] = $mes;
    }
    if ($idCliente) {
        $sql_planes .= " AND c.idcliente = :id_cliente";
        $params_planes['id_cliente'] = $idCliente;
    }

    $stmt_planes = $pdo->prepare($sql_planes);
    $stmt_planes->execute($params_planes);
    $planes = $stmt_planes->fetchAll(PDO::FETCH_ASSOC);

    // 2. Obtener todas las liquidaciones para el período y cliente
    $params_liquidaciones = ['anio' => $anio];
    $sql_liquidaciones = "
        SELECT 
            l.idcontratocli, 
            l.estado, 
            l.cantidahoras,
            CONCAT(c.nombrecomercial, ' - ', cc.descripcion) as nombre_contrato_cliente
        FROM liquidacion l
        JOIN contratocliente cc ON l.idcontratocli = cc.idcontratocli
        JOIN cliente c ON cc.idcliente = c.idcliente
        WHERE l.activo = 1 AND YEAR(l.fecha) = :anio";

    if ($mes) {
        $sql_liquidaciones .= " AND MONTH(l.fecha) = :mes";
        $params_liquidaciones['mes'] = $mes;
    }
    if ($idCliente) {
        // La subconsulta no es necesaria si ya estamos uniendo las tablas
        $sql_liquidaciones .= " AND c.idcliente = :id_cliente";
        $params_liquidaciones['id_cliente'] = $idCliente;
    }
    
    $stmt_liquidaciones = $pdo->prepare($sql_liquidaciones);
    $stmt_liquidaciones->execute($params_liquidaciones);
    $liquidaciones = $stmt_liquidaciones->fetchAll(PDO::FETCH_ASSOC);

    // 3. Obtener datos de colaboradores para liquidaciones completas
    $params_colaboradores = ['anio' => $anio];
     $sql_colaboradores = "
        SELECT e.nombrecorto as colaborador, SUM(d.calculo) as horas_asignadas
        FROM liquidacion l
        JOIN distribucionhora d ON l.idliquidacion = d.idliquidacion
        JOIN empleado e ON d.participante = e.idempleado
        WHERE l.activo = 1 AND l.estado = 'Completo' AND YEAR(l.fecha) = :anio";
    
    if ($mes) {
       $sql_colaboradores .= " AND MONTH(l.fecha) = :mes";
       $params_colaboradores['mes'] = $mes;
    }
    if ($idCliente) {
       $sql_colaboradores .= " AND l.idcontratocli IN (SELECT idcontratocli FROM contratocliente WHERE idcliente = :id_cliente)";
       $params_colaboradores['id_cliente'] = $idCliente;
    }
    $sql_colaboradores .= " GROUP BY e.nombrecorto ORDER BY horas_asignadas DESC";

    $stmt_colaboradores = $pdo->prepare($sql_colaboradores);
    $stmt_colaboradores->execute($params_colaboradores);
    $colaboradores_data = $stmt_colaboradores->fetchAll(PDO::FETCH_ASSOC);

    // Si no hay ni planes ni liquidaciones, no hay nada que mostrar.
    if (empty($planes) && empty($liquidaciones)) {
        echo json_encode($response);
        exit;
    }

    // 4. Procesar y combinar los datos en PHP
    $contratos = [];
    $summary = ['total_horas_planificadas' => 0, 'total_horas_liquidadas' => 0, 'total_horas_completadas' => 0];
    
    // Inicializar y acumular desde planes
    foreach ($planes as $plan) {
        $idContrato = $plan['idContratoCliente'];
        if (!isset($contratos[$idContrato])) {
            $contratos[$idContrato] = [
                'contrato_cliente' => $plan['nombre_contrato_cliente'],
                'horas_planificadas' => 0,
                'estados' => [],
            ];
        }
        $contratos[$idContrato]['horas_planificadas'] += floatval($plan['horasplan']);
        $summary['total_horas_planificadas'] += floatval($plan['horasplan']);
    }

    // Procesar liquidaciones, añadiendo nuevos contratos si es necesario
    foreach ($liquidaciones as $liq) {
        $idContrato = $liq['idcontratocli'];
        
        if (!isset($contratos[$idContrato])) {
            $contratos[$idContrato] = [
                'contrato_cliente' => $liq['nombre_contrato_cliente'],
                'horas_planificadas' => 0,
                'estados' => [],
            ];
        }
        
        $estado = $liq['estado'];
        $horas = floatval($liq['cantidahoras']);
        
        if (!isset($contratos[$idContrato]['estados'][$estado])) {
            $contratos[$idContrato]['estados'][$estado] = 0;
        }
        $contratos[$idContrato]['estados'][$estado] += $horas;
        
        $summary['total_horas_liquidadas'] += $horas;
        if ($estado === 'Completo') {
            $summary['total_horas_completadas'] += $horas;
        }
    }
    
    $estados_data = [];
    foreach ($contratos as $contrato) {
        if (empty($contrato['estados'])) {
             $estados_data[] = [
               'contrato_cliente' => $contrato['contrato_cliente'],
               'estado_liquidacion' => 'Sin Liquidaciones',
               'total_horas' => 0,
               'horas_planificadas' => $contrato['horas_planificadas']
           ];
        } else {
            foreach ($contrato['estados'] as $estado => $horas) {
                $estados_data[] = [
                   'contrato_cliente' => $contrato['contrato_cliente'],
                   'estado_liquidacion' => $estado,
                   'total_horas' => $horas,
                   'horas_planificadas' => $contrato['horas_planificadas']
                ];
            }
        }
    }

    $response = [
        'success' => true,
        'message' => 'Datos obtenidos correctamente.',
        'data' => [
            'contratos' => array_values($contratos),
            'estados' => $estados_data,
            'colaboradores' => $colaboradores_data,
            'summary' => $summary
        ]
    ];

} catch (Exception $e) {
    $response['message'] = 'Error al obtener los datos: ' . $e->getMessage();
    error_log("Error en reporte_planificacion_liquidacion.php: " . $e->getMessage());
}

echo json_encode($response);
?>
