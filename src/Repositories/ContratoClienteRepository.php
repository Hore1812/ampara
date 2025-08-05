<?php

namespace Ampara\Repositories;

use Ampara\Database;
use PDO;
use PDOException;
use Exception;

class ContratoClienteRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Replaces obtenerTodosContratosClientes()
     */
    public function getAll(array $filtros = []): array
    {
        $sql = "SELECT cc.*, c.nombrecomercial AS nombre_cliente, e.nombrecorto AS nombre_lider
                FROM contratocliente cc
                JOIN cliente c ON cc.idcliente = c.idcliente
                JOIN empleado e ON cc.lider = e.idempleado
                WHERE 1=1";
        $params = [];

        if (isset($filtros['id_lider_filtro']) && $filtros['id_lider_filtro'] !== '') {
            $sql .= " AND cc.lider = :lider";
            $params[':lider'] = $filtros['id_lider_filtro'];
        }
        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $sql .= " AND cc.activo = :activo";
            $params[':activo'] = $filtros['activo'];
        }

        $sql .= " ORDER BY cc.fechainicio DESC, c.nombrecomercial ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in ContratoClienteRepository::getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Replaces obtenerContratoClientePorId()
     */
    public function find(int $id)
    {
        $sql = "SELECT cc.*, c.nombrecomercial AS nombre_cliente, e.nombrecorto AS nombre_lider
                FROM contratocliente cc
                JOIN cliente c ON cc.idcliente = c.idcliente
                JOIN empleado e ON cc.lider = e.idempleado
                WHERE cc.idcontratocli = :idcontratocli";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':idcontratocli' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error in ContratoClienteRepository::find: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Replaces registrarContratoCliente()
     * @throws Exception
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO contratocliente (
                    idcliente, lider, descripcion, fechainicio, fechafin, horasfijasmes, costohorafija,
                    mesescontrato, totalhorasfijas, tipobolsa, costohoraextra, montofijomes,
                    planmontomes, planhoraextrames, status, tipohora, activo, editor, registrado, modificado
                ) VALUES (
                    :idcliente, :lider, :descripcion, :fechainicio, :fechafin, :horasfijasmes, :costohorafija,
                    :mesescontrato, :totalhorasfijas, :tipobolsa, :costohoraextra, :montofijomes,
                    :planmontomes, :planhoraextrames, :status, :tipohora, :activo, :editor, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                )";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in ContratoClienteRepository::create: " . $e->getMessage());
            throw new Exception('Error de base de datos al crear el contrato.');
        }
    }

    /**
     * Replaces actualizarContratoCliente()
     * @throws Exception
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE contratocliente SET
                    idcliente = :idcliente, lider = :lider, descripcion = :descripcion, fechainicio = :fechainicio,
                    fechafin = :fechafin, horasfijasmes = :horasfijasmes, costohorafija = :costohorafija,
                    mesescontrato = :mesescontrato, totalhorasfijas = :totalhorasfijas, tipobolsa = :tipobolsa,
                    costohoraextra = :costohoraextra, montofijomes = :montofijomes, planmontomes = :planmontomes,
                    planhoraextrames = :planhoraextrames, status = :status, tipohora = :tipohora, activo = :activo,
                    editor = :editor, modificado = CURRENT_TIMESTAMP
                WHERE idcontratocli = :idcontratocli";
        try {
            $data['idcontratocli'] = $id;
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Error in ContratoClienteRepository::update: " . $e->getMessage());
            throw new Exception('Error de base de datos al actualizar el contrato.');
        }
    }

    /**
     * Replaces actualizarEstadoContratoCliente()
     */
    public function updateStatus(int $id, int $status, int $editorId): bool
    {
        $sql = "UPDATE contratocliente SET activo = :activo, editor = :editor, modificado = CURRENT_TIMESTAMP
                WHERE idcontratocli = :idcontratocli";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':activo' => $status, ':editor' => $editorId, ':idcontratocli' => $id]);
        } catch (PDOException $e) {
            error_log("Error in ContratoClienteRepository::updateStatus: " . $e->getMessage());
            return false;
        }
    }
}
