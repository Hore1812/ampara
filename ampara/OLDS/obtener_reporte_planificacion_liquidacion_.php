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

    // Consulta para la tabla de doble entrada y el gráfico de barras
    $sql_contratos_estados = "
        SELECT
            c.nombrecomercial as contrato_cliente,
            COALESCE(dp.estado, 'No Iniciado') as estado_liquidacion,
            SUM(dp.cantidahoras) as total_horas
        FROM planificacion p
        JOIN contratocliente cc ON p.idContratoCliente = cc.idcontratocli
        JOIN cliente c ON cc.idcliente = c.idcliente
        LEFT JOIN detalles_planificacion dp ON p.Idplanificacion = dp.Idplanificacion
        " . $where . "
        GROUP BY c.nombrecomercial, estado_liquidacion
    ";
    $stmt_contratos_estados = $pdo->prepare($sql_contratos_estados);
    $stmt_contratos_estados->execute($params);
    $contratos_data = $stmt_contratos_estados->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para obtener las horas planificadas por contrato
    $sql_horas_planificadas = "
        SELECT
            c.nombrecomercial as contrato_cliente,
            SUM(p.horasplan) as horas_planificadas
        FROM planificacion p
        JOIN contratocliente cc ON p.idContratoCliente = cc.idcontratocli
        JOIN cliente c ON cc.idcliente = c.idcliente
        " . $where . "
        GROUP BY c.nombrecomercial
    ";
    $stmt_horas_planificadas = $pdo->prepare($sql_horas_planificadas);
    $stmt_horas_planificadas->execute($params);
    $horas_planificadas_data = $stmt_horas_planificadas->fetchAll(PDO::FETCH_ASSOC);

    // Combinar los datos
    $contratos_combinados = [];
    foreach ($contratos_data as $contrato) {
        if (!isset($contratos_combinados[$contrato['contrato_cliente']])) {
            $contratos_combinados[$contrato['contrato_cliente']] = [
                'contrato_cliente' => $contrato['contrato_cliente'],
                'horas_planificadas' => 0,
                'estados' => []
            ];
        }
        $contratos_combinados[$contrato['contrato_cliente']]['estados'][$contrato['estado_liquidacion']] = $contrato['total_horas'];
    }

    foreach ($horas_planificadas_data as $hp) {
        if (isset($contratos_combinados[$hp['contrato_cliente']])) {
            $contratos_combinados[$hp['contrato_cliente']]['horas_planificadas'] = $hp['horas_planificadas'];
        }
    }


    // Consulta para los datos de colaboradores
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

    // Consulta para las tarjetas de resumen
    $sql_summary = "
        SELECT
            (SELECT SUM(horasplan) FROM planificacion p JOIN contratocliente cc ON p.idContratoCliente = cc.idcontratocli JOIN cliente c ON cc.idcliente = c.idcliente " . $where . ") as total_horas_planificadas,
            SUM(dp.cantidahoras) as total_horas_liquidadas,
            SUM(CASE WHEN dp.estado = 'Completo' THEN dp.cantidahoras ELSE 0 END) as total_horas_completadas
        FROM planificacion p
        JOIN contratocliente cc ON p.idContratoCliente = cc.idcontratocli
        JOIN cliente c ON cc.idcliente = c.idcliente
        LEFT JOIN detalles_planificacion dp ON p.Idplanificacion = dp.Idplanificacion
        " . $where . "
    ";
    $stmt_summary = $pdo->prepare($sql_summary);
    $stmt_summary->execute($params);
    $summary_data = $stmt_summary->fetch(PDO::FETCH_ASSOC);


    if ($contratos_combinados || $colaboradores_data) {
        $response['success'] = true;
        $response['message'] = 'Datos obtenidos correctamente.';
        $response['data'] = [
            'contratos' => array_values($contratos_combinados),
            'estados' => $contratos_data,
            'colaboradores' => $colaboradores_data,
            'summary' => $summary_data
        ];
    }

} catch (Exception $e) {
    $response['message'] = 'Error al obtener los datos: ' . $e->getMessage();
}

echo json_encode($response);
?>
