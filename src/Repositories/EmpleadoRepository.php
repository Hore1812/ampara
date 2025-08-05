<?php

namespace Ampara\Repositories;

use Ampara\Database;
use PDO;
use PDOException;

class EmpleadoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtiene empleados activos para poblar selects.
     * Replaces obtenerEmpleadosActivosParaSelect()
     */
    public function findActiveForSelect(): array
    {
        try {
            $sql = "SELECT idempleado, nombrecorto, rutafoto FROM empleado WHERE activo = 1 ORDER BY nombrecorto ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in EmpleadoRepository::findActiveForSelect: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todos los colaboradores (empleados).
     * Replaces obtenerColaboradores()
     */
    public function findAllActive(): array
    {
        try {
            $sql = "SELECT idempleado AS 'ID', nombrecorto AS 'COLABORADOR', rutafoto FROM empleado WHERE activo=1 ORDER BY 2";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in EmpleadoRepository::findAllActive: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Finds active employees and includes a count of themes assigned to them.
     *
     * @return array
     */
    public function findActiveWithTemaCount(): array
    {
        $sql = "SELECT e.idempleado, e.nombrecorto, COUNT(t.idtema) as tema_count
                FROM empleado e
                LEFT JOIN tema t ON e.idempleado = t.idencargado AND t.activo = 1
                WHERE e.activo = 1
                GROUP BY e.idempleado, e.nombrecorto
                ORDER BY e.nombrecorto ASC";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error in EmpleadoRepository::findActiveWithTemaCount: " . $e->getMessage());
            return [];
        }
    }
}
