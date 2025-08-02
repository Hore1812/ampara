CREATE TABLE `detalles_planificacion` (
  `iddetalle` int(11) NOT NULL,
  `Idplanificacion` int(11) NOT NULL,
  `idliquidacion` int(11) NOT NULL,
  `fechaliquidacion` date NOT NULL,
  `estado` varchar(50) NOT NULL,
  `cantidahoras` int(11) NOT NULL,
  `registrado` timestamp NOT NULL DEFAULT current_timestamp(),
  `modificado` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `distribucion_planificacion` (
  `iddistribucionplan` int(11) NOT NULL,
  `iddetalle` int(11) NOT NULL,
  `idparticipante` int(11) NOT NULL,
  `porcentaje` int(11) NOT NULL,
  `horas_asignadas` decimal(10,2) DEFAULT NULL,
  `registrado` timestamp NOT NULL DEFAULT current_timestamp(),
  `modificado` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `planificacion` (
  `Idplanificacion` int(11) NOT NULL,
  `idContratoCliente` int(11) NOT NULL,
  `nombreplan` varchar(255) NOT NULL,
  `fechaplan` date NOT NULL,
  `horasplan` int(11) NOT NULL,
  `lider` int(11) NOT NULL,
  `comentario` text DEFAULT NULL,
  `activo` int(11) NOT NULL DEFAULT 1,
  `editor` int(11) NOT NULL,
  `registrado` timestamp NOT NULL DEFAULT current_timestamp(),
  `modificado` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `trigger_debug_log` (
  `log_id` int(11) NOT NULL,
  `trigger_name` varchar(50) DEFAULT NULL,
  `log_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `message` varchar(255) DEFAULT NULL,
  `idliquidacion_val` int(11) DEFAULT NULL,
  `iddetalle_val` int(11) DEFAULT NULL,
  `estado_val` varchar(50) DEFAULT NULL,
  `planificacion_id_val` int(11) DEFAULT NULL,
  `distribucionhora_count` int(11) DEFAULT NULL,
  `insert_attempted` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--------------------------------------------------------------------------------------------------------------------------------------------------------------------- 

DELIMITER $$
CREATE TRIGGER `trg_after_liquidacion_insert` AFTER INSERT ON `liquidacion` FOR EACH ROW BEGIN
    DECLARE v_idplanificacion INT DEFAULT NULL;
    DECLARE v_iddetalle_inserted INT DEFAULT NULL;
    DECLARE v_dist_hora_count INT DEFAULT 0;

    INSERT INTO trigger_debug_log (trigger_name, message, idliquidacion_val, estado_val)
    VALUES ('insert', 'Trigger START', NEW.idliquidacion, NEW.estado);

    SELECT Idplanificacion INTO v_idplanificacion
    FROM planificacion
    WHERE idContratoCliente = NEW.idcontratocli
      AND YEAR(fechaplan) = YEAR(NEW.fecha)
      AND MONTH(fechaplan) = MONTH(NEW.fecha)
    LIMIT 1;

    INSERT INTO trigger_debug_log (trigger_name, message, idliquidacion_val, planificacion_id_val)
    VALUES ('insert', 'After Planificacion SELECT', NEW.idliquidacion, v_idplanificacion);

    IF v_idplanificacion IS NOT NULL THEN
        INSERT INTO `detalles_planificacion` (
            `Idplanificacion`, `idliquidacion`, `fechaliquidacion`, `estado`, `cantidahoras`
        ) VALUES (
            v_idplanificacion, NEW.idliquidacion, NEW.fecha, NEW.estado, NEW.cantidahoras
        );
        SET v_iddetalle_inserted = LAST_INSERT_ID();
        INSERT INTO trigger_debug_log (trigger_name, message, idliquidacion_val, iddetalle_val, estado_val)
        VALUES ('insert', 'After detalles_planificacion INSERT', NEW.idliquidacion, v_iddetalle_inserted, NEW.estado);

        IF v_iddetalle_inserted IS NOT NULL AND TRIM(UPPER(NEW.estado)) = 'COMPLETO' THEN
            INSERT INTO trigger_debug_log (trigger_name, message, idliquidacion_val, iddetalle_val, estado_val, insert_attempted)
            VALUES ('insert', 'CONDITION MET for distrib_planif', NEW.idliquidacion, v_iddetalle_inserted, NEW.estado, FALSE);

            SELECT COUNT(*) INTO v_dist_hora_count
            FROM `distribucionhora` dh
            WHERE dh.idliquidacion = NEW.idliquidacion;

            INSERT INTO trigger_debug_log (trigger_name, message, idliquidacion_val, distribucionhora_count)
            VALUES ('insert', 'Count from distribucionhora', NEW.idliquidacion, v_dist_hora_count);

            IF v_dist_hora_count > 0 THEN
                DELETE FROM `distribucion_planificacion` WHERE `iddetalle` = v_iddetalle_inserted;
                INSERT INTO trigger_debug_log (trigger_name, message, idliquidacion_val, iddetalle_val)
                VALUES ('insert', 'After DELETE from distrib_planif', NEW.idliquidacion, v_iddetalle_inserted);

                INSERT INTO `distribucion_planificacion` (
                    `iddetalle`, `idparticipante`, `porcentaje`, `horas_asignadas`
                )
                SELECT
                    v_iddetalle_inserted,
                    dh.participante,
                    dh.porcentaje,
                    COALESCE(dh.calculo, 0.00) -- Usar COALESCE como medida defensiva
                FROM `distribucionhora` dh
                WHERE dh.idliquidacion = NEW.idliquidacion;

                INSERT INTO trigger_debug_log (trigger_name, message, idliquidacion_val, iddetalle_val, insert_attempted)
                VALUES ('insert', 'After INSERT attempt to distrib_planif', NEW.idliquidacion, v_iddetalle_inserted, TRUE);
            ELSE
                INSERT INTO trigger_debug_log (trigger_name, message, idliquidacion_val, distribucionhora_count, insert_attempted)
                VALUES ('insert', 'Skipped INSERT (no rows in distribucionhora)', NEW.idliquidacion, v_dist_hora_count, FALSE);
            END IF;
        ELSE
            INSERT INTO trigger_debug_log (trigger_name, message, idliquidacion_val, iddetalle_val, estado_val, insert_attempted)
            VALUES ('insert', 'CONDITION NOT MET for distrib_planif', NEW.idliquidacion, v_iddetalle_inserted, NEW.estado, FALSE);
        END IF;
    ELSE
        INSERT INTO trigger_debug_log (trigger_name, message, idliquidacion_val, planificacion_id_val, insert_attempted)
        VALUES ('insert', 'v_idplanificacion IS NULL', NEW.idliquidacion, v_idplanificacion, FALSE);
    END IF;
    INSERT INTO trigger_debug_log (trigger_name, message, idliquidacion_val)
    VALUES ('insert', 'Trigger END', NEW.idliquidacion);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_liquidacion_update` AFTER UPDATE ON `liquidacion` FOR EACH ROW BEGIN
    DECLARE v_iddetalle INT DEFAULT NULL;
    DECLARE v_idplanif_for_insert INT DEFAULT NULL;

    SELECT dp.iddetalle INTO v_iddetalle
    FROM detalles_planificacion dp
    WHERE dp.idliquidacion = NEW.idliquidacion
    LIMIT 1;

    IF v_iddetalle IS NOT NULL THEN
        UPDATE `detalles_planificacion`
        SET
            `fechaliquidacion` = NEW.fecha,
            `estado` = NEW.estado,
            `cantidahoras` = NEW.cantidahoras,
            `modificado` = CURRENT_TIMESTAMP
        WHERE `iddetalle` = v_iddetalle;
    ELSE 
        SELECT p.Idplanificacion INTO v_idplanif_for_insert
        FROM planificacion p
        WHERE p.idContratoCliente = NEW.idcontratocli
          AND YEAR(p.fechaplan) = YEAR(NEW.fecha)
          AND MONTH(p.fechaplan) = MONTH(NEW.fecha)
        LIMIT 1;

        IF v_idplanif_for_insert IS NOT NULL THEN
            INSERT INTO `detalles_planificacion` (
                `Idplanificacion`,
                `idliquidacion`,
                `fechaliquidacion`,
                `estado`,
                `cantidahoras`
            ) VALUES (
                v_idplanif_for_insert,
                NEW.idliquidacion,
                NEW.fecha,
                NEW.estado,
                NEW.cantidahoras
            );
            SET v_iddetalle = LAST_INSERT_ID(); 
        END IF;
    END IF;

    IF v_iddetalle IS NOT NULL AND TRIM(UPPER(NEW.estado)) = 'COMPLETO' THEN
        DELETE FROM `distribucion_planificacion` WHERE `iddetalle` = v_iddetalle;
        
        INSERT INTO `distribucion_planificacion` (
            `iddetalle`,
            `idparticipante`,
            `porcentaje`,
            `horas_asignadas`
        )
        SELECT
            v_iddetalle,
            dh.participante,
            dh.porcentaje,
            dh.calculo 
        FROM `distribucionhora` dh
        WHERE dh.idliquidacion = NEW.idliquidacion;
    END IF;
END
$$
DELIMITER ;

----------------------------------------------------------------------------------------------------------------------------------------------------------------------
ALTER TABLE `detalles_planificacion`
  ADD PRIMARY KEY (`iddetalle`),
  ADD KEY `idx_Idplanificacion` (`Idplanificacion`),
  ADD KEY `idx_idliquidacion` (`idliquidacion`);
  
  ALTER TABLE `distribucion_planificacion`
  ADD PRIMARY KEY (`iddistribucionplan`),
  ADD KEY `idx_iddetalle` (`iddetalle`),
  ADD KEY `idx_idparticipante` (`idparticipante`);

  ALTER TABLE `planificacion`
  ADD PRIMARY KEY (`Idplanificacion`),
  ADD KEY `idx_idContratoCliente` (`idContratoCliente`),
  ADD KEY `idx_lider` (`lider`),
  ADD KEY `idx_editor` (`editor`);
  
  ALTER TABLE `trigger_debug_log`
  ADD PRIMARY KEY (`log_id`);
  
  ALTER TABLE `detalles_planificacion`
  MODIFY `iddetalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `distribucion_planificacion`
  MODIFY `iddistribucionplan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
  
  ALTER TABLE `planificacion`
  MODIFY `Idplanificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
  
  ALTER TABLE `trigger_debug_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;
  
  ALTER TABLE `detalles_planificacion`
  ADD CONSTRAINT `fk_detalles_planificacion_liquidacion` FOREIGN KEY (`idliquidacion`) REFERENCES `liquidacion` (`idliquidacion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalles_planificacion_planificacion` FOREIGN KEY (`Idplanificacion`) REFERENCES `planificacion` (`Idplanificacion`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `distribucion_planificacion`
  ADD CONSTRAINT `fk_distribucion_planificacion_detalles` FOREIGN KEY (`iddetalle`) REFERENCES `detalles_planificacion` (`iddetalle`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_distribucion_planificacion_empleado` FOREIGN KEY (`idparticipante`) REFERENCES `empleado` (`idempleado`) ON DELETE NO ACTION ON UPDATE CASCADE;
  
ALTER TABLE `planificacion`
  ADD CONSTRAINT `fk_planificacion_contratocliente` FOREIGN KEY (`idContratoCliente`) REFERENCES `contratocliente` (`idcontratocli`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_planificacion_editor` FOREIGN KEY (`editor`) REFERENCES `empleado` (`idempleado`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_planificacion_lider` FOREIGN KEY (`lider`) REFERENCES `empleado` (`idempleado`) ON DELETE NO ACTION ON UPDATE CASCADE;

---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
CREATE TABLE `vista_planificacion_vs_participantes_completado` (
`Idplanificacion` int(11)
,`NombrePlan` varchar(255)
,`MesPlan` varchar(7)
,`idContratoCliente` int(11)
,`NombreCliente` varchar(50)
,`HorasPlanificadasGlobal` int(11)
,`TotalHorasLiquidadasCompletadas` decimal(32,0)
,`PorcentajePlanCompletado` decimal(40,5)
,`IdParticipante` int(11)
,`NombreParticipante` varchar(50)
,`HorasCompletadasPorParticipante` decimal(32,2)
,`PorcentajeDelParticipanteEnCompletadas` decimal(40,7)
,`PorcentajeDelParticipanteEnPlanGlobal` decimal(40,7)
);

CREATE TABLE `vista_reporte_planificacion_vs_liquidacion` (
`Idplanificacion` int(11)
,`NombrePlan` varchar(255)
,`MesPlan` varchar(7)
,`AnioPlan` int(4)
,`MesPlanNumerico` int(2)
,`idContratoCliente` int(11)
,`NombreCliente` varchar(50)
,`HorasPlanificadas` int(11)
,`EstadoLiquidacion` varchar(50)
,`HorasLiquidadasPorEstado` decimal(32,0)
,`TotalHorasLiquidadasMes` decimal(32,0)
,`PorcentajeConsumidoPorEstado` decimal(40,5)
,`PorcentajeTotalConsumidoMes` decimal(40,5)
);
-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
DROP TABLE IF EXISTS `vista_planificacion_vs_participantes_completado`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_planificacion_vs_participantes_completado`  AS WITH PlanificacionHorasCompletadas AS (SELECT `p`.`Idplanificacion` AS `Idplanificacion`, `p`.`nombreplan` AS `nombreplan`, `p`.`fechaplan` AS `fechaplan`, `p`.`idContratoCliente` AS `idContratoCliente`, `p`.`horasplan` AS `HorasPlanificadasGlobal`, sum(case when `dp`.`estado` = 'Completo' then `dp`.`cantidahoras` else 0 end) AS `TotalHorasLiquidadasCompletadas` FROM (`planificacion` `p` left join `detalles_planificacion` `dp` on(`p`.`Idplanificacion` = `dp`.`Idplanificacion`)) GROUP BY `p`.`Idplanificacion`, `p`.`nombreplan`, `p`.`fechaplan`, `p`.`idContratoCliente`, `p`.`horasplan`), HorasCompletadasPorParticipante AS (SELECT `dp`.`Idplanificacion` AS `Idplanificacion`, `dplan`.`idparticipante` AS `idparticipante`, sum(`dplan`.`horas_asignadas`) AS `HorasAsignadasAlParticipante` FROM (`detalles_planificacion` `dp` join `distribucion_planificacion` `dplan` on(`dp`.`iddetalle` = `dplan`.`iddetalle`)) WHERE `dp`.`estado` = 'Completo' GROUP BY `dp`.`Idplanificacion`, `dplan`.`idparticipante`)  SELECT `phc`.`Idplanificacion` AS `Idplanificacion`, `phc`.`nombreplan` AS `NombrePlan`, date_format(`phc`.`fechaplan`,'%Y-%m') AS `MesPlan`, `phc`.`idContratoCliente` AS `idContratoCliente`, `cli`.`nombrecomercial` AS `NombreCliente`, `phc`.`HorasPlanificadasGlobal` AS `HorasPlanificadasGlobal`, coalesce(`phc`.`TotalHorasLiquidadasCompletadas`,0) AS `TotalHorasLiquidadasCompletadas`, CASE WHEN `phc`.`HorasPlanificadasGlobal` is null OR `phc`.`HorasPlanificadasGlobal` = 0 THEN 0 ELSE coalesce(`phc`.`TotalHorasLiquidadasCompletadas`,0) * 100.0 / `phc`.`HorasPlanificadasGlobal` END AS `PorcentajePlanCompletado`, `hpp`.`idparticipante` AS `IdParticipante`, `emp`.`nombrecorto` AS `NombreParticipante`, coalesce(`hpp`.`HorasAsignadasAlParticipante`,0) AS `HorasCompletadasPorParticipante`, CASE WHEN coalesce(`phc`.`TotalHorasLiquidadasCompletadas`,0) = 0 THEN 0 ELSE coalesce(`hpp`.`HorasAsignadasAlParticipante`,0) * 100.0 / `phc`.`TotalHorasLiquidadasCompletadas` END AS `PorcentajeDelParticipanteEnCompletadas`, CASE WHEN `phc`.`HorasPlanificadasGlobal` is null OR `phc`.`HorasPlanificadasGlobal` = 0 THEN 0 ELSE coalesce(`hpp`.`HorasAsignadasAlParticipante`,0) * 100.0 / `phc`.`HorasPlanificadasGlobal` END AS `PorcentajeDelParticipanteEnPlanGlobal` FROM ((((`planificacionhorascompletadas` `phc` left join `horascompletadasporparticipante` `hpp` on(`phc`.`Idplanificacion` = `hpp`.`Idplanificacion`)) left join `empleado` `emp` on(`hpp`.`idparticipante` = `emp`.`idempleado`)) left join `contratocliente` `cc` on(`phc`.`idContratoCliente` = `cc`.`idcontratocli`)) left join `cliente` `cli` on(`cc`.`idcliente` = `cli`.`idcliente`)) ORDER BY date_format(`phc`.`fechaplan`,'%Y-%m') DESC, `phc`.`Idplanificacion` ASC, `emp`.`nombrecorto` ASC ;

DROP TABLE IF EXISTS `vista_reporte_planificacion_vs_liquidacion`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_reporte_planificacion_vs_liquidacion`  AS WITH PlanificacionConTotalesLiquidadas AS (SELECT `p`.`Idplanificacion` AS `Idplanificacion`, `p`.`nombreplan` AS `nombreplan`, `p`.`fechaplan` AS `fechaplan`, `p`.`idContratoCliente` AS `idContratoCliente`, `p`.`horasplan` AS `horasplan`, sum(`dp`.`cantidahoras`) AS `TotalHorasLiquidadasMes` FROM (`planificacion` `p` left join `detalles_planificacion` `dp` on(`p`.`Idplanificacion` = `dp`.`Idplanificacion`)) GROUP BY `p`.`Idplanificacion`, `p`.`nombreplan`, `p`.`fechaplan`, `p`.`idContratoCliente`, `p`.`horasplan`), PlanificacionDetallePorEstado AS (SELECT `p`.`Idplanificacion` AS `Idplanificacion`, `dp`.`estado` AS `EstadoLiquidacion`, sum(`dp`.`cantidahoras`) AS `HorasLiquidadasPorEstado` FROM (`planificacion` `p` join `detalles_planificacion` `dp` on(`p`.`Idplanificacion` = `dp`.`Idplanificacion`)) GROUP BY `p`.`Idplanificacion`, `dp`.`estado`)  SELECT `ptl`.`Idplanificacion` AS `Idplanificacion`, `ptl`.`nombreplan` AS `NombrePlan`, date_format(`ptl`.`fechaplan`,'%Y-%m') AS `MesPlan`, year(`ptl`.`fechaplan`) AS `AnioPlan`, month(`ptl`.`fechaplan`) AS `MesPlanNumerico`, `ptl`.`idContratoCliente` AS `idContratoCliente`, `cli`.`nombrecomercial` AS `NombreCliente`, `ptl`.`horasplan` AS `HorasPlanificadas`, coalesce(`pdpe`.`EstadoLiquidacion`,'Sin Liquidaciones') AS `EstadoLiquidacion`, coalesce(`pdpe`.`HorasLiquidadasPorEstado`,0) AS `HorasLiquidadasPorEstado`, coalesce(`ptl`.`TotalHorasLiquidadasMes`,0) AS `TotalHorasLiquidadasMes`, CASE WHEN `ptl`.`horasplan` is null OR `ptl`.`horasplan` = 0 THEN 0 ELSE coalesce(`pdpe`.`HorasLiquidadasPorEstado`,0) * 100.0 / `ptl`.`horasplan` END AS `PorcentajeConsumidoPorEstado`, CASE WHEN `ptl`.`horasplan` is null OR `ptl`.`horasplan` = 0 THEN 0 ELSE coalesce(`ptl`.`TotalHorasLiquidadasMes`,0) * 100.0 / `ptl`.`horasplan` END AS `PorcentajeTotalConsumidoMes` FROM (((`planificacioncontotalesliquidadas` `ptl` left join `planificaciondetalleporestado` `pdpe` on(`ptl`.`Idplanificacion` = `pdpe`.`Idplanificacion`)) left join `contratocliente` `cc` on(`ptl`.`idContratoCliente` = `cc`.`idcontratocli`)) left join `cliente` `cli` on(`cc`.`idcliente` = `cli`.`idcliente`));
--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

---ELIMINAR LIQUIDACIONES NO ACTIVAS
-------------------------------------

SELECT * FROM `liquidacion` WHERE ACTIVO=0; --> 1,2,34,50
SELECT * FROM `distribucionhora` WHERE idliquidacion in(1,2,34,50);
DELETE FROM `distribucionhora` WHERE idliquidacion in(1,2,34,50);
DELETE FROM `liquidacion` WHERE ACTIVO=0;
---------------------------------------------------------------------------------------------------------------------------
-- Script para sincronizar datos históricos de liquidaciones y distribución de horas con la tabla de planificaciones.

-- Limpiar tablas de destino para evitar duplicados
DELETE FROM distribucion_planificacion;
DELETE FROM detalles_planificacion;

-- Insertar datos en detalles_planificacion
INSERT INTO detalles_planificacion (Idplanificacion, idliquidacion, fechaliquidacion, estado, cantidahoras)
SELECT
    p.Idplanificacion,
    l.idliquidacion,
    l.fecha,
    l.estado,
    l.cantidahoras
FROM
    planificacion p
JOIN
    liquidacion l ON p.idContratoCliente = l.idcontratocli
WHERE
    MONTH(p.fechaplan) = MONTH(l.fecha) AND YEAR(p.fechaplan) = YEAR(l.fecha) AND l.activo=1;

-- Insertar datos en distribucion_planificacion para liquidaciones completas
INSERT INTO distribucion_planificacion (iddetalle, idparticipante, porcentaje, horas_asignadas)
SELECT
    dp.iddetalle,
    dh.participante,
    dh.porcentaje,
    dh.calculo 
FROM
    detalles_planificacion dp
JOIN
    liquidacion l ON dp.idliquidacion = l.idliquidacion
JOIN
    distribucionhora dh ON l.idliquidacion = dh.idliquidacion
WHERE
    l.estado = 'Completo';

----------------------------------------------------------------------------------------------------------------------
DELIMITER $$
CREATE PROCEDURE actualizar_planificacion_existente()
BEGIN
    -- Actualizar detalles de planificación existentes
    UPDATE detalles_planificacion dp
    JOIN liquidacion l ON dp.idliquidacion = l.idliquidacion
    JOIN planificacion p ON l.idcontratocli = p.idContratoCliente AND MONTH(l.fecha) = MONTH(p.fechaplan) AND YEAR(l.fecha) = YEAR(p.fechaplan)
    SET
        dp.fechaliquidacion = l.fecha,
        dp.estado = l.estado,
        dp.cantidahoras = l.cantidahoras
    WHERE l.activo = 1;

    -- Insertar nuevos detalles de planificación
    INSERT INTO detalles_planificacion (Idplanificacion, idliquidacion, fechaliquidacion, estado, cantidahoras)
    SELECT
        p.Idplanificacion,
        l.idliquidacion,
        l.fecha,
        l.estado,
        l.cantidahoras
    FROM
        planificacion p
    JOIN
        liquidacion l ON p.idContratoCliente = l.idcontratocli
    LEFT JOIN
        detalles_planificacion dp ON l.idliquidacion = dp.idliquidacion
    WHERE
        MONTH(p.fechaplan) = MONTH(l.fecha) AND YEAR(p.fechaplan) = YEAR(l.fecha) AND l.activo = 1 AND dp.idliquidacion IS NULL;

    -- Eliminar y volver a insertar la distribución de planificación para liquidaciones completas
    DELETE FROM distribucion_planificacion WHERE iddetalle IN (SELECT iddetalle FROM detalles_planificacion dp JOIN liquidacion l ON dp.idliquidacion = l.idliquidacion WHERE l.estado = 'Completo');

    INSERT INTO distribucion_planificacion (iddetalle, idparticipante, porcentaje, horas_asignadas)
    SELECT
        dp.iddetalle,
        dh.participante,
        dh.porcentaje,
        dh.calculo 
    FROM
        detalles_planificacion dp
    JOIN
        liquidacion l ON dp.idliquidacion = l.idliquidacion
    JOIN
        distribucionhora dh ON l.idliquidacion = dh.idliquidacion
    WHERE
        l.estado = 'Completo';
END$$

DELIMITER ;
--------------------------------------------------------------------------------------------------------------------------------------
CREATE OR REPLACE VIEW vista_progreso_colaborador_vs_meta AS
SELECT 
    e.idempleado,
    e.nombrecorto AS NombreColaborador,
    e.horasmeta AS HorasMeta,
    YEAR(l.fecha) AS Anio,
    MONTH(l.fecha) AS Mes,
    SUM(dh.calculo) AS HorasCompletadas,
    (SUM(dh.calculo) / e.horasmeta) * 100 AS PorcentajeCumplimiento
FROM 
    distribucionhora dh
JOIN 
    liquidacion l ON dh.idliquidacion = l.idliquidacion
JOIN 
    empleado e ON dh.participante = e.idempleado
WHERE 
    l.estado = 'Completo'
GROUP BY 
    e.idempleado, 
    e.nombrecorto, 
    e.horasmeta, 
    YEAR(l.fecha), 
    MONTH(l.fecha)
ORDER BY
    Anio DESC,
    Mes DESC,
    NombreColaborador;