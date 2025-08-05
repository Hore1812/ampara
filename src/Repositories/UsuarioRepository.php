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

    /**
     * Obtiene un usuario por su ID.
     * Replaces obtenerUsuarioPorId().
     */
    public function find(int $id)
    {
        $sql = "SELECT u.idusuario, u.nombre, u.tipo, u.activo, u.idemp, e.nombrecorto AS nombre_empleado
                FROM usuario u
                LEFT JOIN empleado e ON u.idemp = e.idempleado
                WHERE u.idusuario = :idusuario";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':idusuario' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error in UsuarioRepository::find: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo usuario.
     * Replaces registrarUsuario().
     *
     * @throws Exception
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO usuario (nombre, password, tipo, activo, idemp, editor, registrado, modificado)
                VALUES (:nombre, :password, :tipo, :activo, :idemp, :editor, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':password' => $data['password'],
                ':tipo' => $data['tipo'],
                ':activo' => $data['activo'],
                ':idemp' => $data['idemp'],
                ':editor' => $data['editor']
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                throw new Exception('El nombre de usuario ya existe.');
            }
            throw new Exception('Error de base de datos al crear el usuario.');
        }
    }

    /**
     * Actualiza un usuario existente.
     * Replaces actualizarUsuario().
     *
     * @throws Exception
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE usuario SET
                    nombre = :nombre,
                    tipo = :tipo,
                    activo = :activo,
                    idemp = :idemp,
                    editor = :editor,
                    modificado = CURRENT_TIMESTAMP
                WHERE idusuario = :idusuario";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nombre' => $data['nombre'],
                ':tipo' => $data['tipo'],
                ':activo' => $data['activo'],
                ':idemp' => $data['idemp'],
                ':editor' => $data['editor'],
                ':idusuario' => $id
            ]);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                throw new Exception('El nombre de usuario ya existe para otro usuario.');
            }
            throw new Exception('Error de base de datos al actualizar el usuario.');
        }
    }

    /**
     * Actualiza el estado (activo/inactivo) de un usuario.
     * Replaces actualizarEstadoUsuario().
     */
    public function updateStatus(int $id, int $status, int $editorId): bool
    {
        $sql = "UPDATE usuario SET activo = :activo, editor = :editor, modificado = CURRENT_TIMESTAMP WHERE idusuario = :idusuario";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':activo' => $status, ':editor' => $editorId, ':idusuario' => $id]);
        } catch (PDOException $e) {
            error_log("Error in UsuarioRepository::updateStatus: " . $e->getMessage());
            return false;
        }
    }
}
