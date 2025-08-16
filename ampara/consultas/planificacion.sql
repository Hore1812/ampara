-- Consulta 1: Obtener planificación mensual de cada contrato de cliente para un mes específico (ej. Julio 2025)
SELECT
    p.Idplanificacion,
    c.nombrecomercial AS cliente,
    cc.descripcion AS contrato_descripcion,
    p.nombreplan,
    p.fechaplan,
    p.horasplan,
    e.nombrecorto AS lider_planificacion,
    p.comentario AS comentario_planificacion,
    (SELECT SUM(dl.cantidahoras) FROM detalles_planificacion dl WHERE dl.Idplanificacion = p.Idplanificacion) AS horas_ejecutadas_en_plan,
    p.horasplan - COALESCE((SELECT SUM(dl.cantidahoras) FROM detalles_planificacion dl WHERE dl.Idplanificacion = p.Idplanificacion), 0) AS horas_restantes_plan
FROM
    planificacion p
JOIN
    contratocliente cc ON p.idContratoCliente = cc.idcontratocli
JOIN
    cliente c ON cc.idcliente = c.idcliente
JOIN
    empleado e ON p.lider = e.idempleado
WHERE
    p.fechaplan >= '2025-07-01' AND p.fechaplan < '2025-08-01' -- Para Julio 2025
ORDER BY
    c.nombrecomercial, p.fechaplan;

-- Consulta 2: Ver total cantidad de horas liquidadas en un determinado mes y la distribución de este total en los diferentes estados.
SELECT
    c.nombrecomercial AS cliente,
    cc.descripcion AS contrato_descripcion,
    YEAR(l.fecha) AS anio,
    MONTH(l.fecha) AS mes,
    l.estado,
    SUM(l.cantidahoras) AS total_horas_liquidadas_estado
FROM
    liquidacion l
JOIN
    contratocliente cc ON l.idcontratocli = cc.idcontratocli
JOIN
    cliente c ON cc.idcliente = c.idcliente
WHERE
    YEAR(l.fecha) = 2025 AND MONTH(l.fecha) = 5 -- Ejemplo para Mayo 2025
GROUP BY
    c.nombrecomercial, cc.descripcion, YEAR(l.fecha), MONTH(l.fecha), l.estado
ORDER BY
    c.nombrecomercial, anio, mes, l.estado;

-- Consulta 3: Ver los colaboradores y sus horas asignadas según las liquidaciones completadas en las que participaron en un mes específico.
SELECT
    e.nombrecorto AS colaborador,
    c.nombrecomercial AS cliente,
    t.descripcion AS tema_liquidacion,
    l.asunto AS asunto_liquidacion,
    dp.horas_asignadas,
    dp.porcentaje,
    l.fecha AS fecha_liquidacion_completada
FROM
    distribucion_planificacion dp
JOIN
    detalles_planificacion dpl ON dp.iddetalle = dpl.iddetalle
JOIN
    liquidacion l ON dpl.idliquidacion = l.idliquidacion
JOIN
    empleado e ON dp.idparticipante = e.idempleado
JOIN
    planificacion p ON dpl.Idplanificacion = p.Idplanificacion
JOIN
    contratocliente cc ON p.idContratoCliente = cc.idcontratocli
JOIN
    cliente c ON cc.idcliente = c.idcliente
JOIN
    tema t ON l.tema = t.idtema
WHERE
    dpl.estado = 'Completo'
    AND YEAR(p.fechaplan) = 2025 AND MONTH(p.fechaplan) = 5 -- Ejemplo para planificación de Mayo 2025
    -- AND YEAR(l.fecha) = 2025 AND MONTH(l.fecha) = 5 -- Si se quiere filtrar por fecha de liquidación en lugar de fecha de plan
ORDER BY
    colaborador, cliente, l.fecha;

-- Consulta 4: Mostrar comparativa: horas planificadas mensuales vs. sumatoria de horas de liquidaciones (por contrato y mes).
SELECT
    p.nombreplan,
    c.nombrecomercial AS cliente,
    cc.idcontratocli,
    cc.descripcion AS contrato_descripcion,
    p.fechaplan,
    p.horasplan AS horas_planificadas,
    COALESCE(SUM(dpl.cantidahoras), 0) AS horas_ejecutadas_en_planificacion,
    (p.horasplan - COALESCE(SUM(dpl.cantidahoras), 0)) AS diferencia_horas
FROM
    planificacion p
JOIN
    contratocliente cc ON p.idContratoCliente = cc.idcontratocli
JOIN
    cliente c ON cc.idcliente = c.idcliente
LEFT JOIN
    detalles_planificacion dpl ON p.Idplanificacion = dpl.Idplanificacion
WHERE
    YEAR(p.fechaplan) = 2025 AND MONTH(p.fechaplan) = 7 -- Ejemplo para Julio 2025
GROUP BY
    p.Idplanificacion, c.nombrecomercial, cc.idcontratocli, cc.descripcion, p.fechaplan, p.horasplan, p.nombreplan
ORDER BY
    c.nombrecomercial, p.fechaplan;

-- Consulta 5: Comparativa de horas planificadas vs. horas liquidadas (menor, igual, mayor) por contrato y mes.
-- Esta consulta muestra la suma de todas las horas de liquidación para un contrato/mes, sin importar el estado,
-- y las compara con las horas planificadas para ese contrato/mes.
WITH HorasLiquidadasPorContratoMes AS (
    SELECT
        l.idcontratocli,
        YEAR(l.fecha) AS anio_liq,
        MONTH(l.fecha) AS mes_liq,
        SUM(l.cantidahoras) AS total_horas_liquidadas_mes
    FROM
        liquidacion l
    GROUP BY
        l.idcontratocli, YEAR(l.fecha), MONTH(l.fecha)
)
SELECT
    p.Idplanificacion,
    c.nombrecomercial AS cliente,
    cc.descripcion AS contrato_descripcion,
    p.nombreplan,
    p.fechaplan,
    p.horasplan,
    COALESCE(hlm.total_horas_liquidadas_mes, 0) AS total_horas_liquidadas_del_mes_contrato,
    CASE
        WHEN COALESCE(hlm.total_horas_liquidadas_mes, 0) < p.horasplan THEN 'Menor a lo planificado'
        WHEN COALESCE(hlm.total_horas_liquidadas_mes, 0) = p.horasplan THEN 'Igual a lo planificado'
        ELSE 'Mayor a lo planificado'
    END AS comparacion_plan_vs_liquidacion,
    (COALESCE(hlm.total_horas_liquidadas_mes, 0) - p.horasplan) AS diferencia_abs
FROM
    planificacion p
JOIN
    contratocliente cc ON p.idContratoCliente = cc.idcontratocli
JOIN
    cliente c ON cc.idcliente = c.idcliente
LEFT JOIN
    HorasLiquidadasPorContratoMes hlm ON p.idContratoCliente = hlm.idcontratocli
                                     AND YEAR(p.fechaplan) = hlm.anio_liq
                                     AND MONTH(p.fechaplan) = hlm.mes_liq
WHERE
    YEAR(p.fechaplan) = 2025 AND MONTH(p.fechaplan) = 5 -- Ejemplo para Mayo 2025
ORDER BY
    c.nombrecomercial, p.fechaplan;

-- Consulta Adicional: Ver detalle de liquidaciones asociadas a una planificación específica
SELECT
    p.nombreplan,
    c.nombrecomercial AS cliente,
    l.idliquidacion,
    l.asunto AS asunto_liquidacion,
    l.fecha AS fecha_liquidacion_original,
    dpl.fechaliquidacion AS fecha_detalle_plan,
    dpl.estado AS estado_detalle_plan,
    dpl.cantidahoras AS horas_detalle_plan,
    (SELECT GROUP_CONCAT(CONCAT(e.nombrecorto, ' (', dp.porcentaje, '%, ', dp.horas_asignadas, 'h)'))
     FROM distribucion_planificacion dp
     JOIN empleado e ON dp.idparticipante = e.idempleado
     WHERE dp.iddetalle = dpl.iddetalle
    ) AS participantes_distribucion
FROM
    detalles_planificacion dpl
JOIN
    planificacion p ON dpl.Idplanificacion = p.Idplanificacion
JOIN
    liquidacion l ON dpl.idliquidacion = l.idliquidacion
JOIN
    contratocliente cc ON p.idContratoCliente = cc.idcontratocli
JOIN
    cliente c ON cc.idcliente = c.idcliente
WHERE
    p.Idplanificacion = 1 -- Cambiar por el Idplanificacion deseado
ORDER BY
    l.fecha;
