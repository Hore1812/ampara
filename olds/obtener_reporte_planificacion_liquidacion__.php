<?php
require_once '../conexion.php';
require_once '../funciones.php';

header('Content-Type: application/json');

$anio = isset($_POST['anio']) ? intval($_POST['anio']) : date('Y');
$mes = isset($_POST['mes']) && !empty($_POST['mes']) ? intval($_POST['mes']) : null;
$idCliente = isset($_POST['idcliente']) && !empty($_POST['idcliente']) ? intval($_POST['idcliente']) : null;

$response = [
    'success' => false,
    'message' => 'No se encontraron datos.',
    'data' => null
];

try {
    // Parámetros y condiciones base
    $where = " WHERE YEAR(p.fechaplan) = :anio";
    $params = ['anio' => $anio];

    if ($mes) {
        $where .= " AND MONTH(p.fechaplan) = :mes";
        $params['mes'] = $mes;
    }

    if ($idCliente) {
        $where .= " AND c.idcliente = :id_cliente";
        $params['id_cliente'] = $idCliente;
    }

    // Consulta principal para obtener todos los datos necesarios
    $sql = "
        SELECT
            c.nombrecomercial as contrato_cliente,
            p.horasplan,
            dp.estado as estado_liquidacion,
            dp.cantidahoras as horas_liquidadas
        FROM planificacion p
        JOIN contratocliente cc ON p.idContratoCliente = cc.idcontratocli
        JOIN cliente c ON cc.idcliente = c.idcliente
        LEFT JOIN detalles_planificacion dp ON p.Idplanificacion = dp.Idplanificacion
        " . $where . "
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($all_data) {
        $contratos_data = [];
        $summary_data = [
            'total_horas_planificadas' => 0,
            'total_horas_liquidadas' => 0,
            'total_horas_completadas' => 0
        ];

        $planificadas_por_contrato = [];

        foreach ($all_data as $row) {
            // Summary data
            if (!isset($planificadas_por_contrato[$row['contrato_cliente']])) {
                $planificadas_por_contrato[$row['contrato_cliente']] = 0;
            }
            $planificadas_por_contrato[$row['contrato_cliente']] = $row['horasplan'];

            $summary_data['total_horas_liquidadas'] += $row['horas_liquidadas'];
            if ($row['estado_liquidacion'] === 'Completo') {
                $summary_data['total_horas_completadas'] += $row['horas_liquidadas'];
            }

            // Contratos data
            if (!isset($contratos_data[$row['contrato_cliente']])) {
                $contratos_data[$row['contrato_cliente']] = [
                    'contrato_cliente' => $row['contrato_cliente'],
                    'horas_planificadas' => $row['horasplan'],
                    'estados' => []
                ];
            }
            if (!isset($contratos_data[$row['contrato_cliente']]['estados'][$row['estado_liquidacion']])) {
                $contratos_data[$row['contrato_cliente']]['estados'][$row['estado_liquidacion']] = 0;
            }
            $contratos_data[$row['contrato_cliente']]['estados'][$row['estado_liquidacion']] += $row['horas_liquidadas'];
        }

        $summary_data['total_horas_planificadas'] = array_sum($planificadas_por_contrato);

        // Colaboradores
        $sql_colaboradores = "
            SELECT
                e.nombrecorto as colaborador,
                SUM(dpl.horas_asignadas) as horas_asignadas,
                SUM(dpl.porcentaje) as porcentaje
            FROM planificacion p
            JOIN contratocliente cc ON p.idContratoCliente = cc.idcontratocli
            JOIN cliente c ON cc.idcliente = c.idcliente
            LEFT JOIN detalles_planificacion dp ON p.Idplanificacion = dp.Idplanificacion
            LEFT JOIN distribucion_planificacion dpl ON dp.iddetalle = dpl.iddetalle
            LEFT JOIN empleado e ON dpl.idparticipante = e.idempleado
            " . $where . " AND dp.estado = 'Completo'
            GROUP BY e.nombrecorto
        ";
        $stmt_colaboradores = $pdo->prepare($sql_colaboradores);
        $stmt_colaboradores->execute($params);
        $colaboradores_data = $stmt_colaboradores->fetchAll(PDO::FETCH_ASSOC);


        $response['success'] = true;
        $response['message'] = 'Datos obtenidos correctamente.';
        $response['data'] = [
            'contratos' => array_values($contratos_data),
            'estados' => $contratos_data, // This will be processed in JS
            'colaboradores' => array_values($colaboradores_data),
            'summary' => $summary_data
        ];
    }

} catch (Exception $e) {
    $response['message'] = 'Error al obtener los datos: ' . $e->getMessage();
}

echo json_encode($response);
?>