<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';
require_once '../funciones.php';

$response = [
    'success' => false,
    'message' => 'Petición no válida.',
    'data' => null
];

if (!isset($_SESSION['idusuario'])) {
    $response['message'] = 'Acceso denegado. Sesión no iniciada.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anio = filter_input(INPUT_POST, 'anio', FILTER_VALIDATE_INT);
    $mes = filter_input(INPUT_POST, 'mes', FILTER_VALIDATE_INT);
    $idcliente = filter_input(INPUT_POST, 'idcliente', FILTER_VALIDATE_INT);
    $idparticipante = filter_input(INPUT_POST, 'idparticipante', FILTER_VALIDATE_INT);

    try {
        global $pdo;
        
        $sql = "SELECT * FROM vista_planificacion_vs_participantes_completado WHERE 1=1";
        $params = [];

        if (!empty($anio)) {
            if (!empty($mes)) {
                $mes_str = str_pad($mes, 2, '0', STR_PAD_LEFT);
                $sql .= " AND MesPlan = :mes_plan";
                $params[':mes_plan'] = "$anio-$mes_str";
            } else {
                $sql .= " AND MesPlan LIKE :anio_like";
                $params[':anio_like'] = "$anio-%";
            }
        }
        
        if (!empty($idcliente)) {
            $sql .= " AND idContratoCliente IN (SELECT idcontratocli FROM contratocliente WHERE idcliente = :idcliente)";
            $params[':idcliente'] = $idcliente;
        }

        if (!empty($idparticipante)) {
            $sql .= " AND IdParticipante = :idparticipante";
            $params[':idparticipante'] = $idparticipante;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            $response['success'] = true;
            $response['message'] = 'No se encontraron datos para los filtros seleccionados.';
            $response['data'] = [];
            echo json_encode($response);
            exit;
        }

        $planificaciones = [];
        foreach ($resultados as $fila) {
            $idplan = $fila['Idplanificacion'];
            if (!isset($planificaciones[$idplan])) {
                $planificaciones[$idplan] = [
                    'id' => $idplan,
                    'nombre' => $fila['NombrePlan'],
                    'cliente' => $fila['NombreCliente'],
                    'horas_planificadas' => floatval($fila['HorasPlanificadasGlobal']),
                    'total_horas_completadas' => floatval($fila['TotalHorasLiquidadasCompletadas']),
                    'participantes' => []
                ];
            }
            if ($fila['IdParticipante']) {
                 $participante_existente = false;
                 foreach($planificaciones[$idplan]['participantes'] as $p) {
                     if ($p['id'] == $fila['IdParticipante']) {
                         $participante_existente = true;
                         break;
                     }
                 }
                 if (!$participante_existente) {
                     $planificaciones[$idplan]['participantes'][] = [
                        'id' => $fila['IdParticipante'],
                        'nombre' => $fila['NombreParticipante'],
                        'horas_completadas' => floatval($fila['HorasCompletadasPorParticipante']),
                        'porcentaje_contribucion' => floatval($fila['PorcentajeDelParticipanteEnCompletadas'])
                    ];
                 }
            }
        }
        $response['data'] = array_values($planificaciones);
        $response['success'] = true;
        $response['message'] = 'Datos obtenidos correctamente.';

    } catch (PDOException $e) {
        error_log("Error de BD en obtener_reporte_participacion.php: " . $e->getMessage());
        $response['message'] = 'Error de base de datos al generar el reporte.';
    } catch (Exception $e) {
        error_log("Error general en obtener_reporte_participacion.php: " . $e->getMessage());
        $response['message'] = 'Error inesperado al procesar la solicitud.';
    }
}

echo json_encode($response);
?>