<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

// Simulación de una función de seguridad/autenticación
if (!isset($_SESSION['idusuario'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

try {
    global $pdo;

    // Recoger y validar filtros
    $anio = filter_input(INPUT_POST, 'anio', FILTER_VALIDATE_INT);
    $mes = filter_input(INPUT_POST, 'mes', FILTER_VALIDATE_INT);
    $idcolaborador = filter_input(INPUT_POST, 'idcolaborador', FILTER_VALIDATE_INT);

    // Reemplazar la vista con una consulta directa y corregida
    $sql = "
        SELECT
            e.idempleado,
            e.nombrecorto AS NombreColaborador,
            e.horasmeta AS HorasMeta,
            YEAR(l.fecha) AS Anio,
            MONTH(l.fecha) AS Mes,
            SUM(dh.calculo) AS HorasCompletadas,
            (SUM(dh.calculo) / e.horasmeta) * 100 AS PorcentajeCumplimiento
        FROM distribucionhora dh
        JOIN liquidacion l ON dh.idliquidacion = l.idliquidacion
        JOIN empleado e ON dh.participante = e.idempleado
        WHERE l.estado = 'Completo' AND l.activo = 1
    ";
    $params = [];

    if ($anio) {
        $sql .= " AND YEAR(l.fecha) = :anio";
        $params[':anio'] = $anio;
    }
    if ($mes) {
        $sql .= " AND MONTH(l.fecha) = :mes";
        $params[':mes'] = $mes;
    }
    if ($idcolaborador) {
        $sql .= " AND e.idempleado = :idcolaborador";
        $params[':idcolaborador'] = $idcolaborador;
    }

    $sql .= " GROUP BY e.idempleado, e.nombrecorto, e.horasmeta, Anio, Mes";
    $sql .= " ORDER BY Anio DESC, Mes DESC, PorcentajeCumplimiento DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pivotar los datos para la tabla de doble entrada
    $pivotData = [];
    $meses = [];
    foreach ($rawData as $row) {
        $id = $row['idempleado'];
        if (!isset($pivotData[$id])) {
            $pivotData[$id] = [
                'NombreColaborador' => $row['NombreColaborador'],
                'HorasMeta' => $row['HorasMeta'],
                'datos_mes' => []
            ];
        }
        $pivotData[$id]['datos_mes'][$row['Mes']] = [
            'HorasCompletadas' => $row['HorasCompletadas'],
            'PorcentajeCumplimiento' => $row['PorcentajeCumplimiento']
        ];
        if (!in_array($row['Mes'], $meses)) {
            $meses[] = $row['Mes'];
        }
    }

    sort($meses);

    echo json_encode([
        'success' => true,
        'data' => array_values($pivotData),
        'meses' => $meses
    ]);

} catch (PDOException $e) {
    // Log del error para depuración interna
    error_log("Error en obtener_progreso_colaboradores.php: " . $e->getMessage());
    // Enviar una respuesta de error genérica al cliente
    echo json_encode(['success' => false, 'message' => 'Error al consultar la base de datos.']);
}
?>
