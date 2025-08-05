<?php

namespace Ampara\Repositories;

use Ampara\Database;
use PDO;
use PDOException;

class UsuarioRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtiene todos los usuarios con filtros.
     * Replaces obtenerTodosUsuarios().
     */
    public function getAll(array $filtros = []): array
    {
        $sql = "SELECT u.idusuario, u.nombre, u.tipo, u.activo, u.idemp, e.nombrecorto AS nombre_empleado, e.rutafoto AS rutafoto_empleado
                FROM usuario u
                LEFT JOIN empleado e ON u.idemp = e.idempleado
                WHERE 1=1";

        $params = [];
        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $sql .= " AND u.activo = :activo";
            $params[':activo'] = $filtros['activo'];
        }
        $sql .= " ORDER BY u.nombre ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in UsuarioRepository::getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza la contraseña de un usuario.
     * Replaces actualizarPasswordUsuario().
     */
    public function updatePassword(int $id, string $hashedPassword, int $editorId): bool
    {
        $sql = "UPDATE usuario SET password = :password, editor = :editor, modificado = CURRENT_TIMESTAMP WHERE idusuario = :idusuario";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':password' => $hashedPassword,
                ':editor' => $editorId,
                ':idusuario' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error in UsuarioRepository::updatePassword: " . $e->getMessage());
            return false;
        }
    }
}
