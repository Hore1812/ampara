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

    $sql = "SELECT * FROM vista_progreso_colaborador_vs_meta WHERE 1=1";
    $params = [];

    if ($anio) {
        $sql .= " AND Anio = :anio";
        $params[':anio'] = $anio;
    }
    if ($mes) {
        $sql .= " AND Mes = :mes";
        $params[':mes'] = $mes;
    }
    if ($idcolaborador) {
        $sql .= " AND idempleado = :idcolaborador";
        $params[':idcolaborador'] = $idcolaborador;
    }

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
