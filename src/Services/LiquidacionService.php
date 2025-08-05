<?php

namespace Ampara\Services;

use Ampara\Database;
use PDO;
use PDOException;
use Exception;

/**
 * Service class for handling business logic related to Liquidaciones.
 * This demonstrates how to move logic from database triggers into the application layer.
 */
class LiquidacionService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Creates a new liquidacion and all its related records within a transaction.
     * This method is a template for replacing the trigger-based logic.
     *
     * @param array $data The data for the new liquidacion. Expected keys:
     * - 'fecha', 'asunto', 'tema', 'motivo', 'tipohora', 'acargode', 'lider',
     * - 'cantidahoras', 'estado', 'idcontratocli', 'editor'
     * - 'colaboradores' (optional array for distribution)
     * @return int The ID of the newly created liquidacion.
     * @throws Exception if validation fails or DB operation fails.
     */
    public function create(array $data): int
    {
        // Basic validation
        $requiredKeys = ['fecha', 'asunto', 'tema', 'motivo', 'tipohora', 'acargode', 'lider', 'cantidahoras', 'estado', 'idcontratocli', 'editor'];
        foreach ($requiredKeys as $key) {
            if (!isset($data[$key])) {
                throw new Exception("Dato requerido faltante para crear liquidación: {$key}");
            }
        }

        try {
            $this->db->beginTransaction();

            // 1. Insert into `liquidacion` table
            $sqlLiq = "INSERT INTO liquidacion (fecha, asunto, tema, motivo, tipohora, acargode, lider, cantidahoras, estado, idcontratocli, activo, editor)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";
            $stmtLiq = $this->db->prepare($sqlLiq);
            $stmtLiq->execute([
                $data['fecha'], $data['asunto'], $data['tema'], $data['motivo'], $data['tipohora'],
                $data['acargode'], $data['lider'], $data['cantidahoras'], $data['estado'],
                $data['idcontratocli'], $data['editor']
            ]);
            $idLiquidacion = (int)$this->db->lastInsertId();

            // 2. Insert into `distribucionhora` if applicable
            if ($data['estado'] === 'Completo' && !empty($data['colaboradores'])) {
                $sqlDist = "INSERT INTO distribucionhora (participante, porcentaje, comentario, idliquidacion, fecha, horas, calculo)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtDist = $this->db->prepare($sqlDist);

                foreach ($data['colaboradores'] as $colaborador) {
                    $calculo = $data['cantidahoras'] * (floatval($colaborador['porcentaje']) / 100);
                    $stmtDist->execute([
                        $colaborador['id'], $colaborador['porcentaje'], $colaborador['comentario'],
                        $idLiquidacion, $data['fecha'], $data['cantidahoras'], $calculo
                    ]);
                }
            }

            // 3. Replicate the trigger logic: Find matching `planificacion` and create `detalles_planificacion`
            $sqlPlan = "SELECT Idplanificacion FROM planificacion
                        WHERE idContratoCliente = ? AND YEAR(fechaplan) = YEAR(?) AND MONTH(fechaplan) = MONTH(?) LIMIT 1";
            $stmtPlan = $this->db->prepare($sqlPlan);
            $stmtPlan->execute([$data['idcontratocli'], $data['fecha'], $data['fecha']]);
            $idPlanificacion = $stmtPlan->fetchColumn();

            if ($idPlanificacion) {
                // 3a. Insert into `detalles_planificacion`
                $sqlDetalle = "INSERT INTO detalles_planificacion (Idplanificacion, idliquidacion, fechaliquidacion, estado, cantidahoras)
                               VALUES (?, ?, ?, ?, ?)";
                $stmtDetalle = $this->db->prepare($sqlDetalle);
                $stmtDetalle->execute([
                    $idPlanificacion, $idLiquidacion, $data['fecha'], $data['estado'], $data['cantidahoras']
                ]);
                $idDetalle = (int)$this->db->lastInsertId();

                // 3b. If 'Completo', also create `distribucion_planificacion`
                if ($data['estado'] === 'Completo' && !empty($data['colaboradores'])) {
                    $sqlDistPlan = "INSERT INTO distribucion_planificacion (iddetalle, idparticipante, porcentaje, horas_asignadas)
                                    VALUES (?, ?, ?, ?)";
                    $stmtDistPlan = $this->db->prepare($sqlDistPlan);
                    foreach ($data['colaboradores'] as $colaborador) {
                         $calculo = $data['cantidahoras'] * (floatval($colaborador['porcentaje']) / 100);
                         $stmtDistPlan->execute([
                            $idDetalle, $colaborador['id'], $colaborador['porcentaje'], $calculo
                         ]);
                    }
                }
            }

            $this->db->commit();
            return $idLiquidacion;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en LiquidacionService::create: " . $e->getMessage());
            // Re-throw the exception to be handled by the controller
            throw new Exception("Error en la base de datos al crear la liquidación.");
        }
    }
}
