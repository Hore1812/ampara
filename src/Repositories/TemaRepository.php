<?php

namespace Ampara\Repositories;

use Ampara\Database;
use PDO;
use PDOException;

class TemaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtiene todos los temas con filtros opcionales.
     * Refactor of obtenerTodosTemas_crud().
     *
     * @param array $filtros
     * @return array
     */
    public function getAll(array $filtros = []): array
    {
        $sql = "SELECT t.idtema, t.descripcion, t.idencargado, t.comentario, t.editor, t.registrado, t.modificado, e.nombrecorto AS nombre_encargado, t.activo
                FROM tema t
                LEFT JOIN empleado e ON t.idencargado = e.idempleado
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['descripcion'])) {
            $sql .= " AND t.descripcion LIKE :descripcion";
            $params[':descripcion'] = "%" . $filtros['descripcion'] . "%";
        }
        if (isset($filtros['idencargado']) && $filtros['idencargado'] !== '') {
            $sql .= " AND t.idencargado = :idencargado";
            $params[':idencargado'] = $filtros['idencargado'];
        }
        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $sql .= " AND t.activo = :activo";
            $params[':activo'] = $filtros['activo'];
        }

        $sql .= " ORDER BY t.descripcion ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // In a real app, log the error
            error_log("Error in TemaRepository::getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un tema por su ID.
     * Refactor of obtenerTemaPorId().
     *
     * @param int $id
     * @return array|false
     */
    public function find(int $id)
    {
        $sql = "SELECT t.idtema, t.descripcion, t.idencargado, t.comentario, t.activo, e.nombrecorto AS nombre_encargado
                FROM tema t
                LEFT JOIN empleado e ON t.idencargado = e.idempleado
                WHERE t.idtema = :idtema";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':idtema' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error in TemaRepository::find: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo tema.
     * Refactor of registrarTema().
     *
     * @param array $data
     * @return int|false
     */
    public function create(array $data): ?int
    {
        $sql = "INSERT INTO tema (descripcion, idencargado, comentario, activo, editor, registrado, modificado)
                VALUES (:descripcion, :idencargado, :comentario, :activo, :editor, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':descripcion' => $data['descripcion'],
                ':idencargado' => $data['idencargado'] ?: null,
                ':comentario' => $data['comentario'],
                ':activo' => $data['activo'],
                ':editor' => $data['editor']
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in TemaRepository::create: " . $e->getMessage());
            // You could throw a custom exception here to be handled by the controller
            return null;
        }
    }

    /**
     * Actualiza un tema existente.
     * Refactor of actualizarTema().
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE tema SET
                    descripcion = :descripcion,
                    idencargado = :idencargado,
                    comentario = :comentario,
                    activo = :activo,
                    editor = :editor,
                    modificado = CURRENT_TIMESTAMP
                WHERE idtema = :idtema";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':descripcion' => $data['descripcion'],
                ':idencargado' => $data['idencargado'] ?: null,
                ':comentario' => $data['comentario'],
                ':activo' => $data['activo'],
                ':editor' => $data['editor'],
                ':idtema' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error in TemaRepository::update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el estado de un tema (lo activa o desactiva).
     * Refactor of actualizarEstadoTema().
     *
     * @param integer $id
     * @param integer $estado
     * @param integer $editorId
     * @return boolean
     */
    public function updateStatus(int $id, int $estado, int $editorId): bool
    {
        $sql = "UPDATE tema SET activo = :activo, editor = :editor, modificado = CURRENT_TIMESTAMP WHERE idtema = :idtema";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':activo' => $estado, ':editor' => $editorId, ':idtema' => $id]);
        } catch (PDOException $e) {
            error_log("Error in TemaRepository::updateStatus: " . $e->getMessage());
            return false;
        }
    }
}
