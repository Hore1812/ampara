<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once '../conexion.php';
require_once '../auth_check.php';

$response = ['success' => false, 'message' => 'No se pudo procesar la solicitud.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['idusuario'], $_SESSION['idemp'])) {
        $response['message'] = 'Acceso denegado. No ha iniciado sesión.';
        echo json_encode($response);
        exit;
    }

    $idUsuario = $_SESSION['idusuario'];
    $idEmpleado = $_SESSION['idemp'];

    // Campos permitidos de la tabla empleado
    $allowed_fields = [
        'nombres', 'paterno', 'materno', 'nombrecorto', 'dni', 'nacimiento',
        'lugarnacimiento', 'domicilio', 'estadocivil', 'correopersonal',
        'telcelular', 'telfijo', 'contactoemergencia'
    ];

    $update_fields = [];
    $params = [':idempleado' => $idEmpleado];

    foreach ($allowed_fields as $field) {
        if (isset($_POST[$field])) {
            $update_fields[] = "$field = :$field";
            $params[":$field"] = trim($_POST[$field]);
        }
    }

    // Manejo de la foto de perfil
    $newPhotoPath = null;
    if (isset($_FILES['perfilFoto']) && $_FILES['perfilFoto']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['perfilFoto'];
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_file_size = 5 * 1024 * 1024; // 5 MB

        if (!in_array($file['type'], $allowed_mime_types)) {
            $response['message'] = 'Error: Tipo de archivo no permitido.';
            echo json_encode($response);
            exit;
        }

        if ($file['size'] > $max_file_size) {
            $response['message'] = 'Error: El archivo es demasiado grande (máximo 5MB).';
            echo json_encode($response);
            exit;
        }

        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $unique_filename = 'empleado_' . $idEmpleado . '_' . time() . '.' . $file_extension;
        $upload_path = '../img/fotos/empleados/' . $unique_filename;
        $db_path = 'img/fotos/empleados/' . $unique_filename;

        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $newPhotoPath = $db_path;
            $update_fields[] = "rutafoto = :rutafoto";
            $params[':rutafoto'] = $newPhotoPath;
        } else {
            $response['message'] = 'Error al mover el archivo subido.';
            echo json_encode($response);
            exit;
        }
    }

    // Manejo del cambio de contraseña
    $password_updated = false;
    if (!empty($_POST['password_actual']) && !empty($_POST['password_nuevo'])) {
        if ($_POST['password_nuevo'] !== $_POST['password_confirmar']) {
            $response['message'] = 'Las nuevas contraseñas no coinciden.';
            echo json_encode($response);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT password FROM usuario WHERE idusuario = :idusuario");
            $stmt->execute([':idusuario' => $idUsuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($_POST['password_actual'], $user['password'])) {
                $new_password_hashed = password_hash($_POST['password_nuevo'], PASSWORD_DEFAULT);
                $stmt_pass = $pdo->prepare("UPDATE usuario SET password = :password WHERE idusuario = :idusuario");
                $stmt_pass->execute([':password' => $new_password_hashed, ':idusuario' => $idUsuario]);
                $password_updated = true;
            } else {
                $response['message'] = 'La contraseña actual es incorrecta.';
                echo json_encode($response);
                exit;
            }
        } catch (PDOException $e) {
            error_log("Error al actualizar contraseña: " . $e->getMessage());
            $response['message'] = 'Error de base de datos al actualizar la contraseña.';
            echo json_encode($response);
            exit;
        }
    }

    // Actualizar la tabla empleado si hay campos para actualizar
    $employee_updated = false;
    if (!empty($update_fields)) {
        try {
            $sql = "UPDATE empleado SET " . implode(', ', $update_fields) . " WHERE idempleado = :idempleado";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $employee_updated = true;
        } catch (PDOException $e) {
            error_log("Error al actualizar perfil: " . $e->getMessage());
            $response['message'] = 'Error de base de datos al actualizar el perfil.';
            echo json_encode($response);
            exit;
        }
    }

    if ($employee_updated || $password_updated) {
        $response['success'] = true;
        $response['message'] = 'Perfil actualizado con éxito.';

        // Devolver nuevos datos para actualizar la UI
        $response['newData'] = [];
        if(isset($params[':nombrecorto'])) {
            $response['newData']['nombre_usuario_display'] = $params[':nombrecorto'];
            $_SESSION['nombre_usuario'] = $params[':nombrecorto'];
        }
        if($newPhotoPath) {
            $response['newData']['ruta_foto_usuario'] = $newPhotoPath;
        }

    } else {
        // Esto puede pasar si no se cambió ningún dato
        $response['success'] = true; // Considerado éxito si no hay error
        $response['message'] = 'No se realizaron cambios.';
    }

} else {
    $response['message'] = 'Método de solicitud no válido.';
}

echo json_encode($response);
?>
