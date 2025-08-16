<?php
session_start();
require_once '../conexion.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'No se pudo obtener la información del perfil.'];

if (!isset($_SESSION['idemp'])) {
    $response['message'] = 'Usuario no autenticado.';
    echo json_encode($response);
    exit;
}

try {
    $id_empleado = $_SESSION['idemp'];

    $sql = "SELECT 
                e.nombres,
                e.paterno,
                e.materno,
                e.nombrecorto,
                e.dni,
                e.nacimiento,
                e.lugarnacimiento,
                e.domicilio,
                e.estadocivil,
                e.correopersonal,
                e.correocorporativo,
                e.telcelular,
                e.telfijo,
                e.contactoemergencia,
                e.cargo,
                e.area,
                e.rutafoto,
                u.nombre as username
            FROM empleado e
            LEFT JOIN usuario u ON e.idempleado = u.idemp
            WHERE e.idempleado = :id_empleado";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_empleado' => $id_empleado]);
    $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($perfil) {
        $response['success'] = true;
        $response['message'] = 'Perfil obtenido con éxito.';
        $response['data'] = $perfil;
    } else {
        $response['message'] = 'No se encontró el perfil del empleado.';
    }

} catch (Exception $e) {
    error_log("Error en obtener_perfil_usuario.php: " . $e->getMessage());
    $response['message'] = 'Error del servidor al obtener el perfil.';
}

echo json_encode($response);
?>
