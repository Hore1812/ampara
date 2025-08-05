<?php

namespace Ampara\Repositories;

use Ampara\Database;
use PDO;
use PDOException;
use Exception;

class ClienteRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Replaces obtenerTodosClientes_crud()
     */
    public function getAll(array $filtros = []): array
    {
        $sql = "SELECT * FROM cliente WHERE 1=1";
        $params = [];

        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $sql .= " AND activo = :activo";
            $params[':activo'] = $filtros['activo'];
        }
        $sql .= " ORDER BY nombrecomercial ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in ClienteRepository::getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Replaces obtenerClientePorId()
     */
    public function find(int $id)
    {
        $sql = "SELECT * FROM cliente WHERE idcliente = :idcliente";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':idcliente' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error in ClienteRepository::find: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Replaces registrarCliente()
     * @throws Exception
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO cliente (razonsocial, nombrecomercial, ruc, direccion, telefono, sitioweb, representante, telrepresentante, correorepre, gerente, telgerente, correogerente, activo, editor, registrado, modificado)
                VALUES (:razonsocial, :nombrecomercial, :ruc, :direccion, :telefono, :sitioweb, :representante, :telrepresentante, :correorepre, :gerente, :telgerente, :correogerente, :activo, :editor, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                throw new Exception('El RUC ya existe para otro cliente.');
            }
            error_log("Error in ClienteRepository::create: " . $e->getMessage());
            throw new Exception('Error de base de datos al crear el cliente.');
        }
    }

    /**
     * Replaces actualizarCliente()
     * @throws Exception
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE cliente SET
                    razonsocial = :razonsocial, nombrecomercial = :nombrecomercial, ruc = :ruc,
                    direccion = :direccion, telefono = :telefono, sitioweb = :sitioweb,
                    representante = :representante, telrepresentante = :telrepresentante, correorepre = :correorepre,
                    gerente = :gerente, telgerente = :telgerente, correogerente = :correogerente,
                    activo = :activo, editor = :editor, modificado = CURRENT_TIMESTAMP
                WHERE idcliente = :idcliente";
        try {
            $data['idcliente'] = $id;
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                throw new Exception('El RUC ya existe para otro cliente.');
            }
            error_log("Error in ClienteRepository::update: " . $e->getMessage());
            throw new Exception('Error de base de datos al actualizar el cliente.');
        }
    }

    /**
     * Replaces actualizarEstadoCliente()
     */
    public function updateStatus(int $id, int $status, int $editorId): bool
    {
        $sql = "UPDATE cliente SET activo = :activo, editor = :editor, modificado = CURRENT_TIMESTAMP WHERE idcliente = :idcliente";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':activo' => $status, ':editor' => $editorId, ':idcliente' => $id]);
        } catch (PDOException $e) {
            error_log("Error in ClienteRepository::updateStatus: " . $e->getMessage());
            return false;
        }
    }
}
