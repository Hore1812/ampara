-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 16-08-2025 a las 06:39:02
-- Versión del servidor: 10.11.10-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u505676278_inet_ampara`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE PROCEDURE `actualizar_planificacion_existente` ()   BEGIN
    -- Parte 1: Corregir registros existentes en detalles_planificacion.
    -- Esto incluye actualizar los datos Y corregir el enlace a la planificación si es incorrecto.
    -- Se busca el plan correcto (p_correcta) basado en la liquidación y se actualiza el detalle.
    UPDATE detalles_planificacion dp
    JOIN liquidacion l ON dp.idliquidacion = l.idliquidacion
    JOIN planificacion p_correcta ON l.idcontratocli = p_correcta.idContratoCliente
                                  AND MONTH(l.fecha) = MONTH(p_correcta.fechaplan)
                                  AND YEAR(l.fecha) = YEAR(p_correcta.fechaplan)
    SET
        dp.Idplanificacion = p_correcta.Idplanificacion, -- Clave: Corrige el enlace al plan correcto
        dp.fechaliquidacion = l.fecha,
        dp.estado = l.estado,
        dp.cantidahoras = l.cantidahoras
    WHERE
        l.activo = 1;

    -- Parte 2: Insertar nuevos detalles para liquidaciones que aún no tienen una entrada.
    -- Esta lógica no cambia, pero ahora es más segura porque la Parte 1 limpió los datos.
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
        MONTH(p.fechaplan) = MONTH(l.fecha)
        AND YEAR(p.fechaplan) = YEAR(l.fecha)
        AND l.activo = 1
        AND dp.idliquidacion IS NULL;

    -- Parte 3: Re-sincronizar la distribución de horas para liquidaciones completas.
    -- Esta lógica no cambia. Se asegura de que las horas se atribuyan correctamente.
    DELETE FROM distribucion_planificacion
    WHERE iddetalle IN (
        SELECT iddetalle
        FROM detalles_planificacion dp
        JOIN liquidacion l ON dp.idliquidacion = l.idliquidacion
        WHERE l.estado = 'Completo'
    );

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `adendacliente`
--

CREATE TABLE `adendacliente` (
  `idadendacli` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `fechainicio` date NOT NULL,
  `fechafin` date NOT NULL,
  `horasfijasmes` int(11) NOT NULL,
  `horasmaxbolsa` int(11) NOT NULL,
  `planhorasfijas` int(11) NOT NULL,
  `comentarios` varchar(500) NOT NULL,
  `idcontratocli` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `adendacliente`
--

INSERT INTO `adendacliente` (`idadendacli`, `descripcion`, `fechainicio`, `fechafin`, `horasfijasmes`, `horasmaxbolsa`, `planhorasfijas`, `comentarios`, `idcontratocli`) VALUES
(1, 'Adenda 1 CALA', '2023-09-01', '2024-02-29', 8, 2, 10, '', 1),
(2, 'Adenda 2 CALA', '2024-03-01', '2025-02-28', 10, 2, 12, '', 1),
(3, 'Adenda 1 DOLPHIN', '2024-09-01', '2025-08-31', 10, 0, 10, '', 2),
(4, 'Adenda 1 IPT', '2021-08-18', '2022-08-17', 8, 2, 10, '', 3),
(5, 'Adenda 2 IPT', '2022-08-18', '2023-08-17', 8, 2, 10, '', 3),
(6, 'Adenda 3 IPT', '2023-08-18', '2024-08-31', 10, 2, 12, '', 3),
(7, 'Adenda 4 IPT', '2024-09-01', '2025-08-31', 15, 2, 18, '', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `adendaempleado`
--

CREATE TABLE `adendaempleado` (
  `idadendaemp` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `fechainicio` date NOT NULL,
  `fechafin` date NOT NULL,
  `salariobruto` decimal(7,2) NOT NULL,
  `costohoraextra` decimal(7,2) NOT NULL,
  `comentarios` varchar(500) NOT NULL,
  `activo` int(11) NOT NULL,
  `idcontratoemp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `adendaempleado`
--

INSERT INTO `adendaempleado` (`idadendaemp`, `descripcion`, `fechainicio`, `fechafin`, `salariobruto`, `costohoraextra`, `comentarios`, `activo`, `idcontratoemp`) VALUES
(1, 'Adenda 1', '2024-05-07', '2025-04-30', 2000.00, 0.00, '', 0, 2),
(2, 'Adenda 2', '2025-05-01', '2025-10-31', 2200.00, 0.00, '', 0, 2),
(3, 'Adenda 1', '2024-10-01', '2025-09-30', 3000.00, 0.00, '', 0, 3),
(4, 'Adenda 1', '2025-01-03', '2025-07-03', 2500.00, 0.00, '', 0, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anuncio`
--

CREATE TABLE `anuncio` (
  `idanuncio` int(11) NOT NULL,
  `fechainicio` date NOT NULL,
  `fechafin` date NOT NULL,
  `rutaarchivo` varchar(500) NOT NULL,
  `comentario` varchar(500) NOT NULL,
  `acargode` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calendario`
--

CREATE TABLE `calendario` (
  `idcalendario` int(11) NOT NULL,
  `asunto` varchar(150) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `colorfondo` varchar(25) NOT NULL,
  `colortexto` varchar(25) NOT NULL,
  `lider` int(11) NOT NULL,
  `acargode` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `idcliente` int(11) NOT NULL,
  `razonsocial` varchar(50) NOT NULL,
  `nombrecomercial` varchar(50) NOT NULL,
  `ruc` varchar(15) NOT NULL,
  `direccion` varchar(150) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `sitioweb` varchar(150) NOT NULL,
  `representante` varchar(100) NOT NULL,
  `telrepresentante` varchar(15) NOT NULL,
  `correorepre` varchar(150) NOT NULL,
  `gerente` varchar(150) NOT NULL,
  `telgerente` varchar(15) NOT NULL,
  `correogerente` varchar(150) NOT NULL,
  `activo` int(11) NOT NULL DEFAULT 1,
  `editor` int(11) NOT NULL DEFAULT 1,
  `registrado` timestamp NOT NULL DEFAULT current_timestamp(),
  `modificado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`idcliente`, `razonsocial`, `nombrecomercial`, `ruc`, `direccion`, `telefono`, `sitioweb`, `representante`, `telrepresentante`, `correorepre`, `gerente`, `telgerente`, `correogerente`, `activo`, `editor`, `registrado`, `modificado`) VALUES
(1, 'CALA SERVICIOS INTEGRALES E.I.R.L.', 'CALA', '20606544937', 'Jirón San Diego N° 282, Departamento N° 203 - Surquillo', '923418300', 'https://www.mifibra.pe/', 'Alfredo Araujo', '995887204', 'alfredo.araujo@mifibra.pe', 'Israel Tokashiki Yakibu', '995736334', 'israeltoka@gmail.com', 1, 1, '2025-07-07 12:29:24', '2025-07-07 12:29:24'),
(2, 'DOLPHIN TELECOM DEL PERU S.A.C.', 'DOLPHIN', '20467305931', 'Jirón Preciados N° 149, en el distrito de Santiago de Surco', '951680819', 'https://dolphin.pe/', 'Javier Sánchez', '945119964', 'javier.sanchez@dolphin.pe', 'Fernando Javier Sánchez Benalcazar', '945119964', 'javier.sanchez@dolphin.pe', 1, 1, '2025-07-07 12:29:24', '2025-07-07 12:29:24'),
(3, 'INTERNET PARA TODOS S.A.C.', 'IPT', '20602982174', 'Av. Manuel Olguín N° 325, distrito de Santiago de Surco', '953627291', 'https://www.ipt.pe/', 'Sheyla Rojas', '942495272', 'sheyla.reyes@ipt.pe', 'Teresa Gomes De Almeida', '', 'teresa.gomes@ipt.pe', 1, 1, '2025-07-07 12:29:24', '2025-07-07 12:29:24'),
(4, 'FIBERMAX TELECOM S.A.C.', 'FIBERMAX', '20432857183', 'n Calle Ernesto Diez Canseco N°\r\n236, Oficina N° 403 - Miraflores', '958155646', 'https://www.fibermax.com.pe/', 'Kattya Vega', '934310215', 'kattya.vega@intermax.pe', 'Pedro Luis Esponda Villavicencio', '996591315', 'pedro.esponda@intermax.pe', 1, 1, '2025-07-07 12:29:24', '2025-07-07 12:29:24'),
(5, 'INTERMAX S.A.C.', 'INTERMAX', '20600609239', 'Av. Ricardo Palma 341, Oficina 701, Miraflores, Lima', '(01) 7401000', 'https://intermax.pe/#/', 'Kattya Vega', '934310215', 'kattya.vega@intermax.pe', 'Rafael Ángel Yguey Oshiro', '954848710', 'rafael.yguey@intermax.pe', 1, 1, '2025-07-07 12:29:24', '2025-07-07 12:29:24'),
(6, 'PANGEACO S.A.C.', 'PANGEACO', '20606188511', 'Javier Prado Este N° 444, piso 14, oficinas\r\n1401 - 1402, distrito de San Isidro', '', 'https://pe.linkedin.com/company/pangea-peru', 'Julio Cieza', '952934110', 'julio.cieza@pangeaco.pe', 'Luz Giovanna Piskulich Nevado', '', 'giovanna.piskulich@pangeaco.pe', 1, 1, '2025-07-07 12:29:24', '2025-07-07 12:29:24'),
(7, 'PRISONTEC S.A.C.', 'PRISONTEC', '20563709601', 'n Av. Del Pinar N° 180, Oficina 1004 – Santiago de Surco, Lima', '(01) 2566868', 'https://www.prisontec.com/portalweb/', 'Raiza Hernandez', '959717996', 'raiza.hernandez@prisontec.com', 'Augusto Eduardo Fernández Márquez', '', '', 1, 1, '2025-07-07 12:29:24', '2025-07-07 12:29:24'),
(8, 'URBI PROYECTOS SOCIEDAD ANONIMA CERRADA', 'PUNTO DE ACCESO', '20600796438', 'Calle Carlos Villarán Nro. 140, Urb. Santa Catalina, La Victoria, Lima', '(01) 219 2000', 'https://urbiproyectos.pe/', 'Kazhia Fernandez', '939301984', 'kafernandez@intercorp.com.pe', 'Úrsula Consuelo Sánchez Gamarra', '', 'usanchezg@intercorp.com.pe', 1, 1, '2025-07-07 12:29:24', '2025-07-07 12:29:24'),
(9, 'TELECOM BUSINESS PARTNER S.A.C.', 'AMPARA', '20600282205', 'Calle Mártir Olaya N° 129, Oficina N° 1905, Miraflores', '510 1883', 'ampara.pe', 'Juan Carlos Cornejo Cuzzi', '', '', 'Juan Carlos Cornejo Cuzzi', '', '', 1, 1, '2025-07-07 12:29:24', '2025-07-07 12:29:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contratocliente`
--

CREATE TABLE `contratocliente` (
  `idcontratocli` int(11) NOT NULL,
  `idcliente` int(11) NOT NULL,
  `lider` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `fechainicio` date NOT NULL,
  `fechafin` date DEFAULT NULL,
  `horasfijasmes` int(11) NOT NULL,
  `costohorafija` decimal(7,2) NOT NULL,
  `mesescontrato` int(11) NOT NULL,
  `totalhorasfijas` int(11) NOT NULL,
  `tipobolsa` varchar(50) NOT NULL,
  `costohoraextra` decimal(7,2) NOT NULL,
  `montofijomes` decimal(7,2) NOT NULL,
  `planmontomes` decimal(7,2) NOT NULL,
  `planhoraextrames` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `tipohora` varchar(500) NOT NULL,
  `activo` int(11) NOT NULL,
  `editor` int(11) NOT NULL DEFAULT 1,
  `registrado` timestamp NOT NULL DEFAULT current_timestamp(),
  `modificado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `contratocliente`
--

INSERT INTO `contratocliente` (`idcontratocli`, `idcliente`, `lider`, `descripcion`, `fechainicio`, `fechafin`, `horasfijasmes`, `costohorafija`, `mesescontrato`, `totalhorasfijas`, `tipobolsa`, `costohoraextra`, `montofijomes`, `planmontomes`, `planhoraextrames`, `status`, `tipohora`, `activo`, `editor`, `registrado`, `modificado`) VALUES
(1, 1, 4, 'Contrato Principal CALA', '2022-09-01', '2023-08-31', 8, 425.00, 12, 96, 'Mensual', 440.00, 3400.00, 4280.00, 2, 'Vigente', 'Soporte', 0, 2, '2025-07-07 12:35:05', '2025-08-13 12:41:01'),
(2, 2, 6, 'Contrato Principal DOLPHIN', '2023-09-01', '2024-08-31', 10, 500.00, 12, 120, 'Anual', 550.00, 10.00, 5000.00, 0, 'Vigente', 'Soporte', 1, 2, '2025-07-07 12:35:05', '2025-08-08 16:01:29'),
(3, 3, 6, 'Contrato Principal IPT', '2020-08-14', '2021-08-17', 3, 350.00, 12, 36, 'Mensual', 420.00, 3.00, 1890.00, 2, 'Vigente', 'Soporte', 1, 2, '2025-07-07 12:35:05', '2025-08-08 16:01:22'),
(4, 4, 3, 'Contrato Principal Fibermax', '2022-01-01', '2023-12-31', 8, 430.00, 24, 192, 'Mensual', 460.00, 3440.00, 4360.00, 2, 'Vigente', 'Soporte', 1, 2, '2025-07-07 12:35:05', '2025-08-13 12:42:45'),
(5, 5, 3, 'Contrato Principal Intermax', '2022-02-01', '2024-01-31', 20, 306.00, 24, 480, 'Mensual', 460.00, 6120.00, 7040.00, 2, 'Vigente', 'Soporte', 1, 1, '2025-07-07 12:35:05', '2025-07-07 12:35:05'),
(6, 6, 4, 'Contrato Principal PangeaCo', '2022-07-01', '2022-12-30', 8, 435.00, 6, 48, 'Mensual', 460.00, 3480.00, 4400.00, 2, 'Vigente', 'Soporte', 1, 1, '2025-07-07 12:35:05', '2025-07-07 12:35:05'),
(8, 7, 4, 'Contrato Principal Prisontec', '2021-02-01', '2022-01-31', 10, 435.00, 12, 120, 'Mensual', 460.00, 4350.00, 5270.00, 2, 'Vigente', 'Soporte', 1, 1, '2025-07-07 12:35:05', '2025-07-07 12:35:05'),
(9, 8, 6, 'Contrato Principal Punto de Acceso', '2024-08-29', '2024-12-02', 3, 500.00, 3, 9, 'Mensual', 530.00, 1500.00, 3090.00, 3, 'Vigente', 'Soporte', 1, 2, '2025-07-07 12:35:05', '2025-08-13 12:43:11'),
(10, 5, 6, 'Contrato No Soporte Intermax', '2025-02-19', NULL, 20, 0.00, 12, 240, '', 0.00, 0.00, 0.00, 0, 'Vigente', 'No Soporte', 1, 11, '2025-07-07 12:35:05', '2025-07-23 00:21:12'),
(11, 9, 2, '', '2015-04-09', '0000-00-00', 0, 0.00, 0, 0, '', 0.00, 0.00, 0.00, 0, 'Vigente', 'Horas Internas', 1, 1, '2025-07-07 12:35:05', '2025-07-07 12:35:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contratoempleado`
--

CREATE TABLE `contratoempleado` (
  `idcontratoemp` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `fechainicio` date NOT NULL,
  `fechafin` date NOT NULL,
  `modalidad` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `salariobruto` decimal(7,2) NOT NULL,
  `entidadbancaria` varchar(50) NOT NULL,
  `tipocuenta` varchar(50) NOT NULL,
  `numcuenta1` varchar(50) NOT NULL,
  `numcuenta2` varchar(50) NOT NULL,
  `comentario` varchar(500) NOT NULL,
  `direccion` varchar(50) NOT NULL,
  `area` varchar(50) NOT NULL,
  `puesto` varchar(50) NOT NULL,
  `activo` int(11) NOT NULL,
  `idemp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `contratoempleado`
--

INSERT INTO `contratoempleado` (`idcontratoemp`, `descripcion`, `fechainicio`, `fechafin`, `modalidad`, `status`, `salariobruto`, `entidadbancaria`, `tipocuenta`, `numcuenta1`, `numcuenta2`, `comentario`, `direccion`, `area`, `puesto`, `activo`, `idemp`) VALUES
(1, 'Contrato', '2023-02-01', '0000-00-00', 'Planilla', 'Vencido', 2500.00, 'BCP', 'Sueldo', '19199750519098', '', '', 'Operaciones', 'Legal Regulatorio', 'Asociado Ejecutivo', 0, 1),
(2, 'Contrato', '2023-11-07', '0000-00-00', 'Planilla', 'Vencido', 1800.00, 'BCP', 'Sueldo', '19300202613059', '', '', 'Administrativo', 'Recursos Humanos', 'Asistente', 0, 2),
(3, 'Contrato', '2024-04-03', '0000-00-00', 'Planilla', 'Vencido', 2500.00, 'BCP', 'Sueldo', '19199848053016', '', '', 'Operaciones', 'Técnico Regulatorio', 'Asociado Ejecutivo', 0, 3),
(4, 'Contrato', '2024-07-04', '0000-00-00', 'Planilla', 'Vencido', 2500.00, 'BCP', 'Sueldo', '19195006197053', '', '', 'Operaciones', 'Legal Regulatorio', 'Asociado Ejecutivo', 0, 4),
(5, 'Contrato', '2025-02-11', '0000-00-00', 'Planilla', 'Vigente', 2500.00, 'IBK', 'Sueldo', '00389801345860304042', '', '', 'Operaciones', 'Legal Regulatorio', 'Asociado Ejecutivo', 0, 5),
(6, 'Contrato', '2025-02-17', '0000-00-00', 'Planilla', 'Vigente', 3000.00, 'BCP', 'Sueldo', '19105393963046', '', '', 'Operaciones', 'Legal Regulatorio', 'Asociado Ejecutivo', 0, 6),
(7, 'Contrato', '2025-02-26', '0000-00-00', 'Planilla', 'Vigente', 2800.00, 'SCTBK', 'Sueldo', '00914220952002488718', '', '', 'Operaciones', 'Técnico Regulatorio', 'Asociado Ejecutivo', 0, 7),
(8, 'Contrato', '2015-04-08', '0000-00-00', 'Recibo por Honorarios', '', 8913.04, 'BCP', 'Ahorros', '19199020334038', '', 'Sueldo neto original 8.2k', 'Socios', 'Legal Regulatorio', 'Socio Fundador', 0, 8),
(9, 'Contrato', '2015-04-08', '0000-00-00', 'Recibo por Honorarios', '', 8913.04, 'IBK', 'Ahorros', '00336801309344242488', '', 'Sueldo neto original 8.2k', 'Socios', 'Técnico Regulatorio', 'Socio Fundador', 0, 9),
(10, 'Contrato', '0000-00-00', '0000-00-00', '', '', 500.00, 'BCP', 'Ahorros', '19431468626089', '', '', 'Administrativo', 'Contabilidad', 'Locador de Servicios', 0, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuotahito`
--

CREATE TABLE `cuotahito` (
  `idcouta` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `hito` varchar(500) NOT NULL,
  `avance` varchar(500) NOT NULL,
  `cuota` decimal(7,2) NOT NULL,
  `idpresupuesto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle`
--

CREATE TABLE `detalle` (
  `idetalle` int(11) NOT NULL,
  `idfacturacion` int(11) NOT NULL,
  `tiposervicio` varchar(50) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(7,2) NOT NULL,
  `importe` decimal(7,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_planificacion`
--

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

--
-- Volcado de datos para la tabla `detalles_planificacion`
--

INSERT INTO `detalles_planificacion` (`iddetalle`, `Idplanificacion`, `idliquidacion`, `fechaliquidacion`, `estado`, `cantidahoras`, `registrado`, `modificado`) VALUES
(1, 1, 4, '2025-05-02', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(2, 1, 5, '2025-05-13', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(3, 1, 6, '2025-05-30', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(4, 2, 12, '2025-05-06', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(5, 2, 15, '2025-05-09', 'Completo', 3, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(6, 2, 16, '2025-05-09', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(7, 2, 17, '2025-05-09', 'Completo', 4, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(8, 2, 18, '2025-05-13', 'Completo', 9, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(9, 2, 19, '2025-05-16', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(10, 2, 21, '2025-05-23', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(11, 2, 23, '2025-05-28', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(12, 2, 26, '2025-05-30', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(13, 3, 13, '2025-05-07', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(14, 3, 14, '2025-05-09', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(15, 3, 20, '2025-05-23', 'Completo', 4, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(16, 3, 22, '2025-05-26', 'Completo', 3, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(17, 3, 25, '2025-05-30', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(18, 4, 7, '2025-05-16', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(19, 4, 8, '2025-05-23', 'Completo', 5, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(20, 4, 9, '2025-05-27', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(21, 4, 10, '2025-05-29', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(22, 4, 11, '2025-05-29', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(23, 5, 32, '2025-07-02', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(24, 5, 37, '2025-07-07', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(25, 5, 46, '2025-07-07', 'Completo', 4, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(26, 5, 53, '2025-07-08', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(27, 5, 54, '2025-07-09', 'Completo', 3, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(28, 5, 57, '2025-07-08', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(29, 5, 64, '2025-07-10', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(30, 5, 70, '2025-07-31', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-31 20:46:08'),
(31, 5, 73, '2025-07-24', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-30 17:27:27'),
(32, 5, 74, '2025-07-24', 'Completo', 4, '2025-07-18 12:15:54', '2025-07-24 18:57:47'),
(33, 6, 47, '2025-07-24', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-24 19:01:36'),
(34, 6, 48, '2025-07-16', 'Completo', 3, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(35, 6, 49, '2025-07-09', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(36, 6, 55, '2025-07-31', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-31 20:51:53'),
(37, 15, 69, '2025-08-05', 'Completo', 3, '2025-07-18 12:15:54', '2025-08-13 22:37:01'),
(38, 6, 72, '2025-07-18', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-19 06:46:21'),
(39, 7, 38, '2025-07-07', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(40, 7, 68, '2025-07-15', 'Completo', 4, '2025-07-18 12:15:54', '2025-07-21 22:24:14'),
(41, 7, 71, '2025-07-21', 'Completo', 3, '2025-07-18 12:15:54', '2025-07-30 19:06:17'),
(42, 8, 36, '2025-07-03', 'Completo', 4, '2025-07-18 12:15:54', '2025-07-25 21:52:41'),
(43, 8, 44, '2025-07-08', 'Completo', 4, '2025-07-18 12:15:54', '2025-07-31 15:20:44'),
(44, 8, 67, '2025-07-18', 'Completo', 4, '2025-07-18 12:15:54', '2025-07-25 21:51:53'),
(45, 9, 66, '2025-07-11', 'Completo', 3, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(46, 10, 24, '2025-07-04', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(47, 10, 30, '2025-07-02', 'Completo', 4, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(48, 10, 40, '2025-07-04', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(49, 10, 42, '2025-07-31', 'Completo', 3, '2025-07-18 12:15:54', '2025-07-31 21:22:47'),
(50, 10, 58, '2025-07-10', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(51, 10, 59, '2025-07-10', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(52, 10, 62, '2025-07-11', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(53, 10, 63, '2025-07-25', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-25 19:33:36'),
(54, 10, 65, '2025-07-11', 'Completo', 3, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(55, 11, 33, '2025-07-02', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-25 21:53:44'),
(56, 11, 35, '2025-07-02', 'Completo', 3, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(57, 11, 39, '2025-07-04', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(58, 11, 45, '2025-07-17', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-31 23:21:07'),
(59, 11, 52, '2025-07-07', 'Completo', 4, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(60, 11, 60, '2025-07-09', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-25 21:55:26'),
(61, 11, 61, '2025-07-09', 'Completo', 1, '2025-07-18 12:15:54', '2025-07-25 21:56:10'),
(62, 12, 31, '2025-07-02', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(63, 12, 41, '2025-07-03', 'Completo', 2, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(64, 12, 75, '2025-07-25', 'Completo', 4, '2025-07-18 12:15:54', '2025-07-25 23:43:23'),
(65, 13, 43, '2025-07-08', 'Completo', 3, '2025-07-18 12:15:54', '2025-08-01 17:34:07'),
(66, 13, 51, '2025-07-08', 'Completo', 3, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(67, 13, 56, '2025-07-08', 'Completo', 3, '2025-07-18 12:15:54', '2025-07-18 12:15:54'),
(128, 17, 76, '2025-08-15', 'En revisión', 5, '2025-07-19 06:42:50', '2025-08-16 01:17:29'),
(129, 10, 78, '2025-07-21', 'Completo', 4, '2025-07-21 13:29:30', '2025-07-25 19:19:16'),
(130, 10, 79, '2025-07-22', 'Completo', 4, '2025-07-21 13:31:19', '2025-07-25 19:22:20'),
(131, 10, 80, '2025-07-25', 'Completo', 2, '2025-07-21 13:47:22', '2025-07-31 21:01:08'),
(132, 16, 81, '2025-08-05', 'En proceso', 1, '2025-07-21 13:56:23', '2025-08-13 22:37:01'),
(133, 10, 82, '2025-07-18', 'Completo', 1, '2025-07-21 14:00:25', '2025-07-21 14:00:25'),
(134, 11, 83, '2025-07-18', 'Completo', 4, '2025-07-21 14:08:01', '2025-07-21 15:41:29'),
(135, 8, 84, '2025-07-21', 'Completo', 1, '2025-07-21 14:30:31', '2025-07-22 15:39:14'),
(136, 6, 85, '2025-07-18', 'Completo', 2, '2025-07-21 15:23:40', '2025-07-21 15:23:40'),
(137, 7, 86, '2025-08-01', 'Programado', 1, '2025-07-21 18:10:33', '2025-08-08 22:43:51'),
(138, 8, 87, '2025-07-22', 'Completo', 1, '2025-07-22 15:38:29', '2025-07-31 21:55:15'),
(139, 14, 77, '2025-07-21', 'Completo', 3, '2025-07-23 00:50:01', '2025-08-01 18:52:25'),
(140, 5, 88, '2025-07-24', 'Completo', 2, '2025-07-24 03:25:18', '2025-07-24 18:59:26'),
(141, 7, 89, '2025-07-30', 'Completo', 3, '2025-07-24 03:29:08', '2025-07-31 21:04:39'),
(142, 11, 90, '2025-07-24', 'Completo', 1, '2025-07-24 16:57:02', '2025-07-24 16:57:02'),
(143, 13, 91, '2025-07-22', 'Completo', 3, '2025-07-25 20:02:37', '2025-07-25 20:02:37'),
(144, 8, 93, '2025-07-31', 'Completo', 3, '2025-07-31 15:13:13', '2025-08-01 18:36:17'),
(145, 10, 94, '2025-08-08', 'Completo', 4, '2025-07-31 15:16:09', '2025-08-11 17:08:46'),
(146, 10, 95, '2025-07-31', 'Completo', 2, '2025-07-31 15:17:26', '2025-07-31 15:17:26'),
(147, 12, 97, '2025-07-31', 'Completo', 1, '2025-07-31 22:59:43', '2025-08-01 17:49:02'),
(148, 8, 98, '2025-07-30', 'Completo', 1, '2025-07-31 23:19:21', '2025-08-01 17:45:51'),
(149, 14, 100, '2025-07-31', 'Completo', 1, '2025-08-01 18:40:51', '2025-08-01 18:40:51'),
(150, 15, 99, '2025-08-01', 'Completo', 3, '2025-08-08 16:18:55', '2025-08-08 16:18:55'),
(151, 15, 109, '2025-08-07', 'Completo', 1, '2025-08-08 21:43:26', '2025-08-08 21:43:26'),
(152, 15, 112, '2025-08-12', 'En proceso', 1, '2025-08-13 15:25:10', '2025-08-15 21:27:47'),
(153, 15, 113, '2025-08-12', 'Completo', 2, '2025-08-13 15:25:14', '2025-08-15 21:27:21'),
(154, 16, 114, '2025-08-13', 'Completo', 1, '2025-08-13 16:54:27', '2025-08-13 16:54:27'),
(155, 16, 101, '2025-08-31', 'En proceso', 2, '2025-08-13 22:37:01', '2025-08-16 00:09:04'),
(156, 16, 102, '2025-08-05', 'En proceso', 1, '2025-08-13 22:37:01', '2025-08-13 22:37:01'),
(157, 16, 103, '2025-08-07', 'Completo', 3, '2025-08-13 22:37:01', '2025-08-13 22:37:01'),
(158, 16, 104, '2025-08-05', 'Completo', 4, '2025-08-13 22:37:01', '2025-08-13 22:37:01'),
(159, 16, 108, '2025-08-08', 'Completo', 5, '2025-08-13 22:37:01', '2025-08-13 22:37:01'),
(160, 16, 111, '2025-08-12', 'Completo', 3, '2025-08-13 22:37:01', '2025-08-13 22:37:01'),
(161, 18, 105, '2025-08-04', 'Completo', 4, '2025-08-13 22:37:01', '2025-08-13 22:37:01'),
(162, 20, 110, '2025-08-11', 'Completo', 1, '2025-08-13 22:37:01', '2025-08-13 22:37:01'),
(163, 23, 107, '2025-08-07', 'Completo', 4, '2025-08-13 22:37:01', '2025-08-13 22:37:01'),
(170, 15, 115, '2025-08-14', 'Completo', 1, '2025-08-14 17:30:50', '2025-08-14 17:30:50'),
(171, 20, 116, '2025-08-15', 'Completo', 1, '2025-08-15 22:45:18', '2025-08-16 06:31:12'),
(172, 19, 117, '2025-08-15', 'Completo', 2, '2025-08-15 23:57:31', '2025-08-15 23:57:31'),
(173, 19, 118, '2025-08-15', 'Completo', 1, '2025-08-16 00:05:19', '2025-08-16 00:07:21'),
(174, 17, 119, '2025-08-15', 'Completo', 1, '2025-08-16 01:24:51', '2025-08-16 01:25:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `distribucionhora`
--

CREATE TABLE `distribucionhora` (
  `id` int(11) NOT NULL,
  `participante` int(11) NOT NULL,
  `porcentaje` int(11) NOT NULL,
  `comentario` varchar(500) NOT NULL,
  `idliquidacion` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `horas` int(11) NOT NULL,
  `calculo` decimal(10,2) DEFAULT NULL,
  `registrado` timestamp NOT NULL DEFAULT current_timestamp(),
  `modificado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `distribucionhora`
--

INSERT INTO `distribucionhora` (`id`, `participante`, `porcentaje`, `comentario`, `idliquidacion`, `fecha`, `horas`, `calculo`, `registrado`, `modificado`) VALUES
(4, 4, 100, '', 4, '2025-05-02 23:55:00', 2, 2.00, '2025-06-18 04:56:07', '2025-06-18 04:56:07'),
(5, 4, 100, '', 5, '2025-05-13 23:56:00', 2, 2.00, '2025-06-18 04:57:14', '2025-06-18 04:57:14'),
(6, 4, 90, '', 6, '2025-05-30 23:58:00', 1, 0.90, '2025-06-18 04:59:56', '2025-06-18 04:59:56'),
(7, 8, 10, '', 6, '2025-05-30 23:58:00', 1, 0.10, '2025-06-18 04:59:56', '2025-06-18 04:59:56'),
(8, 3, 10, '', 7, '2025-05-16 00:00:00', 1, 0.10, '2025-06-18 05:01:13', '2025-06-18 05:01:13'),
(9, 6, 10, '', 7, '2025-05-16 00:00:00', 1, 0.10, '2025-06-18 05:01:13', '2025-06-18 05:01:13'),
(10, 8, 80, '', 7, '2025-05-16 00:00:00', 1, 0.80, '2025-06-18 05:01:13', '2025-06-18 05:01:13'),
(11, 3, 50, '', 8, '2025-05-23 00:01:00', 5, 2.50, '2025-06-18 05:03:00', '2025-06-18 05:03:00'),
(12, 8, 50, '', 8, '2025-05-23 00:01:00', 5, 2.50, '2025-06-18 05:03:00', '2025-06-18 05:03:00'),
(13, 3, 80, '', 9, '2025-05-27 00:03:00', 2, 1.60, '2025-06-18 05:04:20', '2025-06-18 05:04:20'),
(14, 8, 20, '', 9, '2025-05-27 00:03:00', 2, 0.40, '2025-06-18 05:04:20', '2025-06-18 05:04:20'),
(15, 8, 100, '', 10, '2025-05-29 00:05:00', 1, 1.00, '2025-06-18 05:06:32', '2025-06-18 05:06:32'),
(16, 3, 10, '', 11, '2025-05-29 00:06:00', 1, 0.10, '2025-06-18 05:07:41', '2025-06-18 05:07:41'),
(17, 8, 90, '', 11, '2025-05-29 00:06:00', 1, 0.90, '2025-06-18 05:07:41', '2025-06-18 05:07:41'),
(18, 3, 20, '', 12, '2025-05-06 00:08:00', 1, 0.20, '2025-06-18 05:09:08', '2025-06-18 05:09:08'),
(19, 6, 20, '', 12, '2025-05-06 00:08:00', 1, 0.20, '2025-06-18 05:09:08', '2025-06-18 05:09:08'),
(20, 8, 60, '', 12, '2025-05-06 00:08:00', 1, 0.60, '2025-06-18 05:09:08', '2025-06-18 05:09:08'),
(24, 8, 100, '', 14, '2025-05-09 00:00:00', 1, 1.00, '2025-07-02 15:39:44', '2025-07-02 15:39:44'),
(25, 3, 80, '', 15, '2025-05-09 10:40:00', 3, 2.40, '2025-07-02 15:41:45', '2025-07-02 15:41:45'),
(26, 8, 20, '', 15, '2025-05-09 10:40:00', 3, 0.60, '2025-07-02 15:41:45', '2025-07-02 15:41:45'),
(27, 3, 10, '', 16, '2025-05-09 10:42:00', 2, 0.20, '2025-07-02 15:43:31', '2025-07-02 15:43:31'),
(28, 6, 80, '', 16, '2025-05-09 10:42:00', 2, 1.60, '2025-07-02 15:43:31', '2025-07-02 15:43:31'),
(29, 8, 10, '', 16, '2025-05-09 10:42:00', 2, 0.20, '2025-07-02 15:43:31', '2025-07-02 15:43:31'),
(30, 3, 50, '', 17, '2025-05-09 10:43:00', 4, 2.00, '2025-07-02 15:44:51', '2025-07-02 15:44:51'),
(31, 6, 30, '', 17, '2025-05-09 10:43:00', 4, 1.20, '2025-07-02 15:44:51', '2025-07-02 15:44:51'),
(32, 8, 20, '', 17, '2025-05-09 10:43:00', 4, 0.80, '2025-07-02 15:44:51', '2025-07-02 15:44:51'),
(33, 3, 40, '', 18, '2025-05-13 10:53:00', 9, 3.60, '2025-07-02 15:54:03', '2025-07-02 15:54:03'),
(34, 8, 60, '', 18, '2025-05-13 10:53:00', 9, 5.40, '2025-07-02 15:54:03', '2025-07-02 15:54:03'),
(35, 3, 40, '', 19, '2025-05-16 10:55:00', 2, 0.80, '2025-07-02 15:56:16', '2025-07-02 15:56:16'),
(36, 6, 60, '', 19, '2025-05-16 10:55:00', 2, 1.20, '2025-07-02 15:56:16', '2025-07-02 15:56:16'),
(37, 3, 20, '', 20, '2025-05-23 10:56:00', 4, 0.80, '2025-07-02 15:57:49', '2025-07-02 15:57:49'),
(38, 6, 60, '', 20, '2025-05-23 10:56:00', 4, 2.40, '2025-07-02 15:57:49', '2025-07-02 15:57:49'),
(39, 8, 20, '', 20, '2025-05-23 10:56:00', 4, 0.80, '2025-07-02 15:57:49', '2025-07-02 15:57:49'),
(40, 3, 40, '', 21, '2025-05-23 10:58:00', 1, 0.40, '2025-07-02 16:00:09', '2025-07-02 16:00:09'),
(41, 8, 60, '', 21, '2025-05-23 10:58:00', 1, 0.60, '2025-07-02 16:00:09', '2025-07-02 16:00:09'),
(42, 3, 30, '', 22, '2025-05-26 11:01:00', 3, 0.90, '2025-07-02 16:02:16', '2025-07-02 16:02:16'),
(43, 6, 70, '', 22, '2025-05-26 11:01:00', 3, 2.10, '2025-07-02 16:02:16', '2025-07-02 16:02:16'),
(44, 4, 90, '', 23, '2025-05-28 11:03:00', 2, 1.80, '2025-07-02 16:03:33', '2025-07-02 16:03:33'),
(45, 8, 10, '', 23, '2025-05-28 11:03:00', 2, 0.20, '2025-07-02 16:03:33', '2025-07-02 16:03:33'),
(48, 6, 90, '', 25, '2025-05-30 11:05:00', 2, 1.80, '2025-07-02 16:05:54', '2025-07-02 16:05:54'),
(49, 8, 10, '', 25, '2025-05-30 11:05:00', 2, 0.20, '2025-07-02 16:05:54', '2025-07-02 16:05:54'),
(50, 3, 50, '', 26, '2025-05-30 11:06:00', 2, 1.00, '2025-07-02 16:07:30', '2025-07-02 16:07:30'),
(51, 8, 50, '', 26, '2025-05-30 11:06:00', 2, 1.00, '2025-07-02 16:07:30', '2025-07-02 16:07:30'),
(57, 4, 95, 'Elaboración de análisis, revisión de documentos y elaboración de correo de respuesta', 31, '2025-07-02 00:00:00', 2, 1.90, '2025-07-03 00:49:53', '2025-07-03 00:49:53'),
(58, 8, 5, 'Apoyo con la estructuración de correo', 31, '2025-07-02 00:00:00', 2, 0.10, '2025-07-03 00:49:53', '2025-07-03 00:49:53'),
(59, 4, 25, 'Efectuó coordinaciones previas, tuvo participaciones en la reunión y en la toma de acuerdos', 32, '2025-07-02 17:00:00', 1, 0.25, '2025-07-03 01:21:50', '2025-07-03 01:21:50'),
(60, 8, 75, 'Dirigieron reunión y tuvieron participación activa en toda la reunión', 32, '2025-07-02 17:00:00', 1, 0.75, '2025-07-03 01:21:50', '2025-07-03 01:21:50'),
(78, 6, 70, 'Desarrollo del tema general', 35, '2025-07-02 00:00:00', 3, 2.10, '2025-07-03 18:48:27', '2025-07-03 18:48:27'),
(79, 3, 25, 'Soporte en la revisión de la matriz y elaboración de correo para trasladar los hallazgos', 35, '2025-07-02 00:00:00', 3, 0.75, '2025-07-03 18:48:27', '2025-07-03 18:48:27'),
(80, 8, 5, 'Guía para desarrollar el tema y revisión sin cambios', 35, '2025-07-02 00:00:00', 3, 0.15, '2025-07-03 18:48:27', '2025-07-03 18:48:27'),
(84, 3, 10, '', 13, '2025-05-07 00:00:00', 1, 0.10, '2025-07-04 07:55:46', '2025-07-04 07:55:46'),
(85, 6, 20, '', 13, '2025-05-07 00:00:00', 1, 0.20, '2025-07-04 07:55:46', '2025-07-04 07:55:46'),
(86, 8, 70, '', 13, '2025-05-07 00:00:00', 1, 0.70, '2025-07-04 07:55:46', '2025-07-04 07:55:46'),
(87, 6, 35, '', 39, '2025-07-04 02:30:00', 1, 0.35, '2025-07-04 21:08:56', '2025-07-04 21:08:56'),
(88, 4, 60, '', 39, '2025-07-04 02:30:00', 1, 0.60, '2025-07-04 21:08:56', '2025-07-04 21:08:56'),
(89, 3, 5, '', 39, '2025-07-04 02:30:00', 1, 0.05, '2025-07-04 21:08:56', '2025-07-04 21:08:56'),
(90, 3, 10, '', 24, '2025-07-04 10:00:00', 2, 0.20, '2025-07-04 21:11:27', '2025-07-04 21:11:27'),
(91, 6, 10, '', 24, '2025-07-04 10:00:00', 2, 0.20, '2025-07-04 21:11:27', '2025-07-04 21:11:27'),
(92, 8, 80, '', 24, '2025-07-04 10:00:00', 2, 1.60, '2025-07-04 21:11:27', '2025-07-04 21:11:27'),
(93, 3, 80, '', 30, '2025-07-02 00:00:00', 4, 3.20, '2025-07-04 21:17:01', '2025-07-04 21:17:01'),
(94, 8, 20, '', 30, '2025-07-02 00:00:00', 4, 0.80, '2025-07-04 21:17:01', '2025-07-04 21:17:01'),
(97, 3, 95, 'Revisión de normativa/ antecedentes de la NRIP / elaboración de correo', 40, '2025-07-04 16:22:00', 1, 0.95, '2025-07-04 21:31:13', '2025-07-04 21:31:13'),
(98, 8, 5, 'Recomendación PUNKU / lectura y aprobación del correo', 40, '2025-07-04 16:22:00', 1, 0.05, '2025-07-04 21:31:13', '2025-07-04 21:31:13'),
(103, 3, 50, '', 41, '2025-07-03 00:00:00', 2, 1.00, '2025-07-07 16:37:26', '2025-07-07 16:37:26'),
(104, 5, 50, '', 41, '2025-07-03 00:00:00', 2, 1.00, '2025-07-07 16:37:26', '2025-07-07 16:37:26'),
(105, 3, 100, '', 51, '2025-07-08 00:00:00', 3, 3.00, '2025-07-08 19:34:33', '2025-07-08 19:34:33'),
(106, 4, 35, '', 46, '2025-07-07 00:00:00', 4, 1.40, '2025-07-08 19:38:29', '2025-07-08 19:38:29'),
(107, 8, 65, '', 46, '2025-07-07 00:00:00', 4, 2.60, '2025-07-08 19:38:29', '2025-07-08 19:38:29'),
(110, 4, 10, '', 37, '2025-07-07 15:00:00', 1, 0.10, '2025-07-08 19:43:57', '2025-07-08 19:43:57'),
(111, 8, 90, '', 37, '2025-07-07 15:00:00', 1, 0.90, '2025-07-08 19:43:57', '2025-07-08 19:43:57'),
(112, 4, 10, '', 38, '2025-07-07 16:30:00', 1, 0.10, '2025-07-08 19:44:56', '2025-07-08 19:44:56'),
(113, 8, 90, '', 38, '2025-07-07 16:30:00', 1, 0.90, '2025-07-08 19:44:56', '2025-07-08 19:44:56'),
(114, 4, 50, '', 53, '2025-07-08 00:00:00', 2, 1.00, '2025-07-08 19:46:24', '2025-07-08 19:46:24'),
(115, 8, 50, '', 53, '2025-07-08 00:00:00', 2, 1.00, '2025-07-08 19:46:24', '2025-07-08 19:46:24'),
(116, 5, 100, '', 56, '2025-07-08 11:46:00', 3, 3.00, '2025-07-08 21:50:17', '2025-07-08 21:50:17'),
(117, 3, 20, '', 52, '2025-07-07 00:00:00', 4, 0.80, '2025-07-08 22:07:36', '2025-07-08 22:07:36'),
(118, 4, 80, '', 52, '2025-07-07 00:00:00', 4, 3.20, '2025-07-08 22:07:36', '2025-07-08 22:07:36'),
(119, 4, 50, '', 57, '2025-07-08 18:00:00', 1, 0.50, '2025-07-08 23:46:24', '2025-07-08 23:46:24'),
(120, 3, 50, '', 57, '2025-07-08 18:00:00', 1, 0.50, '2025-07-08 23:46:24', '2025-07-08 23:46:24'),
(121, 8, 5, '', 49, '2025-07-09 12:46:00', 2, 0.10, '2025-07-09 21:56:01', '2025-07-09 21:56:01'),
(122, 4, 95, '', 49, '2025-07-09 12:46:00', 2, 1.90, '2025-07-09 21:56:01', '2025-07-09 21:56:01'),
(123, 8, 100, '', 54, '2025-07-09 19:11:00', 3, 3.00, '2025-07-10 00:22:39', '2025-07-10 00:22:39'),
(124, 3, 80, 'Desarrollo de PPTS', 58, '2025-07-10 10:00:00', 2, 1.60, '2025-07-10 17:29:16', '2025-07-10 17:29:16'),
(125, 8, 20, 'Inclusión y análisis de escenarios de SMS A2P / Adecuación de títulos de la PPT', 58, '2025-07-10 10:00:00', 2, 0.40, '2025-07-10 17:29:16', '2025-07-10 17:29:16'),
(126, 3, 40, 'Presentación y detalle de escenarios', 59, '2025-07-10 10:00:00', 2, 0.80, '2025-07-10 17:50:30', '2025-07-10 17:50:30'),
(127, 8, 50, 'Presentación y detalle de escenarios', 59, '2025-07-10 10:00:00', 2, 1.00, '2025-07-10 17:50:30', '2025-07-10 17:50:30'),
(128, 6, 10, 'Acta', 59, '2025-07-10 10:00:00', 2, 0.20, '2025-07-10 17:50:30', '2025-07-10 17:50:30'),
(152, 3, 15, '', 64, '2025-07-10 15:47:00', 2, 0.30, '2025-07-10 23:15:02', '2025-07-10 23:15:02'),
(153, 4, 85, '', 64, '2025-07-10 15:47:00', 2, 1.70, '2025-07-10 23:15:02', '2025-07-10 23:15:02'),
(154, 3, 20, 'Revisión de normativa y mandato', 62, '2025-07-11 09:07:00', 1, 0.20, '2025-07-11 14:07:42', '2025-07-11 14:07:42'),
(155, 8, 80, 'Elaboración y revisión del correo', 62, '2025-07-11 09:07:00', 1, 0.80, '2025-07-11 14:07:42', '2025-07-11 14:07:42'),
(163, 6, 80, '', 66, '2025-07-11 03:00:00', 3, 2.40, '2025-07-11 22:59:16', '2025-07-11 22:59:16'),
(164, 8, 20, 'Revisión del escrito y orientación.', 66, '2025-07-11 03:00:00', 3, 0.60, '2025-07-11 22:59:16', '2025-07-11 22:59:16'),
(167, 3, 70, '', 65, '2025-07-11 19:45:00', 3, 2.10, '2025-07-12 00:46:04', '2025-07-12 00:46:04'),
(168, 8, 30, '', 65, '2025-07-11 19:45:00', 3, 0.90, '2025-07-12 00:46:04', '2025-07-12 00:46:04'),
(181, 8, 10, '', 48, '2025-07-16 00:00:00', 3, 0.30, '2025-07-17 00:05:21', '2025-07-17 00:05:21'),
(182, 4, 90, '', 48, '2025-07-16 00:00:00', 3, 2.70, '2025-07-17 00:05:21', '2025-07-17 00:05:21'),
(192, 4, 15, '', 72, '2025-07-18 11:30:00', 1, 0.15, '2025-07-19 06:46:21', '2025-07-19 06:46:21'),
(193, 8, 35, '', 72, '2025-07-18 11:30:00', 1, 0.35, '2025-07-19 06:46:21', '2025-07-19 06:46:21'),
(194, 3, 50, '', 72, '2025-07-18 11:30:00', 1, 0.50, '2025-07-19 06:46:21', '2025-07-19 06:46:21'),
(195, 8, 95, '', 82, '2025-07-18 10:00:00', 1, 0.95, '2025-07-21 14:00:25', '2025-07-21 14:00:25'),
(196, 3, 5, '', 82, '2025-07-18 10:00:00', 1, 0.05, '2025-07-21 14:00:25', '2025-07-21 14:00:25'),
(197, 4, 10, '', 85, '2025-07-18 12:00:00', 2, 0.20, '2025-07-21 15:23:40', '2025-07-21 15:23:40'),
(198, 8, 10, '', 85, '2025-07-18 12:00:00', 2, 0.20, '2025-07-21 15:23:40', '2025-07-21 15:23:40'),
(199, 3, 80, '', 85, '2025-07-18 12:00:00', 2, 1.60, '2025-07-21 15:23:40', '2025-07-21 15:23:40'),
(200, 3, 50, '', 83, '2025-07-18 05:30:00', 4, 2.00, '2025-07-21 15:41:29', '2025-07-21 15:41:29'),
(201, 4, 50, '', 83, '2025-07-18 05:30:00', 4, 2.00, '2025-07-21 15:41:29', '2025-07-21 15:41:29'),
(206, 8, 10, '', 68, '2025-07-15 00:00:00', 4, 0.40, '2025-07-21 22:24:14', '2025-07-21 22:24:14'),
(207, 4, 80, '', 68, '2025-07-15 00:00:00', 4, 3.20, '2025-07-21 22:24:14', '2025-07-21 22:24:14'),
(208, 5, 10, '', 68, '2025-07-15 00:00:00', 4, 0.40, '2025-07-21 22:24:14', '2025-07-21 22:24:14'),
(211, 6, 60, '', 84, '2025-07-21 00:00:00', 1, 0.60, '2025-07-22 15:39:14', '2025-07-22 15:39:14'),
(212, 8, 40, '', 84, '2025-07-21 00:00:00', 1, 0.40, '2025-07-22 15:39:14', '2025-07-22 15:39:14'),
(213, 4, 50, '', 90, '2025-07-24 11:30:00', 1, 0.50, '2025-07-24 16:57:02', '2025-07-24 16:57:02'),
(214, 3, 50, '', 90, '2025-07-24 11:30:00', 1, 0.50, '2025-07-24 16:57:02', '2025-07-24 16:57:02'),
(215, 8, 50, '', 74, '2025-07-24 00:00:00', 4, 2.00, '2025-07-24 18:57:47', '2025-07-24 18:57:47'),
(216, 4, 50, '', 74, '2025-07-24 00:00:00', 4, 2.00, '2025-07-24 18:57:47', '2025-07-24 18:57:47'),
(217, 3, 10, '', 88, '2025-07-24 00:00:00', 2, 0.20, '2025-07-24 18:59:26', '2025-07-24 18:59:26'),
(218, 4, 90, '', 88, '2025-07-24 00:00:00', 2, 1.80, '2025-07-24 18:59:26', '2025-07-24 18:59:26'),
(221, 5, 20, '', 47, '2025-07-24 00:00:00', 2, 0.40, '2025-07-24 19:01:36', '2025-07-24 19:01:36'),
(222, 4, 80, '', 47, '2025-07-24 00:00:00', 2, 1.60, '2025-07-24 19:01:36', '2025-07-24 19:01:36'),
(227, 3, 60, '', 78, '2025-07-21 00:00:00', 4, 2.40, '2025-07-25 19:19:16', '2025-07-25 19:19:16'),
(228, 9, 40, '', 78, '2025-07-21 00:00:00', 4, 1.60, '2025-07-25 19:19:16', '2025-07-25 19:19:16'),
(229, 4, 80, '', 79, '2025-07-22 00:00:00', 4, 3.20, '2025-07-25 19:22:20', '2025-07-25 19:22:20'),
(230, 3, 10, '', 79, '2025-07-22 00:00:00', 4, 0.40, '2025-07-25 19:22:20', '2025-07-25 19:22:20'),
(231, 9, 10, '', 79, '2025-07-22 00:00:00', 4, 0.40, '2025-07-25 19:22:20', '2025-07-25 19:22:20'),
(232, 3, 100, '', 91, '2025-07-22 12:00:00', 3, 3.00, '2025-07-25 19:25:35', '2025-07-25 19:25:35'),
(236, 3, 70, '', 63, '2025-07-25 12:00:00', 2, 1.40, '2025-07-25 19:33:36', '2025-07-25 19:33:36'),
(237, 8, 30, '', 63, '2025-07-25 12:00:00', 2, 0.60, '2025-07-25 19:33:36', '2025-07-25 19:33:36'),
(240, 6, 95, '', 67, '2025-07-18 00:00:00', 4, 3.80, '2025-07-25 21:51:53', '2025-07-25 21:51:53'),
(241, 8, 5, 'Guía para abordar el tema.', 67, '2025-07-18 00:00:00', 4, 0.20, '2025-07-25 21:51:53', '2025-07-25 21:51:53'),
(242, 6, 80, '', 36, '2025-07-03 00:00:00', 4, 3.20, '2025-07-25 21:52:41', '2025-07-25 21:52:41'),
(243, 8, 20, '', 36, '2025-07-03 00:00:00', 4, 0.80, '2025-07-25 21:52:41', '2025-07-25 21:52:41'),
(244, 6, 30, '', 33, '2025-07-02 00:00:00', 1, 0.30, '2025-07-25 21:53:44', '2025-07-25 21:53:44'),
(245, 4, 50, '', 33, '2025-07-02 00:00:00', 1, 0.50, '2025-07-25 21:53:44', '2025-07-25 21:53:44'),
(246, 3, 20, '', 33, '2025-07-02 00:00:00', 1, 0.20, '2025-07-25 21:53:44', '2025-07-25 21:53:44'),
(247, 6, 90, '', 60, '2025-07-09 00:00:00', 1, 0.90, '2025-07-25 21:55:26', '2025-07-25 21:55:26'),
(248, 8, 10, '', 60, '2025-07-09 00:00:00', 1, 0.10, '2025-07-25 21:55:26', '2025-07-25 21:55:26'),
(249, 8, 75, '', 61, '2025-07-09 00:00:00', 1, 0.75, '2025-07-25 21:56:10', '2025-07-25 21:56:10'),
(250, 6, 20, '', 61, '2025-07-09 00:00:00', 1, 0.20, '2025-07-25 21:56:10', '2025-07-25 21:56:10'),
(251, 3, 5, '', 61, '2025-07-09 00:00:00', 1, 0.05, '2025-07-25 21:56:10', '2025-07-25 21:56:10'),
(252, 3, 50, '', 75, '2025-07-25 00:00:00', 4, 2.00, '2025-07-25 23:43:23', '2025-07-25 23:43:23'),
(253, 6, 20, '', 75, '2025-07-25 00:00:00', 4, 0.80, '2025-07-25 23:43:23', '2025-07-25 23:43:23'),
(254, 9, 30, '', 75, '2025-07-25 00:00:00', 4, 1.20, '2025-07-25 23:43:23', '2025-07-25 23:43:23'),
(257, 8, 10, '', 73, '2025-07-24 00:00:00', 2, 0.20, '2025-07-30 17:27:27', '2025-07-30 17:27:27'),
(258, 4, 90, '', 73, '2025-07-24 00:00:00', 2, 1.80, '2025-07-30 17:27:27', '2025-07-30 17:27:27'),
(259, 5, 30, '', 71, '2025-07-21 00:00:00', 3, 0.90, '2025-07-30 19:06:17', '2025-07-30 19:06:17'),
(260, 8, 20, '', 71, '2025-07-21 00:00:00', 3, 0.60, '2025-07-30 19:06:17', '2025-07-30 19:06:17'),
(261, 4, 50, '', 71, '2025-07-21 00:00:00', 3, 1.50, '2025-07-30 19:06:17', '2025-07-30 19:06:17'),
(262, 6, 100, '', 92, '2025-07-31 11:00:00', 1, 1.00, '2025-07-31 15:11:37', '2025-07-31 15:11:37'),
(265, 9, 30, '', 95, '2025-07-31 17:30:00', 2, 0.60, '2025-07-31 15:17:26', '2025-07-31 15:17:26'),
(266, 4, 70, '', 95, '2025-07-31 17:30:00', 2, 1.40, '2025-07-31 15:17:26', '2025-07-31 15:17:26'),
(267, 6, 70, '', 44, '2025-07-08 00:00:00', 4, 2.80, '2025-07-31 15:20:44', '2025-07-31 15:20:44'),
(268, 8, 30, '', 44, '2025-07-08 00:00:00', 4, 1.20, '2025-07-31 15:20:44', '2025-07-31 15:20:44'),
(269, 5, 25, '', 70, '2025-07-31 00:00:00', 2, 0.50, '2025-07-31 20:46:08', '2025-07-31 20:46:08'),
(270, 3, 25, '', 70, '2025-07-31 00:00:00', 2, 0.50, '2025-07-31 20:46:08', '2025-07-31 20:46:08'),
(271, 8, 50, '', 70, '2025-07-31 00:00:00', 2, 1.00, '2025-07-31 20:46:08', '2025-07-31 20:46:08'),
(272, 3, 15, '', 55, '2025-07-31 00:00:00', 1, 0.15, '2025-07-31 20:51:53', '2025-07-31 20:51:53'),
(273, 4, 85, '', 55, '2025-07-31 00:00:00', 1, 0.85, '2025-07-31 20:51:53', '2025-07-31 20:51:53'),
(274, 3, 90, '', 80, '2025-07-25 00:00:00', 2, 1.80, '2025-07-31 21:01:08', '2025-07-31 21:01:08'),
(275, 9, 10, '', 80, '2025-07-25 00:00:00', 2, 0.20, '2025-07-31 21:01:08', '2025-07-31 21:01:08'),
(276, 6, 45, '', 89, '2025-07-30 00:00:00', 3, 1.35, '2025-07-31 21:04:39', '2025-07-31 21:04:39'),
(277, 5, 45, '', 89, '2025-07-30 00:00:00', 3, 1.35, '2025-07-31 21:04:39', '2025-07-31 21:04:39'),
(278, 4, 10, '', 89, '2025-07-30 00:00:00', 3, 0.30, '2025-07-31 21:04:39', '2025-07-31 21:04:39'),
(283, 9, 35, '', 42, '2025-07-31 00:00:00', 3, 1.05, '2025-07-31 21:22:47', '2025-07-31 21:22:47'),
(284, 4, 24, '', 42, '2025-07-31 00:00:00', 3, 0.72, '2025-07-31 21:22:47', '2025-07-31 21:22:47'),
(285, 3, 29, '', 42, '2025-07-31 00:00:00', 3, 0.87, '2025-07-31 21:22:47', '2025-07-31 21:22:47'),
(286, 6, 12, '', 42, '2025-07-31 00:00:00', 3, 0.36, '2025-07-31 21:22:47', '2025-07-31 21:22:47'),
(293, 8, 95, '', 87, '2025-07-22 00:00:00', 1, 0.95, '2025-07-31 21:55:15', '2025-07-31 21:55:15'),
(294, 6, 5, '', 87, '2025-07-22 00:00:00', 1, 0.05, '2025-07-31 21:55:15', '2025-07-31 21:55:15'),
(295, 6, 100, '', 96, '2025-07-31 01:00:00', 2, 2.00, '2025-07-31 22:29:39', '2025-07-31 22:29:39'),
(301, 6, 50, '', 45, '2025-07-17 00:00:00', 1, 0.50, '2025-07-31 23:21:07', '2025-07-31 23:21:07'),
(302, 4, 30, '', 45, '2025-07-17 00:00:00', 1, 0.30, '2025-07-31 23:21:07', '2025-07-31 23:21:07'),
(303, 3, 20, '', 45, '2025-07-17 00:00:00', 1, 0.20, '2025-07-31 23:21:07', '2025-07-31 23:21:07'),
(304, 3, 100, '', 99, '2025-08-01 12:00:00', 3, 3.00, '2025-08-01 17:09:00', '2025-08-01 17:09:00'),
(305, 5, 100, '', 43, '2025-07-08 00:00:00', 3, 3.00, '2025-08-01 17:34:07', '2025-08-01 17:34:07'),
(306, 6, 50, '', 98, '2025-07-30 00:00:00', 1, 0.50, '2025-08-01 17:45:51', '2025-08-01 17:45:51'),
(307, 8, 50, '', 98, '2025-07-30 00:00:00', 1, 0.50, '2025-08-01 17:45:51', '2025-08-01 17:45:51'),
(308, 4, 50, '', 97, '2025-07-31 00:00:00', 1, 0.50, '2025-08-01 17:49:02', '2025-08-01 17:49:02'),
(309, 8, 50, '', 97, '2025-07-31 00:00:00', 1, 0.50, '2025-08-01 17:49:02', '2025-08-01 17:49:02'),
(310, 6, 70, '', 93, '2025-07-31 00:00:00', 3, 2.10, '2025-08-01 18:36:17', '2025-08-01 18:36:17'),
(311, 8, 30, '', 93, '2025-07-31 00:00:00', 3, 0.90, '2025-08-01 18:36:17', '2025-08-01 18:36:17'),
(312, 3, 80, '', 100, '2025-07-31 17:30:00', 1, 0.80, '2025-08-01 18:40:51', '2025-08-01 18:40:51'),
(313, 9, 20, '', 100, '2025-07-31 17:30:00', 1, 0.20, '2025-08-01 18:40:51', '2025-08-01 18:40:51'),
(314, 3, 90, '', 77, '2025-07-21 00:00:00', 3, 2.70, '2025-08-01 18:52:25', '2025-08-01 18:52:25'),
(315, 8, 10, '', 77, '2025-07-21 00:00:00', 3, 0.30, '2025-08-01 18:52:25', '2025-08-01 18:52:25'),
(316, 3, 80, '', 104, '2025-08-05 16:40:00', 4, 3.20, '2025-08-05 21:40:56', '2025-08-05 21:40:56'),
(317, 9, 20, '', 104, '2025-08-05 16:40:00', 4, 0.80, '2025-08-05 21:40:56', '2025-08-05 21:40:56'),
(318, 8, 5, '', 69, '2025-08-05 00:00:00', 3, 0.15, '2025-08-08 20:33:30', '2025-08-08 20:33:30'),
(319, 6, 15, '', 69, '2025-08-05 00:00:00', 3, 0.45, '2025-08-08 20:33:30', '2025-08-08 20:33:30'),
(320, 3, 35, '', 69, '2025-08-05 00:00:00', 3, 1.05, '2025-08-08 20:33:30', '2025-08-08 20:33:30'),
(321, 4, 45, '', 69, '2025-08-05 00:00:00', 3, 1.35, '2025-08-08 20:33:30', '2025-08-08 20:33:30'),
(326, 3, 50, '', 106, '2025-08-07 15:53:00', 1, 0.50, '2025-08-08 21:23:16', '2025-08-08 21:23:16'),
(327, 9, 50, '', 106, '2025-08-07 15:53:00', 1, 0.50, '2025-08-08 21:23:16', '2025-08-08 21:23:16'),
(328, 6, 100, '', 107, '2025-08-07 08:00:00', 4, 4.00, '2025-08-08 21:25:30', '2025-08-08 21:25:30'),
(329, 3, 50, '', 103, '2025-08-07 00:00:00', 3, 1.50, '2025-08-08 21:26:10', '2025-08-08 21:26:10'),
(330, 9, 30, '', 103, '2025-08-07 00:00:00', 3, 0.90, '2025-08-08 21:26:10', '2025-08-08 21:26:10'),
(331, 6, 20, '', 103, '2025-08-07 00:00:00', 3, 0.60, '2025-08-08 21:26:10', '2025-08-08 21:26:10'),
(333, 3, 100, '', 94, '2025-08-08 17:30:00', 4, 4.00, '2025-08-08 21:30:58', '2025-08-08 21:30:58'),
(338, 4, 100, '', 109, '2025-08-07 16:42:00', 1, 1.00, '2025-08-08 21:43:26', '2025-08-08 21:43:26'),
(339, 6, 40, '', 105, '2025-08-04 00:00:00', 4, 1.60, '2025-08-08 22:01:13', '2025-08-08 22:01:13'),
(340, 3, 40, '', 105, '2025-08-04 00:00:00', 4, 1.60, '2025-08-08 22:01:13', '2025-08-08 22:01:13'),
(341, 4, 15, '', 105, '2025-08-04 00:00:00', 4, 0.60, '2025-08-08 22:01:13', '2025-08-08 22:01:13'),
(342, 8, 5, '', 105, '2025-08-04 00:00:00', 4, 0.20, '2025-08-08 22:01:13', '2025-08-08 22:01:13'),
(343, 3, 88, '', 108, '2025-08-08 00:00:00', 5, 4.40, '2025-08-11 17:10:52', '2025-08-11 17:10:52'),
(344, 6, 12, '', 108, '2025-08-08 00:00:00', 5, 0.60, '2025-08-11 17:10:52', '2025-08-11 17:10:52'),
(345, 6, 100, '', 110, '2025-08-11 04:00:00', 1, 1.00, '2025-08-11 21:45:49', '2025-08-11 21:45:49'),
(346, 6, 20, '', 111, '2025-08-12 15:00:00', 3, 0.60, '2025-08-12 21:33:54', '2025-08-12 21:33:54'),
(347, 3, 80, '', 111, '2025-08-12 15:00:00', 3, 2.40, '2025-08-12 21:33:54', '2025-08-12 21:33:54'),
(349, 3, 60, '', 114, '2025-08-13 11:00:00', 1, 0.60, '2025-08-13 16:54:27', '2025-08-13 16:54:27'),
(350, 9, 40, '', 114, '2025-08-13 11:00:00', 1, 0.40, '2025-08-13 16:54:27', '2025-08-13 16:54:27'),
(351, 6, 100, '', 115, '2025-08-14 11:00:00', 1, 1.00, '2025-08-14 17:30:50', '2025-08-14 17:30:50'),
(354, 6, 100, '', 113, '2025-08-12 00:00:00', 2, 2.00, '2025-08-15 21:27:21', '2025-08-15 21:27:21'),
(357, 3, 80, '', 117, '2025-08-15 18:50:00', 2, 1.60, '2025-08-15 23:57:31', '2025-08-15 23:57:31'),
(358, 9, 20, '', 117, '2025-08-15 18:50:00', 2, 0.40, '2025-08-15 23:57:31', '2025-08-15 23:57:31'),
(362, 3, 20, '', 118, '2025-08-15 00:00:00', 1, 0.20, '2025-08-16 00:07:21', '2025-08-16 00:07:21'),
(363, 9, 70, '', 118, '2025-08-15 00:00:00', 1, 0.70, '2025-08-16 00:07:21', '2025-08-16 00:07:21'),
(364, 6, 10, '', 118, '2025-08-15 00:00:00', 1, 0.10, '2025-08-16 00:07:21', '2025-08-16 00:07:21'),
(367, 4, 10, '', 119, '2025-08-15 00:00:00', 1, 0.10, '2025-08-16 01:25:53', '2025-08-16 01:25:53'),
(368, 8, 90, '', 119, '2025-08-15 00:00:00', 1, 0.90, '2025-08-16 01:25:53', '2025-08-16 01:25:53'),
(369, 6, 40, '', 116, '2025-08-15 00:00:00', 1, 0.40, '2025-08-16 06:31:12', '2025-08-16 06:31:12'),
(370, 3, 60, '', 116, '2025-08-15 00:00:00', 1, 0.60, '2025-08-16 06:31:12', '2025-08-16 06:31:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `distribucion_planificacion`
--

CREATE TABLE `distribucion_planificacion` (
  `iddistribucionplan` int(11) NOT NULL,
  `iddetalle` int(11) NOT NULL,
  `idparticipante` int(11) NOT NULL,
  `porcentaje` int(11) NOT NULL,
  `horas_asignadas` decimal(10,2) DEFAULT NULL,
  `registrado` timestamp NOT NULL DEFAULT current_timestamp(),
  `modificado` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `distribucion_planificacion`
--

INSERT INTO `distribucion_planificacion` (`iddistribucionplan`, `iddetalle`, `idparticipante`, `porcentaje`, `horas_asignadas`, `registrado`, `modificado`) VALUES
(285, 128, 4, 100, 1.00, '2025-07-23 00:50:01', '2025-07-23 00:50:01'),
(2100, 1, 4, 100, 2.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2101, 2, 4, 100, 2.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2102, 3, 4, 90, 0.90, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2103, 3, 8, 10, 0.10, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2104, 18, 3, 10, 0.10, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2105, 18, 6, 10, 0.10, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2106, 18, 8, 80, 0.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2107, 19, 3, 50, 2.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2108, 19, 8, 50, 2.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2109, 20, 3, 80, 1.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2110, 20, 8, 20, 0.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2111, 21, 8, 100, 1.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2112, 22, 3, 10, 0.10, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2113, 22, 8, 90, 0.90, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2114, 4, 3, 20, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2115, 4, 6, 20, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2116, 4, 8, 60, 0.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2117, 13, 3, 10, 0.10, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2118, 13, 6, 20, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2119, 13, 8, 70, 0.70, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2120, 14, 8, 100, 1.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2121, 5, 3, 80, 2.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2122, 5, 8, 20, 0.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2123, 6, 3, 10, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2124, 6, 6, 80, 1.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2125, 6, 8, 10, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2126, 7, 3, 50, 2.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2127, 7, 6, 30, 1.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2128, 7, 8, 20, 0.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2129, 8, 3, 40, 3.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2130, 8, 8, 60, 5.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2131, 9, 3, 40, 0.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2132, 9, 6, 60, 1.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2133, 15, 3, 20, 0.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2134, 15, 6, 60, 2.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2135, 15, 8, 20, 0.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2136, 10, 3, 40, 0.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2137, 10, 8, 60, 0.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2138, 16, 3, 30, 0.90, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2139, 16, 6, 70, 2.10, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2140, 11, 4, 90, 1.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2141, 11, 8, 10, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2142, 46, 3, 10, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2143, 46, 6, 10, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2144, 46, 8, 80, 1.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2145, 17, 6, 90, 1.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2146, 17, 8, 10, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2147, 12, 3, 50, 1.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2148, 12, 8, 50, 1.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2149, 47, 3, 80, 3.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2150, 47, 8, 20, 0.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2151, 62, 4, 95, 1.90, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2152, 62, 8, 5, 0.10, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2153, 23, 4, 25, 0.25, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2154, 23, 8, 75, 0.75, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2155, 55, 6, 30, 0.30, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2156, 55, 4, 50, 0.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2157, 55, 3, 20, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2158, 56, 6, 70, 2.10, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2159, 56, 3, 25, 0.75, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2160, 56, 8, 5, 0.15, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2161, 42, 6, 80, 3.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2162, 42, 8, 20, 0.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2163, 24, 4, 10, 0.10, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2164, 24, 8, 90, 0.90, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2165, 39, 4, 10, 0.10, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2166, 39, 8, 90, 0.90, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2167, 57, 6, 35, 0.35, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2168, 57, 4, 60, 0.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2169, 57, 3, 5, 0.05, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2170, 48, 3, 95, 0.95, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2171, 48, 8, 5, 0.05, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2172, 63, 3, 50, 1.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2173, 63, 5, 50, 1.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2174, 49, 9, 35, 1.05, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2175, 49, 4, 24, 0.72, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2176, 49, 3, 29, 0.87, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2177, 49, 6, 12, 0.36, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2178, 65, 5, 100, 3.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2179, 43, 6, 70, 2.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2180, 43, 8, 30, 1.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2181, 58, 6, 50, 0.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2182, 58, 4, 30, 0.30, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2183, 58, 3, 20, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2184, 25, 4, 35, 1.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2185, 25, 8, 65, 2.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2186, 33, 5, 20, 0.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2187, 33, 4, 80, 1.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2188, 34, 8, 10, 0.30, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2189, 34, 4, 90, 2.70, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2190, 35, 8, 5, 0.10, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2191, 35, 4, 95, 1.90, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2192, 66, 3, 100, 3.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2193, 59, 3, 20, 0.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2194, 59, 4, 80, 3.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2195, 26, 4, 50, 1.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2196, 26, 8, 50, 1.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2197, 27, 8, 100, 3.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2198, 36, 3, 15, 0.15, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2199, 36, 4, 85, 0.85, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2200, 67, 5, 100, 3.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2201, 28, 4, 50, 0.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2202, 28, 3, 50, 0.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2203, 50, 3, 80, 1.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2204, 50, 8, 20, 0.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2205, 51, 3, 40, 0.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2206, 51, 8, 50, 1.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2207, 51, 6, 10, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2208, 60, 6, 90, 0.90, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2209, 60, 8, 10, 0.10, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2210, 61, 8, 75, 0.75, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2211, 61, 6, 20, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2212, 61, 3, 5, 0.05, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2213, 52, 3, 20, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2214, 52, 8, 80, 0.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2215, 53, 3, 70, 1.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2216, 53, 8, 30, 0.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2217, 29, 3, 15, 0.30, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2218, 29, 4, 85, 1.70, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2219, 54, 3, 70, 2.10, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2220, 54, 8, 30, 0.90, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2221, 45, 6, 80, 2.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2222, 45, 8, 20, 0.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2223, 44, 6, 95, 3.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2224, 44, 8, 5, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2225, 40, 8, 10, 0.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2226, 40, 4, 80, 3.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2227, 40, 5, 10, 0.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2228, 37, 8, 5, 0.15, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2229, 37, 6, 15, 0.45, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2230, 37, 3, 35, 1.05, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2231, 37, 4, 45, 1.35, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2232, 30, 5, 25, 0.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2233, 30, 3, 25, 0.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2234, 30, 8, 50, 1.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2235, 41, 5, 30, 0.90, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2236, 41, 8, 20, 0.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2237, 41, 4, 50, 1.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2238, 38, 4, 15, 0.15, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2239, 38, 8, 35, 0.35, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2240, 38, 3, 50, 0.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2241, 31, 8, 10, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2242, 31, 4, 90, 1.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2243, 32, 8, 50, 2.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2244, 32, 4, 50, 2.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2245, 64, 3, 50, 2.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2246, 64, 6, 20, 0.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2247, 64, 9, 30, 1.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2248, 139, 3, 90, 2.70, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2249, 139, 8, 10, 0.30, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2250, 129, 3, 60, 2.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2251, 129, 9, 40, 1.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2252, 130, 4, 80, 3.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2253, 130, 3, 10, 0.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2254, 130, 9, 10, 0.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2255, 131, 3, 90, 1.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2256, 131, 9, 10, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2257, 133, 8, 95, 0.95, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2258, 133, 3, 5, 0.05, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2259, 134, 3, 50, 2.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2260, 134, 4, 50, 2.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2261, 135, 6, 60, 0.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2262, 135, 8, 40, 0.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2263, 136, 4, 10, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2264, 136, 8, 10, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2265, 136, 3, 80, 1.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2266, 138, 8, 95, 0.95, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2267, 138, 6, 5, 0.05, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2268, 140, 3, 10, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2269, 140, 4, 90, 1.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2270, 141, 6, 45, 1.35, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2271, 141, 5, 45, 1.35, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2272, 141, 4, 10, 0.30, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2273, 142, 4, 50, 0.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2274, 142, 3, 50, 0.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2275, 143, 3, 100, 3.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2276, 144, 6, 70, 2.10, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2277, 144, 8, 30, 0.90, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2278, 145, 3, 100, 4.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2279, 146, 9, 30, 0.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2280, 146, 4, 70, 1.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2281, 147, 4, 50, 0.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2282, 147, 8, 50, 0.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2283, 148, 6, 50, 0.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2284, 148, 8, 50, 0.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2285, 150, 3, 100, 3.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2286, 149, 3, 80, 0.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2287, 149, 9, 20, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2288, 157, 3, 50, 1.50, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2289, 157, 9, 30, 0.90, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2290, 157, 6, 20, 0.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2291, 158, 3, 80, 3.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2292, 158, 9, 20, 0.80, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2293, 161, 6, 40, 1.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2294, 161, 3, 40, 1.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2295, 161, 4, 15, 0.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2296, 161, 8, 5, 0.20, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2297, 163, 6, 100, 4.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2298, 159, 3, 88, 4.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2299, 159, 6, 12, 0.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2300, 151, 4, 100, 1.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2301, 162, 6, 100, 1.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2302, 160, 6, 20, 0.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2303, 160, 3, 80, 2.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2305, 154, 3, 60, 0.60, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2306, 154, 9, 40, 0.40, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2307, 170, 6, 100, 1.00, '2025-08-15 18:16:16', '2025-08-15 18:16:16'),
(2357, 153, 6, 100, 2.00, '2025-08-15 21:27:21', '2025-08-15 21:27:21'),
(2361, 172, 3, 80, 1.60, '2025-08-15 23:57:31', '2025-08-15 23:57:31'),
(2362, 172, 9, 20, 0.40, '2025-08-15 23:57:31', '2025-08-15 23:57:31'),
(2367, 173, 3, 20, 0.20, '2025-08-16 00:07:21', '2025-08-16 00:07:21'),
(2368, 173, 9, 70, 0.70, '2025-08-16 00:07:21', '2025-08-16 00:07:21'),
(2369, 173, 6, 10, 0.10, '2025-08-16 00:07:21', '2025-08-16 00:07:21'),
(2373, 174, 4, 10, 0.10, '2025-08-16 01:25:53', '2025-08-16 01:25:53'),
(2374, 174, 8, 90, 0.90, '2025-08-16 01:25:53', '2025-08-16 01:25:53'),
(2376, 171, 6, 40, 0.40, '2025-08-16 06:31:12', '2025-08-16 06:31:12'),
(2377, 171, 3, 60, 0.60, '2025-08-16 06:31:12', '2025-08-16 06:31:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

CREATE TABLE `empleado` (
  `idempleado` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `paterno` varchar(50) NOT NULL,
  `materno` varchar(50) NOT NULL,
  `nombrecorto` varchar(50) DEFAULT NULL,
  `dni` varchar(10) NOT NULL,
  `nacimiento` date NOT NULL,
  `lugarnacimiento` varchar(100) NOT NULL,
  `domicilio` varchar(150) NOT NULL,
  `estadocivil` varchar(50) NOT NULL,
  `correopersonal` varchar(100) NOT NULL,
  `correocorporativo` varchar(100) NOT NULL,
  `telcelular` varchar(15) NOT NULL,
  `telfijo` varchar(10) NOT NULL,
  `horasmeta` int(11) NOT NULL DEFAULT 30,
  `area` varchar(50) NOT NULL,
  `cargo` varchar(50) NOT NULL,
  `derechohabiente` varchar(50) NOT NULL,
  `cantidadhijos` int(11) NOT NULL,
  `contactoemergencia` varchar(100) NOT NULL,
  `nivelestudios` varchar(50) NOT NULL,
  `regimenpension` varchar(50) NOT NULL,
  `fondopension` varchar(50) NOT NULL,
  `cussp` varchar(50) NOT NULL,
  `modalidad` varchar(50) NOT NULL,
  `rutafoto` varchar(250) NOT NULL,
  `activo` int(11) NOT NULL,
  `editor` int(11) NOT NULL DEFAULT 0,
  `registrado` timestamp NOT NULL DEFAULT current_timestamp(),
  `modificado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empleado`
--

INSERT INTO `empleado` (`idempleado`, `nombres`, `paterno`, `materno`, `nombrecorto`, `dni`, `nacimiento`, `lugarnacimiento`, `domicilio`, `estadocivil`, `correopersonal`, `correocorporativo`, `telcelular`, `telfijo`, `horasmeta`, `area`, `cargo`, `derechohabiente`, `cantidadhijos`, `contactoemergencia`, `nivelestudios`, `regimenpension`, `fondopension`, `cussp`, `modalidad`, `rutafoto`, `activo`, `editor`, `registrado`, `modificado`) VALUES
(1, 'Kelly Yajaira', 'Renquifo', 'Cieza', 'Kelly', '47962973', '1993-09-05', 'Hualgayoc, Cajamarca', 'Jr. Trinidad 295, Dpto 301, Urb. Villa Jardin, San Luis, Lima', 'Soltera', 'k.renquifo@gmail.com', 'kelly.renquifo@ampara.pe', '951600532', 'No Aplica', 30, '', '', 'No Aplica', 0, 'Flor Cieza Infante - Madre - 971792154', 'Bachiller en Derecho', 'AFP', 'PRIMA', '342150KRCQZ1', 'Planilla', 'img/fotos/empleados/Kelly.png', 0, 0, '2025-07-02 16:00:21', '2025-07-02 16:00:21'),
(2, 'Maria Lucia Margot\r\n', 'Gonzalez', 'Soto', 'Malú', '70774300', '1999-05-24', 'Miraflores, Lima', 'Curazao 385, La Molina', 'Soltera', 'malumags@gmail.com', 'marialucia.gonzalez@ampara.pe', '980362757', 'No Aplica', 30, '', '', 'No Aplica', 0, 'Victor Humberto Gonzalez Acuña | Padre | 999106985', 'Bachiller en Psicología', 'AFP', 'INTEGRA', '663020MGSZO8', 'Planilla', 'img/fotos/empleados/Maria.png', 1, 0, '2025-07-02 16:00:21', '2025-07-02 16:00:21'),
(3, 'Jacy Sarahi', 'Rojas', 'Pasapera', 'Jacy', '72667251', '2000-04-30', 'Piura, Piura', 'Av. Benjamin Franklin 576, Ate', 'Soltera', 'jacyrp72667251@gmail.com', 'jacy.rojas@ampara.pe', '963587885', 'No Aplica', 30, '', '', 'No Aplica', 0, 'JACINTA PASAPERA PINTADO | MAMÁ | 928153463', 'Bachiller en Ingeniería Electrónica y de Telecomun', 'AFP', 'INTEGRA', '666440JRPAA4', 'Planilla', 'img/fotos/empleados/Jacy.png', 1, 0, '2025-07-02 16:00:21', '2025-07-02 16:00:21'),
(4, 'Gustavo Vittorio', 'Ramirez', 'Sanchez', 'Gustavo', '75310964', '1998-07-25', 'Miraflores, Lima', 'Sector 03 - Grupo 15 Mz. B Lote 3, Villa El Salvador								', 'Soltero', 'asdafe25@gmail.com', 'gustavo.ramirez@ampara.pe', '944340916', 'No Aplica', 30, '', '', 'No Aplica', 0, 'Victorio Ramirez Sánchez | Padre | 949231482', 'Bachiller en Derecho', 'AFP', 'INTEGRA', '659991GRSIC6', 'Planilla', 'img/fotos/empleados/Gustavo.png', 1, 0, '2025-07-02 16:00:21', '2025-07-02 16:00:21'),
(5, 'Katy Andrea', 'Nieto', 'Casafranca', 'Katy', '72369959', '1997-12-09', 'Santiago, Cusco', 'Calle Chacabuco Nº 185, Torre Nº 8 y departamento Nº 1207, San Miguel', 'Soltera', 'katy.nieto@pucp.edu.pe', 'katy.nieto@ampara.pe', '982049282', 'No Aplica', 30, '', '', 'No Aplica', 0, '', 'Bachiller en Derecho', 'AFP', 'INTEGRA', '657710KNCTA9', 'Planilla', 'img/fotos/empleados/Katy.png', 0, 2, '2025-07-02 16:00:21', '2025-08-15 18:20:58'),
(6, 'Janira Samajuto', 'Torres', 'Cuadros', 'Janira', '48137494', '1994-02-23', 'Huamanga, Ayacucho', 'Duque de la Palata 157, Surco', 'Soltera', 'janira_torres_@outlook.com', 'janira.torres@ampara.pe', '994341934', 'No Aplica', 30, '', '', 'No Aplica', 0, 'TORRES CUADROS TONY | HERMANO | 920 877 844	', 'Titulada en Derecho', 'AFP', 'INTEGRA', '643860JTCRD0', 'Planilla', 'img/fotos/empleados/Janira.png', 1, 0, '2025-07-02 16:00:21', '2025-07-02 16:00:21'),
(7, 'David Gustavo', 'Roque', 'Mamani', 'David', '72682491', '1994-11-01', 'Yanahuara, Arequipa', 'Rafael Aedo Guerrero Mz Z1 Lote 6, Surco', 'Soltera', 'davidgroquem@gmail.com', 'david.roque@ampara.pe', '974438885', 'No Aplica', 30, '', '', 'No Aplica', 0, 'Flores Turpo Fancy Noemi | Conviviente | 955713507', 'Bachiller en Ingeniería de Telecomunicaciones', 'AFP', 'PRIMA', '646371DRMUA9', 'Planilla', 'img/fotos/empleados/David.png', 0, 0, '2025-07-02 16:00:21', '2025-07-02 16:00:21'),
(8, 'Juan Carlos', 'Cornejo', 'Cuzzi', 'Socios', '10286953', '1977-03-07', 'Lima', 'Miraflores, Lima', 'Casado', 'jccornejocuzzi@gmail.com', 'juancarlos.cornejo@ampara.pe', '996291396', 'No Aplica', 30, '', '', 'No Aplica', 0, '', 'Abogado Colegiado', 'AFP', 'PRIMA', '581891JCCNZ0', 'Recibo por Honorarios', 'img/fotos/empleados/Juan_Carlos.png', 1, 11, '2025-07-02 16:00:21', '2025-07-23 01:26:54'),
(9, 'Gino Christian', 'Kou', 'Reyna', 'Socios', '10288581', '1977-05-17', 'Lima', 'Surco, Lima', 'Casado', '', 'gino.kou@ampara.pe', '995731361', 'No Aplica', 30, '', '', 'No Aplica', 0, '', 'Ingeniero Colegiado', 'AFP', 'HABITAT', '582601GKRUN1', 'Recibo por Honorarios', 'img/fotos/empleados/Gino.png', 1, 0, '2025-07-02 16:00:21', '2025-07-02 16:00:21'),
(10, 'Maria Laura', 'Yataco', 'Cornejo', 'Maria Laura', '07628964', '0000-00-00', '', 'Calle 28 de Julio 535 Dpto 302, Magdalena del Mar', '', 'marialaura.yataco@gmail.com', '', '975590975', '', 30, '', '', '', 0, '', '', 'AFP', 'PRIMA', '566820MYCAN2', 'Recibo por Honorarios', 'img/fotos/empleados/Milena.png', 0, 0, '2025-07-02 16:00:21', '2025-07-02 16:00:21'),
(11, 'hUGo', 'ore', 'Julca', 'Hhugo ore', '10650100', '0980-07-22', 'PL', 'QW', 'Soltero', 'HCOREJ@GMAIL.COM', 'HCOREJ@GMAIL.COM', '8654654645', '543541231', 20, 'Recursos Humanos', 'Asociado', 'No aplica', 1, 'GDGDF', 'Bachiller', 'AFP', 'INTEGRA', 'UTYURTYU', 'Recibo por Honorarios', 'img/fotos/empleados/Hugo.PNG', 0, 2, '2025-07-23 00:03:28', '2025-08-15 18:21:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evento`
--

CREATE TABLE `evento` (
  `idevento` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `colorfondo` varchar(25) NOT NULL,
  `colortexto` varchar(25) NOT NULL,
  `url` varchar(150) NOT NULL,
  `fechainicio` date NOT NULL,
  `fechafin` date NOT NULL,
  `lider` int(11) NOT NULL,
  `acargode` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturacion`
--

CREATE TABLE `facturacion` (
  `idfacturacion` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `horasgen` varchar(50) NOT NULL,
  `tipocliente` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `moneda` varchar(50) NOT NULL,
  `cambiosunat` decimal(5,2) NOT NULL,
  `tiposervicio` varchar(50) NOT NULL,
  `idcliente` int(11) NOT NULL,
  `subtotal` decimal(7,2) NOT NULL,
  `igv` decimal(7,2) NOT NULL,
  `total` decimal(7,2) NOT NULL,
  `detraccion` decimal(7,2) NOT NULL,
  `netosindetrac` decimal(7,2) NOT NULL,
  `fechaemision` date NOT NULL,
  `fechaenvio` date NOT NULL,
  `fechapago` date NOT NULL,
  `fechapagodetrac` date NOT NULL,
  `comentarios` varchar(500) NOT NULL,
  `mesemision` date NOT NULL,
  `mescobrado` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `liquidacion`
--

CREATE TABLE `liquidacion` (
  `idliquidacion` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `asunto` varchar(1500) NOT NULL,
  `tema` int(11) NOT NULL,
  `motivo` mediumtext NOT NULL,
  `tipohora` varchar(45) NOT NULL,
  `acargode` int(11) NOT NULL,
  `lider` int(11) NOT NULL,
  `cantidahoras` int(11) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `idcontratocli` int(11) NOT NULL,
  `idpresupuesto` int(11) NOT NULL,
  `activo` int(11) NOT NULL,
  `editor` int(11) NOT NULL DEFAULT 1,
  `registrado` timestamp NOT NULL DEFAULT current_timestamp(),
  `modificado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `liquidacion`
--

INSERT INTO `liquidacion` (`idliquidacion`, `fecha`, `asunto`, `tema`, `motivo`, `tipohora`, `acargode`, `lider`, `cantidahoras`, `estado`, `idcontratocli`, `idpresupuesto`, `activo`, `editor`, `registrado`, `modificado`) VALUES
(4, '2025-05-02', 'Análisis y revisión', 12, 'Elaboración de ficha de registro de nueva tarifa establecida por servicio de internet 1000 Mbps para clientes de MiFibra, indicando sus respectivas condiciones y restricciones. Asimismo, se realizó adecuaciones a la tarifa promocional por el servicio de internet 500 Mbps. Se brindaron recomendaciones y sugerencias ante los dos casos planteados para evaluación del cliente.', 'Soporte', 4, 4, 2, 'Completo', 1, 0, 1, 1, '2025-06-18 04:56:07', '2025-06-18 04:56:07'),
(5, '2025-05-13', 'Análisis y revisión', 6, 'Búsqueda, revisión y análisis de jurisprudencia de OSIPTEL vinculada a casuística de uso indebido, en base a la cual, se enviaron recomendaciones a CALA respecto de la implementación de sus protocolos para la detección y acreditación de casos de uso indebido de los servicios públicos de telecomunicaciones.', 'Soporte', 4, 4, 2, 'Completo', 1, 0, 1, 1, '2025-06-18 04:57:14', '2025-06-18 04:57:14'),
(6, '2025-05-30', 'Horas audio', 1, 'Seguimiento y consulta con OSIPTEL de solicitud de acceso a la información pública sobre detalles financieros de WIN y WOW (15.05.2025) // Llamada de Jorge Araujo con Juan Carlos para absolver consultas sobre plazo forzoso y resolución de contratos (20.05.2025) // Envío a cliente de información remitida por OSIPTEL en respuesta a solicitud de acceso a la información pública (20.05.2025).', 'Soporte', 5, 4, 1, 'Completo', 1, 0, 1, 1, '2025-06-18 04:59:56', '2025-06-18 04:59:56'),
(7, '2025-05-16', 'Reunión', 14, 'Reunión de revisión de pendientes.', 'Soporte', 6, 6, 1, 'Completo', 2, 0, 1, 1, '2025-06-18 05:01:13', '2025-06-18 05:01:13'),
(8, '2025-05-23', 'Análisis y revisión', 14, '- Revisión y análisis de la Carta N° 000184-2025-DPRC/OSIPTEL de OSIPTEL donde nos traslada los comentarios de Telefónica.\r\n- Elaboración de propuesta de respuesta a la carta del OSIPTEL donde se fundamentó legal y jurisprudencialmente la correcta aplicación del artículo 7 de las “NORMAS COMPLEMENTARIAS APLICABLES A LOS OPERADORES MÓVILES VIRTUALES” (RCD Nº 009-2016-CD/OSIPTEL); asimismo, se elaboraron diagramas de topología de red para darle mayor peso a la carta.', 'Soporte', 6, 6, 5, 'Completo', 2, 0, 1, 1, '2025-06-18 05:03:00', '2025-06-18 05:03:00'),
(9, '2025-05-27', 'Análisis y revisión', 23, 'Elaboración de carta para dar respuesta a la solicitud de información respecto a los compromisos establecidos a DOLPHIN en el proceso de\r\nreordenamiento de la banda de frecuencias 2 300 – 2 400 MHz realizada por el MTC.', 'Soporte', 3, 6, 2, 'Completo', 2, 0, 1, 1, '2025-06-18 05:04:20', '2025-06-18 05:04:20'),
(10, '2025-05-29', 'Horas audio', 1, 'Reuniones de coordinación (varias) entre Gino y Juan Carlos con Javier y César.', 'Soporte', 5, 6, 1, 'Completo', 2, 0, 1, 1, '2025-06-18 05:06:32', '2025-06-18 05:06:32'),
(11, '2025-05-29', 'Reunión', 1, 'Reunión respecto a la elaboración de medios probatorios que complementen la carta de respuesta al requerimiento de información del MTC (reordenamiento de bandas).', 'Soporte', 5, 6, 1, 'Completo', 2, 0, 1, 1, '2025-06-18 05:07:41', '2025-06-18 05:07:41'),
(12, '2025-05-06', 'Reunión', 15, 'Reunión para revisar lo precisado por el OSIPTEL en el mandato complementario de acceso entre INTERMAX y BITEL, y las actividades a seguir  para la implementación total del servicio.', 'Soporte', 6, 3, 1, 'Completo', 5, 0, 1, 1, '2025-06-18 05:09:08', '2025-06-18 05:09:08'),
(13, '2025-05-07', 'Reunión', 14, 'Reunión respecto a la respuesta de la Orden de Servicio por SMS enviadas a BITEL.', 'Soporte', 6, 6, 1, 'Completo', 4, 0, 1, 1, '2025-07-02 15:29:47', '2025-07-04 07:55:46'),
(14, '2025-05-09', 'Reunión', 14, 'Acompañamiento en la reunión de coordinación técnica con el equipo de BITEL a propósito de la OS por SMS enviada.', 'Soporte', 6, 6, 1, 'Completo', 4, 0, 1, 1, '2025-07-02 15:38:42', '2025-07-02 15:39:44'),
(15, '2025-05-09', 'Análisis y revisión', 25, 'Analisis y revisión de consulta respecto a la subsanación voluntaria de formatos establecidos en la NEIP, revisión de plazos establecidos, condiciones y mecanismos empleados. Analisis y elaboración estratégica frente a una posible sanción por la no presentación de los formatos en los 3 ultimos trimestres. Investigación sobre mecanismos empleados y las comunicaciones trasladadas por el OSIPTEL a los OMV.', 'Soporte', 3, 3, 3, 'Completo', 5, 0, 1, 1, '2025-07-02 15:41:45', '2025-07-02 15:41:45'),
(16, '2025-05-09', 'Análisis y revisión', 15, '\"Matriz-resumen de las disposiciones aprobadas mediante Mandato Complementario (coubicación) y las propuestas del Proyecto de Mandato y los comentarios presentados al respecto.\r\n\r\nIncluye lectura y analisis del proyecto del mandato y del mandato final emitido por el OSIPTEL \"', 'Soporte', 6, 3, 2, 'Completo', 5, 0, 1, 1, '2025-07-02 15:43:31', '2025-07-02 15:43:31'),
(17, '2025-05-09', 'Análisis y revisión', 15, '\"Análisis, revisión y modificación de proyecto de carta para BITEL para solicitar \r\n(i) la habilitación de la numeración asignada a INTERMAX en la red de BITEL \r\n(ii) la notificación correspondiente a los operadores con los cuales BITEL mantiene relaciones de interconexión\r\n(iii) la culminación de todas las configuraciones técnicas necesarias para permitir el acceso efectivo y operativo de los servicios de INTERMAX (Voz y SMS) a la red de BITEL.\r\n\r\nIncluye el análisis de los antecedentes (actas ', 'Soporte', 6, 3, 4, 'Completo', 5, 0, 1, 1, '2025-07-02 15:44:51', '2025-07-02 15:44:51'),
(18, '2025-05-13', 'Reunión', 14, '\"Reunión de acompañamiento para acción de fiscalización del OSIPTEL por el posible incumplimiento a la Medida Correctiva impuesta a BITEL. \r\nApoyo en elaboración de actas, establecer escenarios de pruebas y detalle de comentarios\"', 'Soporte', 6, 3, 9, 'Completo', 5, 0, 1, 1, '2025-07-02 15:54:03', '2025-07-02 15:54:03'),
(19, '2025-05-16', 'Análisis y revisión', 15, 'Absolución de consulta respecto la solicitud de la facturación por la implementación del acceso con BITEL y actualización de carta. Además evaluación de plazos establecidos en el mandato de acceso y mandato complementario.', 'Soporte', 6, 3, 2, 'Completo', 5, 0, 1, 1, '2025-07-02 15:56:16', '2025-07-02 15:56:16'),
(20, '2025-05-23', 'Análisis y revisión', 14, '- Revisión y análisis de la  Carta DMR-CE-1378-25 de CLARO mediante la cual se pronunció sobre la solicitud de interconexión de telefonía+ transporte, a través de protocolo SIP, y SMS.\r\n- Elaboración de propuesta de respuesta a la carta de CLARO donde, principalmente, se cuestionó (bajo fundamento legal y jurisprudencial) la posición de rechazo de CLARO sobre la solicitud de SMS bajo cualquier modalidad que contemple aplicativo.\r\n- Atención a los correos y mensajes posteriores al respecto.', 'Soporte', 6, 6, 4, 'Completo', 4, 0, 1, 1, '2025-07-02 15:57:49', '2025-07-02 15:57:49'),
(21, '2025-05-23', 'Reunión', 26, 'Reunión para absolver consultas respecto a la fiscalización por el uso del MNC en los servicios fijo y de OMV a cargo de MTC ', 'Soporte', 3, 3, 1, 'Completo', 5, 0, 1, 1, '2025-07-02 16:00:09', '2025-07-02 16:00:09'),
(22, '2025-05-26', 'Horas audio', 14, '- Llamada previa de coordinación con Rafael para la reunión de acompañamiento con BITEL (cronograma de actividades).\r\n- Llamada de coordinación con Rafael sobre los siguientes pasos en asuntos de interconexión a propósito de la reunión de acompañamiento.\r\n- Revisión de la orden de servicio por llamada (SIP), revisión del Mandato de interconexión con Bitel y la normativa de interconexión a fin de validar su correcto envío por el equipo de Fibermax. Se identificó y alertó (mediante llamada del 12.', 'Soporte', 6, 6, 3, 'Completo', 4, 0, 1, 1, '2025-07-02 16:02:16', '2025-07-02 16:02:16'),
(23, '2025-05-28', 'Análisis y revisión', 1, 'Absolución de consulta y emisión de recomendaciones respecto de las acciones a seguir por INTERMAX ante la imposibilidad de continuar compensando sus pagos por cargos de interconexión ante TELEFÓNICA, como resultado del inicio de su procedimiento concursal, en función de la normativa concursal y regulatoria aplicable.', 'Soporte', 5, 3, 2, 'Completo', 5, 0, 1, 1, '2025-07-02 16:03:33', '2025-07-02 16:03:33'),
(24, '2025-07-04', 'Reunión', 15, 'Reunión de coordinación para establecer una estrategia frente a la implementación del acceso con BITEL; además se absolvieron consultas respecto al riesgo regulatorio por contemplara el grupo económico de INTERMAX en la carta a BITEL por bloqueo a la interconexión y acceso ', 'Soporte', 6, 3, 2, 'Completo', 5, 0, 1, 3, '2025-07-02 16:04:50', '2025-07-04 21:11:27'),
(25, '2025-05-30', 'Análisis y revisión', 14, '\"- Revisión de la Orden de Servicio N° FBX-0002 (OS) vinculado al servicio de llamadas - SIP.\r\n- Revisión y análisis de los alcances de la carta N° 0606-2024/GL.CDR de BITEL donde observa la OS emitida.\r\n- Elaboración de proyecto de respuesta a la carta de BITEL y elaboración de nueva Orden de Servicio para llamadas - SIP.\"', 'Soporte', 6, 6, 2, 'Completo', 4, 0, 1, 1, '2025-07-02 16:05:54', '2025-07-02 16:05:54'),
(26, '2025-05-30', 'Horas audio', 14, 'Audio con Osiptel respecto a los formatos aplicables para el cumplimiento del NEIP | Reunión con Rafael al mediodía para evaluar status de fiscalización (al mediodía) | Reunión con Marcelo al culminar la fiscalización para evaluar posición frente a los posibles resultados de la evaluación de OSIPTEL | Audio con Kattya, respecto a la fiscalización de MNC por parte del OSIPTEL (29-05-25)', 'Soporte', 6, 3, 2, 'Completo', 5, 0, 1, 1, '2025-07-02 16:07:30', '2025-07-02 16:07:30'),
(30, '2025-07-02', 'Análisis y revisión', 15, 'Comentarios al proyecto de mandato de OMV con ENTEL, se realizó una lectura integral del documento para mapear puntos a comentar y posteriormente precisar los que tendría una mayor viabilidad y consistencia con la situación actual de INTERMAX.  S e evaluaron antecedentes para mapear relaciones de acceso de ENTEL con otros OMV; además se realizó análisis de costos actuales que INTERMAX mantiene con otros OMR y los cargos que sería aplicables asociados a la ORMV', 'Soporte', 6, 3, 4, 'Completo', 5, 0, 1, 3, '2025-07-02 18:37:55', '2025-07-04 21:17:01'),
(31, '2025-07-02', 'Análisis y revisión', 12, 'Atención de consulta de Javier Sánchez sobre obligación de DOLPHIN para comunicar al OSIPTEL (a través del SIRT u otro mecanismo) las tarifas mayoristas que ofrecen a sus comercializadores. Se analizó normativa regulatoria vigente en materia tarifaria y documentos asociados (informe de sustento y exposición de motivos), así como también a nivel histórico (normativa derogada).', 'Soporte', 4, 6, 2, 'Completo', 2, 0, 1, 1, '2025-07-03 00:38:35', '2025-07-03 00:49:53'),
(32, '2025-07-02', 'Reunión', 13, 'Reunión solicitada por Julio Cieza, en compañía de Giovanna Piskulich y Angélica Chumpitaz, para abordar el caso advertido en Arequipa sobre uso indebido por parte de INTEGRATEL del servicio de arrendamiento de circuitos. Se brindaron alcances en base a la norma y alternativas de acción ante a OSIPTEL.', 'Soporte', 4, 4, 1, 'Completo', 6, 0, 1, 1, '2025-07-03 01:21:50', '2025-07-03 01:21:50'),
(33, '2025-07-02', 'Reunión', 19, 'Joana solicitó una reunión para revisar, de modo general, los proyectos de su área de tecnología admisibles en las categorías de innovación y cierre de brechas del SANDBOX REGULATORIO. Se precisaron las directrices legales-generales. Se añadieron consideraciones técnicas en caso se usen bandas no licenciadas. Se recomendó guiarse de la matriz resumen enviada, hacer énfasis en los impedimentos, mapear las consideraciones técnicas y guiarse del cuestionario para la admisión de cada proyecto.', 'Soporte', 6, 6, 1, 'Completo', 3, 0, 1, 6, '2025-07-03 18:03:17', '2025-07-25 21:53:44'),
(35, '2025-07-02', 'Análisis y revisión', 33, 'Sheyla nos solicitó atender la Carta N° 000518-2025-OAF-URDA/OSIPTEL mediante la cual se sigue el procedimiento de fiscalización del Aporte por Regulación de 2023. Revisamos los alcances de la carta, ordenamos y revisamos el histórico de documentos cursados, revisamos la matriz con el detallado de comprobantes de pago (enviados por su equipo de finanzas), trasladamos mediante correo el resultado de los hallazgos, elaboramos el proyecto de carta de respuesta donde además desarrollamos argumentos ', 'Soporte', 6, 6, 3, 'Completo', 3, 0, 1, 1, '2025-07-03 18:39:37', '2025-07-03 18:48:27'),
(36, '2025-07-03', 'Análisis y revisión', 14, 'Rafael solicitó atender la carta DMR-CE-1859-25 de CLARO por la negociación de interconexión para los servicios de telefonía y transporte conmutado local con protocolo SIP y SMS. Analizamos y ajustamos el proyecto de contrato de SMS. Validamos la información observada en el ANEXO I. Proyectamos una carta de respuesta donde desarrollamos comentarios generales sobre nuestra apreciación del proyecto de contrato de SMS, remitimos la información observada en el ANEXO I, y solicitamos ampliación del plazo para ambas negociaciones.', 'Soporte', 6, 6, 4, 'Completo', 4, 0, 1, 6, '2025-07-03 19:07:58', '2025-07-25 21:52:41'),
(37, '2025-07-07', 'Reunión', 7, 'Reunión solicitada por Julio Cieza y Giovanna Piskulich para brindar e intercambiar alcances sobre la posibilidad de aperturar en Perú un nuevo mercado mayorista para la compartición de infraestructura de telecomunicaciones para prestar servicios de conectividad, con presencia de Proveedores Importantes, en atención a experiencias de España. Se otorgaron alcances para estrategia a seguir respecto de posibles respuestas de TELEFÓNICA-INTEGRATEL.', 'Soporte', 4, 4, 1, 'Completo', 6, 0, 1, 4, '2025-07-04 17:48:04', '2025-07-08 19:43:57'),
(38, '2025-07-07', 'Reunión', 11, 'Reunión de seguimiento solicitada por Viviana Sánchez sobre análisis legal, contractual e internacional sobre la implementación de servicio de videollamadas en establecimientos penitenciarios. Se presentaron actualizaciones de la última versión del informe enviado previamente.', 'Soporte', 4, 4, 1, 'Completo', 8, 0, 1, 4, '2025-07-04 17:52:00', '2025-07-08 19:44:56'),
(39, '2025-07-04', 'Reunión', 19, 'Ximena (participó Sheyla y Joana) nos convocó a una reunión para elaborar una descripción de exención regulatoria para todos sus proyectos del SANDBOX. Revisamos el detalle del pedido y los alcances del formulario de presentación. Quedaron en enviarnos su PPT con el detalle de sus proyectos para formular nuestra descripción.', 'Soporte', 6, 6, 1, 'Completo', 3, 0, 1, 6, '2025-07-04 21:08:56', '2025-07-04 21:08:56'),
(40, '2025-07-04', 'Análisis y revisión', 22, 'Absolución de consulta respecto a la definición regulatoria y técnica de \"línea activa\" ello con la finalidad de responder a requerimiento planteado por RENTESEG. Implicó análisis del origen de una definición planteada por el OSIPTEL y su equivalencia a la definición de \"línea en servicio\" ', 'Soporte', 3, 3, 1, 'Completo', 5, 0, 1, 3, '2025-07-04 21:31:13', '2025-07-04 21:31:13'),
(41, '2025-07-03', 'Análisis y revisión', 23, 'Absolución de consultas formuladas respecto a la concesión, licencias y obligaciones regulatorias aplicables a Dolphin en relación a su renovación de concesión. Evaluación de estado de cumplimiento actual de DOLPHIN y de los riesgos en caso de denegatoria. ', 'Soporte', 3, 6, 2, 'Completo', 2, 0, 1, 5, '2025-07-04 21:35:05', '2025-07-07 16:37:26'),
(42, '2025-07-31', 'Horas audio', 22, 'Audio respecto a líneas activa solicitadas por RENTESEG (01-07-25 30 min) (SOCIOS) | Seguimiento de solicitud de numeración adicional para INTERMAX (03-07-25 30 min) (SOCIOS | JACY) | Revisión de carta que se trasladará al OSIPTEL para denunciar a TDP por el bloqueo de los mensajes A2P en la relación de interconexión con INTERMAX (10-07-25 30 min) (SOCIOS | JACY) | Audio de Kattya Vega con Gustavo Ramirez y Jacy Rojas sobre el inicio de operaciones de sus servicios portadores locales (20 min 22-7-25) | Absolución de consulta de Kattya Vega sobre fechas de publicación de tarifas en el SIRT (10 min 25-7-25) |Consulta sobre base legal de tarifa negociada solicitada por Ernesto Dávila atendida por Gustavo Ramirez (5min - 9-7-2025) | Rafael nos consultó sobre la viabilidad de atender la solicitud de BITEL vinculada a la habilitación de la numeración. Al respecto, trasladamos nuestra sugerencia de proceder con la habilitación, dentro del plazo regulado (14 días hábiles) y reiterar la atención a la solicitud de habilitación planteado por INTERMAX, además de la atención de otros compromisos pendientes. (30/07) (JANIRA) (20 min) | Absolución de consultas adicionales respecto de la tarifa a publicar para el inicio de operaciones del servicio portador local (20 min 25-07-2025/30-07-2025) (JROJAS/GUSTAVO)', 'Soporte', 3, 3, 3, 'Completo', 5, 0, 1, 3, '2025-07-04 21:49:08', '2025-07-31 21:22:47'),
(43, '2025-07-08', 'Análisis y revisión', 1, 'Elaboración Alerta Normativa diaria', 'Horas Internas', 5, 8, 3, 'Completo', 11, 0, 1, 2, '2025-07-04 22:05:25', '2025-08-01 17:34:07'),
(44, '2025-07-08', 'Análisis y revisión', 14, 'Revisión, análisis y ajustes al proyecto de contrato de interconexión de telefonía y tránsito con protocolo SIP, enviado por CLARO. Adicionalmente, se elaboró una carta complementaria.', 'Soporte', 6, 6, 4, 'Completo', 4, 0, 1, 6, '2025-07-04 22:07:00', '2025-07-31 15:20:44'),
(45, '2025-07-17', 'Horas audio', 19, 'A solicitud de Ximena: \r\n- Se envío del detalle del formulario para ingresar la solicitud del SANDBOX. (07.07.2025)\r\n- Nos contactamos con el MTC para solicitar información acerca del horario máximo de recepción de solicitudes (se envió correo de respaldo). (07.07.2025)\r\n- Absolvimos consulta adicional sobre el uso de firma electrónica en documentos para presentar ante el MTC. (07.07.2025)\r\n- Tuvimos llamada (con Ximena Guevara) para brindar alcances y solicitar ampliación de detalle de justificación normativa para proyectos de SANDBOX. (17.07.2025)', 'Soporte', 6, 6, 1, 'Completo', 3, 0, 1, 6, '2025-07-04 22:10:22', '2025-07-31 23:21:07'),
(46, '2025-07-07', 'Análisis y revisión', 7, 'Análisis y revisión de información de documentos de consulta pública de la Comisión Nacional de los Mercados y la Competencia (CNMC) sobre determinación de mercado relevante de infraestructura y proveedores importantes en España, solicitado por Giovanna Piskulich. Se realizó contraste de información con normativa aplicable y metodología de OSIPTEL para determinar proveedores importantes en Perú.', 'Soporte', 4, 4, 4, 'Completo', 6, 0, 1, 4, '2025-07-07 14:08:34', '2025-07-08 19:38:29'),
(47, '2025-07-24', 'Análisis y revisión', 6, 'Revisión de normativa correspondiente para brindar alcances ante comentario planteado por Alonso Mesones sobre la supuesta invalidez de contratos suscritos de abonados, por parte de OSIPTEL Arequipa, debido al uso en dichos acuerdos de firma no manuscrita (fotografía) del representante legal de CALA. Se brindaron recomendaciones adicionales respecto de acciones a seguir ante eventual cuestionamiento formal de dicha entidad.', 'Soporte', 4, 4, 2, 'Completo', 1, 0, 1, 4, '2025-07-07 14:27:53', '2025-07-24 19:01:36'),
(48, '2025-07-16', 'Análisis y revisión', 17, 'Revisión de proyecto de contrato y anexo, solicitado por Alonso Mesones, que CALA para identificar stoppers en la comercialización de su servicio de acceso a internet dedicado (B2B estándar). Se realizaron adecuaciones y modificaciones al contrato y anexo, en base a la revisión y análisis de la normativa regulatoria aplicable, documentos similares, así como se brindaron comentarios y recomendaciones adicionales. Por último, se elaboró carta de envío de contrato para conocimiento de OSIPTEL, conforme lo solicitado.', 'Soporte', 6, 4, 3, 'Completo', 1, 0, 1, 4, '2025-07-07 14:29:26', '2025-07-17 00:05:21'),
(49, '2025-07-09', 'Análisis y revisión', 6, 'Revisión solicitada por Alfredo Araujo para identificar el requerimiento de información contenido en la carta de OSIPTEL remitida a CALA respecto de la supuesta falta de elevación de una apelación ante el TRASU. Se brindaron alcances y recomendaciones a seguir, en función al análisis de las disposiciones e infracciones tipificadas en el TUO del Reglamento de Atención de Reclamos.', 'Soporte', 4, 4, 2, 'Completo', 1, 0, 1, 4, '2025-07-07 14:32:48', '2025-07-09 21:56:01'),
(51, '2025-07-08', 'Análisis y revisión', 20, 'Agendas Regulatorias del mes de JULIO para todos los clientes AMPARA', 'Horas Internas', 3, 8, 3, 'Completo', 11, 0, 1, 3, '2025-07-08 15:07:09', '2025-07-08 19:34:33'),
(52, '2025-07-07', 'Análisis y revisión', 19, 'Revisión y recopilación de normas del MTC para solicitar exención y/o flexibilización en propuestas de sandbox regulaorio, así como, elaboración de breve descripción por cada norma señalada, a solicitud de Sheyla Reyes. Asimismo, se enviaron comentarios y recomendaciones, así como un checklist detallado de documentos y formalidades a presentar por cada propuesta, ante consultas de Ximena Guevara vía correo electrónico.', 'Soporte', 6, 6, 4, 'Completo', 3, 0, 1, 6, '2025-07-08 15:25:17', '2025-07-08 22:07:36'),
(53, '2025-07-08', 'Análisis y revisión', 7, 'Elaboración de presentación a solicitud de Giovanna Piskulich conteniendo alcances y recomendaciones advertidas de revisión de documentos de consulta pública de España sobre determinación de mercado relevante de infraestructura y proveedores importantes.', 'Soporte', 4, 4, 2, 'Completo', 6, 0, 1, 4, '2025-07-08 16:04:23', '2025-07-08 19:46:24'),
(54, '2025-07-09', 'Análisis y revisión', 7, 'Elaboración de Informe Ejecutivo a manera de resumen solicitado por Giovanna Piskulich, en base a la revisión y análisis del Proyecto de Norma de Determinación de Proveedor Importante en el Mercado N°35.', 'Soporte', 4, 4, 3, 'Completo', 6, 0, 1, 4, '2025-07-08 16:13:11', '2025-07-10 00:22:39'),
(55, '2025-07-31', 'Horas audio', 1, 'Absolución de consulta de Jorge Ramirez por parte de Gustavo Ramirez sobre firma de órdenes de servicio para compartición de infraestructura de TDP (7.07.2025) // Envío de formato 26 por parte de Jacy Rojas, solicitado por Alfredo Araujo vía correo electrónico (8.07.2025) // Videollamada solicitada por Alonso Mesones con Gustavo y Jacy para revisión conjunta de proyecto de contrato para prestar internet dedicado (17.07.2025) // Adecuación final al proyecto de contrato para brindar acceso a internet dedicado (17.07.2025).', 'Soporte', 5, 4, 1, 'Completo', 1, 0, 1, 4, '2025-07-08 16:21:31', '2025-07-31 20:51:53'),
(56, '2025-07-08', 'Análisis y revisión', 1, 'Elaboración del boletín regulatorio correspondiente al mes de junio de 2025.', 'Horas Internas', 5, 2, 3, 'Completo', 11, 0, 1, 5, '2025-07-08 21:50:17', '2025-07-08 21:50:17'),
(57, '2025-07-08', 'Análisis y revisión', 13, 'Revisión de formalidades solicitada por Giovanna Piskulich para ingresas denuncias de uso indebido ante OSIPTEL. Se brindaron alcances en base a las consultas y correos enviados a OSIPTEL para la creación y envío de usuario y contraseña de PANGEACO para acceder al Sistema de Reporte de Denuncias por Uso Indebido – SISREDU, asimismo se envió proyecto de carta para solicitar dichas credenciales.', 'Soporte', 4, 4, 1, 'Completo', 6, 0, 1, 4, '2025-07-08 23:46:24', '2025-07-08 23:46:24'),
(58, '2025-07-10', 'Análisis y revisión', 15, 'Elaboración de presentación ejecutiva analizando los escenarios de interconexión derivados de la relación de acceso que INTERMAX (OMV) tiene con los OMR. Se realizó análisis normativo para la procedencia de establecer interconexiones directas , además del detalle de los escenarios de SMS A2P.', 'Soporte', 6, 3, 2, 'Completo', 5, 0, 1, 3, '2025-07-10 17:29:16', '2025-07-10 17:29:16'),
(59, '2025-07-10', 'Reunión', 15, 'Reunión para exponer los escenarios de interconexión derivados delas relaciones de acceso que tiene INTERMAX (OMV) con los OMR.', 'Soporte', 6, 3, 2, 'Completo', 5, 0, 1, 3, '2025-07-10 17:50:30', '2025-07-10 17:50:30'),
(60, '2025-07-09', 'Análisis y revisión', 33, 'Sheyla nos solicitó evaluar 6 consultas de su equipo de BDO vinculadas a la declaración/pago del Aporte por Regulación, Aporte al FITEL y TEC que se presenta este mes..\r\nEvaluamos los casos (incluyendo la revisión del convenio de liberación de interferencias con el MTC) y le trasladamos nuestras conclusiones por correo. ', 'Soporte', 6, 6, 1, 'Completo', 3, 0, 1, 6, '2025-07-10 20:47:19', '2025-07-25 21:55:26'),
(61, '2025-07-09', 'Reunión', 33, 'Sheyla nos convocó a una reunión para conversar acerca de las 6 consultas de su equipo de BDO vinculadas a la declaración/pago del Aporte por Regulación, Aporte al FITEL y TEC que se presenta este mes.', 'Soporte', 6, 6, 1, 'Completo', 3, 0, 1, 6, '2025-07-10 21:23:28', '2025-07-25 21:56:10'),
(62, '2025-07-11', 'Análisis y revisión', 14, 'Absolución de consulta respecto a la posibilidad de emplear los enlaces de interconexión ya implementados para el servicio fijo, con el propósito de soportar ahora el servicio móvil.', 'Soporte', 6, 3, 1, 'Completo', 5, 0, 1, 3, '2025-07-10 23:10:39', '2025-07-11 14:07:42'),
(63, '2025-07-25', 'Análisis y revisión', 14, 'Elaboración de carta dirigida al OSIPTEL a fin de trasladar comentarios sobre el proyecto de mandato de interconexión a fin de incluir el protocolo SIP  a la interconexión de CLARO con INTERMAX; se hizo lectura completa del informe donde se sustenta la emisión del mandato, se identifico los puntos para los cuales se precisarían comentarios.', 'Soporte', 6, 3, 2, 'Completo', 5, 0, 1, 3, '2025-07-10 23:13:06', '2025-07-25 19:33:36'),
(64, '2025-07-10', 'Análisis y revisión', 13, 'Análisis de respuesta extraoficial de OSIPTEL ante denuncia de uso indebido presentada por PANGEACO para identificar las posibles respuestas ante las dudas señaladas internamente por la autoridad sobre la información remitida en la denuncia. Asimismo, se brindaron recomendaciones y pasos a seguir ante el eventual requerimiento adicional de información que plantee OSIPTEL, en función de lo señalado en las Condiciones de Uso.', 'Soporte', 4, 4, 2, 'Completo', 6, 0, 1, 4, '2025-07-10 23:15:02', '2025-07-10 23:15:02'),
(65, '2025-07-11', 'Análisis y revisión', 26, 'Elaboración de comunicación que se debe trasladar al MTC para absolver 5 observaciones a la solicitud de asignación de recurso numérico para el Servicio de Telefonía Fija. ', 'Soporte', 3, 3, 3, 'Completo', 5, 0, 1, 3, '2025-07-10 23:19:33', '2025-07-12 00:46:04'),
(66, '2025-07-11', 'Análisis y revisión', 19, '- Revisión de la carta de la secretaría técnica de Solución de Controversias que solicita información vinculada a la solicitud de medida cautelar y admisión de la reclamación (además de validar los alcances de lo solicitado, se mapearon los actos procedimentales realizados).\r\n- Se revisó la información enviada por INTERMAX vinculado al requerimiento de información y el escrito enviado.\r\n- Se advirtieron observaciones y se envió un correo a Rafael detallando las observaciones y sugiriendo ajustes.', 'No Soporte', 6, 6, 3, 'Completo', 10, 0, 1, 6, '2025-07-11 14:31:31', '2025-07-11 22:59:16'),
(67, '2025-07-18', 'Análisis y revisión', 14, 'Rafael solicitó revisar los alcances de la denuncia a BITEL y plantear una respuesta alineada con la demora en la implementación de SMS. Se elaboró una carta donde se evidenció el vencimiento del plazo de implementación, el retraso del cronograma de implementación propuesto por BITEL, la falta de ajuste al cronograma propuesto, la demora en el envío del formato técnico, la falta de firma del acta por la reunión del 9 de mayo y se solicitó una reunión técnica para ajustar el cronograma.\r\nAdicionalmente, Rafael no consultó sobre la pertinencia de presentar una nueva denuncia por incumplimiento de plazos. Le trasladamos nuestra sugerencia de sujetarnos sobre la denuncia ya presentada y seguir recabando pruebas. Para esto, también se le precisó que nada impedía presentar una nueva denuncia. ', 'Soporte', 6, 6, 4, 'Completo', 4, 0, 1, 6, '2025-07-11 22:35:16', '2025-07-25 21:51:53'),
(68, '2025-07-15', 'Análisis y revisión', 11, 'Análisis y revisión solicitada por Viviana Sánchez, respecto de la norma que modifica el Reglamento del SISCRICO y el artículo 37 del Reglamento del Código de Ejecución Penal, a fin de identificar los impactos en el CIPS y actividades operativas de PRISONTEC en torno a la prestación de su servicio de telefonía pública en establecimientos penitenciarios. En función a dichos alcances se elaboró un informe ejecutivo, junto con comentarios y recomendaciones para el cliente.', 'Soporte', 4, 4, 4, 'Completo', 8, 0, 1, 4, '2025-07-11 22:57:41', '2025-07-21 22:24:14'),
(69, '2025-08-05', 'Análisis y revisión', 20, 'Elaboración de checkout para CALA. Se preparó matriz con estado actual de las obligaciones aplicables a cada servicio prestado por dicha empresa, según lo atendido por AMPARA; asimismo, se brindaron recomendaciones para el futuro.', 'Horas Internas', 3, 2, 3, 'Completo', 11, 0, 1, 2, '2025-07-11 23:02:11', '2025-08-08 20:33:30'),
(70, '2025-07-31', 'Horas audio', 1, 'Consulta de Julio Cieza con Juan Carlos Cornejo sobre caso de uso indebido en Arequipa (9.07.2025) // Consultas de Julio Cieza y Giovanna Piskulich con ambos socios sobre viabilidad de apertura de nuevo mercado de infraestructura con Proveedor Importante (10.07.2025) // Absolución de consultas adicionales de Angélica Chumpitaz atendidas vía correo electrónico por Jacy y Katy sobre el informe de renovación de concesiones (11.07.2025) // Audio y consultas por WhatsApp de Julio Cieza y Angélica Chumpitaz atendidas por Jacy Rojas sobre renovación de concesiones (14.07.2025) // Reunión solicitada por AMPARA con Giovanna Piskulich y Julio Cieza para abordar alcances estratégicos sobre el análisis de la determinación de Proveedores Importantes en mercados mayoristas (14.07.2025) // Recomendaciones vía llamada y correo a Julio Cieza por parte de Jacy Rojas sobre respuesta a OSIPTEL por caso de uso indebido (16.07.2025).', 'Soporte', 5, 4, 2, 'Completo', 6, 0, 1, 4, '2025-07-11 23:08:50', '2025-07-31 20:46:08'),
(71, '2025-07-21', 'Análisis y revisión', 11, 'Actualizar el informe sobre viabilidad para la implementación de servicio de videollamadas, solicitado por Viviana Sánchez, respecto de experiencias internacionales (benchmarking). Asimismo, se brindaron alcances adicionales respecto de la publicación de norma que modifica el artículo 37 del Reglamento del Código de Ejecución Penal.', 'Soporte', 4, 4, 3, 'Completo', 8, 0, 1, 4, '2025-07-11 23:11:30', '2025-07-30 19:06:17'),
(72, '2025-07-18', 'Reunión', 17, 'Reunión solicitada por Alonso Mesones para brindar alcances respecto de las obligaciones regulatorias aplicables al servicio de acceso a internet dedicado, arrendamiento de circutos y fibra oscura, que AMPARA desarrolló a profundidad en los informes remitidos a CALA con fecha 30.06.2025. Se brindaron recomendaciones para el tratamiento comercial de los servicios.', 'Soporte', 6, 4, 1, 'Completo', 1, 0, 1, 4, '2025-07-15 02:19:40', '2025-07-19 06:46:21'),
(73, '2025-07-24', 'Análisis y revisión', 8, 'Revisión de normativa regulatoria y de carácter general solicitado por Angélica Chumpitaz para la identificar y señalar las obligaciones aplicables a PANGEACO, respecto del tipo de servicios que brinda y títulos habilitantes que tiene para implementar su página web.', 'Soporte', 4, 4, 2, 'Completo', 6, 0, 1, 4, '2025-07-17 02:09:10', '2025-07-30 17:27:27'),
(74, '2025-07-24', 'Análisis y revisión', 7, 'Elaboración de informe solicitado por Julio Cieza respecto de la viabilidad para implementar un nuevo mercado mayorista de infraestructura física de telecomunicaciones. Se realizó análisis y revisión de normativa aplicable, así como de documentos asociados para plantear los principales obstáculos que dicha propuesta trae consigo: i) necesidad de modificar normativa y ii) demostrar la presencia de Proveedores Importantes en el mismo.', 'Soporte', 4, 4, 4, 'Completo', 6, 0, 1, 4, '2025-07-17 02:13:47', '2025-07-24 18:57:47'),
(75, '2025-07-25', 'Análisis y revisión', 15, 'Jaime nos solicitó atender la carta C. 000298-2025-DPRC/OSIPTEL referida a los comentarios de Integratel al proyecto de mandato de acceso. Revisión del proyecto de mandato de acceso. Análisis y comentarios a la referida carta.', 'Soporte', 6, 6, 4, 'Completo', 2, 0, 1, 6, '2025-07-17 18:11:08', '2025-07-25 23:43:23'),
(76, '2025-08-15', 'Análisis y revisión', 7, 'Elaboración de informe con análisis solicitado por Julio Cieza respecto de escenarios complementarios que el cliente planteó ante la eventual resolución de su OBC con INTEGRATEL: i) viabilidad regulatoria para continuar utilizando infraestructura de INTEGRATEL bajo Ley General de Compartición y ii) Denunciar a INTEGRATEL ante Cuerpo Colegiado de OSIPTEL por abuso de posición de dominio en la modalidad de negativa injustificada para contratar. Se incluyeron alcances, detalle de riesgos advertidos y algunas recomendaciones.', 'Soporte', 4, 4, 5, 'En revisión', 6, 0, 1, 4, '2025-07-19 06:42:50', '2025-08-16 01:17:29'),
(77, '2025-07-21', 'Análisis y revisión', 24, 'Análisis normativo y regulatorio de aplicación del pago anual del canon frente a la instalación y operación de VSAT operadas por PUNTO DE ACCESO u otros proveedores, Así como análisis de la no aplicación de dicho pago al uso de bandas libres: \r\n- Revisión de reglamento General de la ley\r\n-Revisión de antecedentes de otras empresas respecto al pago del canon\r\n\r\nAdemás, absolución de consultas respecto a responsabilidad económica del proveedor del servicio (HUGHES) frente al pago del canon anual, para lo cual se analizó el alcance de los servicios que están sujetos a dicho pago.', 'Soporte', 3, 3, 3, 'Completo', 9, 0, 1, 3, '2025-07-21 13:25:31', '2025-08-01 18:52:25'),
(78, '2025-07-21', 'Análisis y revisión', 14, 'Elaboración de comunicación para brindar respuesta a la solicitud de modificación de contrato de interconexión entre BITEL e INTERMAX, además de análisis y estrategia frente a la modificación de la clausula de mecanismos Anti-spam sustentando posición en pronunciamientos previos del OSIPTEL. Ademas de comentarios a las referencias de los principios de igualdad de acceso, predictibilidad y temporalidad', 'Soporte', 6, 3, 4, 'Completo', 5, 0, 1, 3, '2025-07-21 13:29:30', '2025-07-25 19:19:16'),
(79, '2025-07-22', 'Análisis y revisión', 12, 'Análisis y revisión de normativa aplicable para brindar alcances y estrategias a seguir solicitado por Kattya Vega para acreditar inicio de operaciones de INTERMAX respecto de su servicio portador local en su modalidad conmutado y no conmutado. Para el conmutado, se revisó y adecuó modelo de contrato y tarifa correspondientes, así como se brindó recomendaciones para su envío a OSIPTEL. Para el no conmutado, se presentaron los esquemas comerciales advertidos para la contratación de internet dedicado, así como las disposiciones para el arrendamiento de circuitos; para revisión y evaluación del cliente considerando la fecha máxima de inicio de operaciones.', 'Soporte', 4, 3, 4, 'Completo', 5, 0, 1, 3, '2025-07-21 13:31:19', '2025-07-25 19:22:20'),
(80, '2025-07-25', 'Análisis y revisión', 14, 'Elaboración de la cuarta adenda entre ENTEL con INTERMAX a fin de subsanar observaciones que plantean incluir la red del servicio portador de larga distancia internacional de INTERMAX y los escenarios de liquidación de tráfico de los servicios de cobro revertido (0800) y pago compartido (0801); además de prescindir de los escenarios de portador de larga distancia nacional y transporte conmutado prestados por ENTEL.', 'Soporte', 6, 3, 2, 'Completo', 5, 0, 1, 3, '2025-07-21 13:47:22', '2025-07-31 21:01:08'),
(81, '2025-08-05', 'Análisis y revisión', 15, 'Evaluar la presentación de una solicitud de supervisión por parte de la DFI ante la inactividad de BITEL frente a la implementación del acceso, considerando el impacto legal de ello, además de reimpulsar el enrutamiento, la habilitación del MNC y el envío de la factura', 'Soporte', 6, 3, 1, 'En proceso', 5, 0, 1, 3, '2025-07-21 13:56:23', '2025-08-04 13:49:28'),
(82, '2025-07-18', 'Reunión', 23, 'Reunión respecto al inicio de operaciones del serviico portador local conmutado y no conmutado, se absolvieron consultas respecto a publicación de tarifa, contratos y obligaciones regulatorias.', 'Soporte', 3, 3, 1, 'Completo', 5, 0, 1, 3, '2025-07-21 14:00:25', '2025-07-21 14:00:25'),
(83, '2025-07-18', 'Análisis y revisión', 19, 'Análisis y revisión de normativa del MTC solicitado por Ximena Guevara para señalar flexibilizaciones y/o exenciones que se tomarán como justificación normativa para cada proyecto de sandbox propuesto por IPT. Se elaboró cuadro indicando las referencias normativas correspondientes, de acuerdo con la información descriptiva y técnica enviada por el cliente por cada propuesta.', 'Soporte', 6, 6, 4, 'Completo', 3, 0, 1, 6, '2025-07-21 14:08:01', '2025-07-21 15:41:29'),
(84, '2025-07-21', 'Reunión', 14, 'Reunión de coordinación con el equipo de Rafael para definir la estrategia para la reunión técnica con BITEL (ajuste de cronograma de implementación de SMS). Se envió la propuesta de cronograma (FIBERMAX) por correo.', 'Soporte', 6, 6, 1, 'Completo', 4, 0, 1, 6, '2025-07-21 14:30:31', '2025-07-22 15:39:14'),
(85, '2025-07-18', 'Análisis y revisión', 17, 'Elaboración y envío al cliente de presentación conteniendo los alcances relevantes de las obligaciones aplicables a los servicios de acceso a internet dedicado, arrendamiento de circuitos y fibra oscura.', 'Soporte', 6, 4, 2, 'Completo', 1, 0, 1, 4, '2025-07-21 15:23:40', '2025-07-21 15:23:40'),
(86, '2025-08-01', 'Análisis y revisión', 1, 'xxx', 'Horas Internas', 5, 2, 1, 'Programado', 11, 0, 0, 0, '2025-07-21 18:10:33', '2025-08-08 22:43:51'),
(87, '2025-07-22', 'Reunión', 14, 'Acompañamiento a la reunión técnica con BITEL para el ajuste de cronograma de implementación de SMS. Se envió el cronograma ajustado y acordado en reunión. Se revisó y ajustó el acta.', 'Soporte', 6, 6, 1, 'Completo', 4, 0, 1, 6, '2025-07-22 15:38:29', '2025-07-31 21:55:15'),
(88, '2025-07-24', 'Análisis y revisión', 1, 'Revisión y análisis de proyecto normativo sobre inaplicación de normativa regulatoria durante la contratación con abonados corporativos para brindar comentarios a los hallazgos advertidos y remitidos por Angélica Chumpitaz y que podrían afectar a PANGEACO. Se brindaron recomendaciones en archivo remitido.', 'Soporte', 5, 4, 2, 'Completo', 6, 0, 1, 4, '2025-07-24 03:25:18', '2025-07-24 18:59:26'),
(89, '2025-07-30', 'Análisis y revisión', 3, 'Elaboración de matriz normativa solicitado por Pedro Castro conteniendo el detalle de obligaciones legales aplicables al encargado del tratamiento de datos personales, en cumplimiento de la Ley N°29733 (Ley de Protección de Datos Personales) y su reglamento (D.S. N°016-2024-JUS). Se realizó reunión previa al respecto con participación de Mapi Castañeda y Pedro Castro con fecha 22.07.2025.', 'Soporte', 5, 4, 3, 'Completo', 8, 0, 1, 4, '2025-07-24 03:29:08', '2025-07-31 21:04:39'),
(90, '2025-07-24', 'Reunión', 19, 'Reunión con Sheyla, Joana y Ximena para comentarnos sobre los resultados de su reunión con el MTC por el tema del SANDBOX REGUALTORIO. Nos solicitaron elaborar un documento con más detalles de las exoneraciones regulatorias para los 10 proyectos presentados.', 'Soporte', 6, 6, 1, 'Completo', 3, 0, 1, 6, '2025-07-24 16:57:02', '2025-07-24 16:57:02'),
(91, '2025-07-22', 'Análisis y revisión', 14, 'Exposición temática: elaboración de presentación, elaboración de examen y presentación del tema de INTERCONEXIÓN.', 'Horas Internas', 6, 2, 3, 'Completo', 11, 0, 1, 3, '2025-07-25 19:25:35', '2025-07-25 19:25:35'),
(92, '2025-07-31', 'Análisis y revisión', 20, 'Se elaboró una matriz con un listado de obligaciones de CALA para enviárselo a su salida. Se han añadido comentarios/sugerencias de AMPARA.', 'Horas Internas', 3, 2, 1, 'Completo', 11, 0, 1, 6, '2025-07-31 15:11:37', '2025-07-31 15:11:37'),
(93, '2025-07-31', 'Análisis y revisión', 14, 'Ernesto nos solicitó validar la información enviada por BITEL respecto a la implementación de la OS de telefonía con protocolo SIP. Trasladamos por correo nuestra posición sobre la propuesta económica de BITEL por correo electrónico. Rafael nos solicitó elaborar y enviarle una carta recogiendo nuestra posición. Enviamos la carta con dichas especificaciones. Posteriormente, adecuamos la carta incluyendo un pronunciamiento adicional sobre la OS enviada por BITEL. Por correo comunicamos los detalles de estas adecuaciones.', 'Soporte', 6, 6, 3, 'Completo', 4, 0, 1, 6, '2025-07-31 15:13:13', '2025-08-01 18:36:17'),
(94, '2025-08-08', 'Análisis y revisión', 14, 'Elaboración de informe ejecutivo que contempla el análisis de la Ley 32323 (que modifica la Ley del consumidor) y del proyecto normativo que para la lucha contra llamadas y SMS ilicitos; ello con la finalidad de evaluar su impacto en el servicio de SMS A2P con numeración alfanumérica provista por INTERMAX. Adicionalmente se incluyo la vinculación de la normativa citada con la solicitud de modificación de mandato trasladada por BITEL, centrando el cambio en la clausula de Mecanismos Anti-spam.', 'Soporte', 6, 3, 4, 'Completo', 5, 0, 0, 0, '2025-07-31 15:16:09', '2025-08-11 17:08:46'),
(95, '2025-07-31', 'Análisis y revisión', 12, 'Elaboración de esquema tarifario solicitado por Kattya Vega de cara al inicio de operaciones del servicio portador local no conmutado. Se revisaron esquemas tarifarios similares utilizados por otras empresas operadoras respecto del servicio de arrendamiento de circuitos.', 'Soporte', 4, 3, 2, 'Completo', 5, 0, 1, 3, '2025-07-31 15:17:26', '2025-07-31 15:17:26'),
(96, '2025-07-31', 'Análisis y revisión', 19, 'A solicitud de Rafael, seguimiento al OSIPTEL para la emisión de la resolución que se pronuncia sobre la MC (24.07)\r\nRevisión de la resolución y mapear los plazos para considerarla ejecución de la MC (30.07)\r\nCorreos de respuesta a Rafael y Kattya sobre las consultas vinculadas a la ejecución de la MC (31.07)', 'No Soporte', 6, 6, 2, 'Completo', 10, 0, 1, 6, '2025-07-31 22:29:39', '2025-07-31 22:29:39'),
(97, '2025-07-31', 'Horas audio', 12, 'Absolución de consultas adicionales de Javier Sanchez sobre tarifas de comercializadores por parte de Gustavo Ramirez para lo cual se realizaron consultas al OSIPTEL (04.07). Resolución de consultas y seguimiento a temas varios.', 'Soporte', 4, 6, 1, 'Completo', 2, 0, 1, 2, '2025-07-31 22:59:43', '2025-08-01 17:49:02'),
(98, '2025-07-30', 'Horas audio', 14, 'Consulta de Ernesto sobre el incumplimiento de BITEL respecto del nuevo cronograma de implementación de SMS. Al respecto, trasladamos nuestra sugerencia de ser persistentes con el seguimiento y propiciar el envío de comunicaciones que  permitan dejar constancia que FIBERMAX estuvo actuando con diligencia para exigir su cumplimiento. Se está a la espera de una respuesta de BITEL para elaborar una carta complementaria a la denuncia ya planteada. (30/07) (JANIRA). Seguimiento, control y absolución de consultas varias a través de llamadas y mensajes.', 'Soporte', 6, 6, 1, 'Completo', 4, 0, 1, 2, '2025-07-31 23:19:21', '2025-08-01 17:45:51'),
(99, '2025-08-01', 'Análisis y revisión', 25, 'Agendas Regulatorias del mes de AGOSTO para todos los clientes AMPARA', 'Horas Internas', 3, 2, 3, 'Completo', 11, 0, 1, 3, '2025-08-01 17:09:00', '2025-08-01 17:09:00'),
(100, '2025-07-31', 'Horas audio', 25, 'Seguimiento de actividades a seguir a fin de iniciar las operaciones del servicio de Portador Local conmutado; para lo cual se elaboró un correo trasladando mapa detallado de obligaciones, asi como cronograma con los plazos establecidos para cada actividad (15-07-25) | Absolución de consulta respecto al reporte del formato 25 SIGEP y precisión de formato a reportar al SIGIEP (25-07-25) | Respuesta a consulta sobre implicancia en pagos regulatorios  si es que el pago de Urbi a PAPSAC se realiza al término de la contraprestación (25-07-25)', 'Soporte', 3, 3, 1, 'Completo', 9, 0, 1, 3, '2025-08-01 18:40:51', '2025-08-01 18:40:51'),
(101, '2025-08-31', 'Horas audio', 14, 'LLamada de coordinación para absolver consulta de Ernesto respecto a RENTESEG (6-8-25) (JACY) (5 min)| LLamada de coordinación para absolver consulta de Ernesto respecto a los comentarios del proyecto de mandato de acceso con ENTEL (7-8-25) (JACY) (5 min) | Elaboración de correo a fin de brindar recomendaciones frente a la implementación de la conexión con RENTESEG (6-8-25) (JACY/SOCIOS) (15 min) | Elaboración de carta para brindar respuesta a BITEL respecto a sus comentarios frente a la solicitud de la clausula Antispam, adicionalmente se solicitó ampliar el plazo de negociación a fin de poder llevar a cabo una reunión (7-8-25) (SOCIOS/JACY) (30 min) | Llamada con Ernesto a fin de absolver consultas respecto a correo de BITEL en la cual se plantea dejar precedente de la no implementación del mandato de Acceso (15-8-25) (JACY) (15 min)', 'Soporte', 6, 3, 2, 'En proceso', 5, 0, 1, 3, '2025-08-04 13:44:13', '2025-08-16 00:09:04'),
(102, '2025-08-05', 'Análisis y revisión', 14, 'Elaboración Solicitud de interconexión con Telefónica como OMV Intermax', 'Soporte', 6, 3, 1, 'En proceso', 5, 0, 1, 3, '2025-08-04 13:46:16', '2025-08-04 13:46:16'),
(103, '2025-08-07', 'Reunión', 5, 'Reunión para presentar análisis de la Ley Antispam y Proyecto normativo a fin de establecer su impacto en los SMS A2P. Adicionalmente se presentaron antecedentes de la denuncia del Secreto de las Telecomunicaciones y posibles caminos a seguir', 'Soporte', 5, 3, 3, 'Completo', 5, 0, 1, 3, '2025-08-04 13:48:07', '2025-08-08 21:26:10'),
(104, '2025-08-05', 'Análisis y revisión', 15, 'Elaboración de comentarios a la carta trasladada por ENTEL con sus descargo respecto al proyecto de mandato de acceso. Se realizó una lectura integral de los descargos para su posterior análisis y sustento que refuercen nuestra posición frente a la solicitud de mandato de acceso', 'Soporte', 6, 3, 4, 'Completo', 5, 0, 1, 3, '2025-08-04 13:51:55', '2025-08-05 21:40:56'),
(105, '2025-08-04', 'Análisis y revisión', 19, 'A solicitud de Sheyla, actualizamos la matriz que contiene el detalle de la flexibilización normativa a ser considerada para efectos del SANDBOX regulatorio. Se agregó la obtención del registro de valor añadido (conmutación de datos por paquetes), cumplimiento de los indicadores de calidad (continuidad del servicio) como OIMR, homologación e internamiento de equipos y aprobación del MTC de arrendamiento de bandas. Adicionalmente, se mandó un correo con un resumen de las normas y algunas precisiones adicionales.\r\n', 'Soporte', 6, 6, 4, 'Completo', 3, 0, 1, 2, '2025-08-08 20:29:43', '2025-08-08 22:01:13'),
(106, '2025-08-07', 'Análisis y revisión', 14, 'Elaboración de carta para brindar respuesta a BITEL respecto a sus comentarios frente a la solicitud de la clausula Antispam, adicionalmente se solicitó ampliar el plazo de negociación a fin de poder llevar a cabo una reunión.', 'Soporte', 6, 3, 1, 'Completo', 5, 0, 0, 0, '2025-08-08 21:23:16', '2025-08-11 17:11:25'),
(107, '2025-08-07', 'Análisis y revisión', 19, 'Se recabó el histórico de comunicaciones entre INTERMAX-TELEFÓNICA. Se elaboró una matriz ordenando y describiendo brevemente cada comunicación. Se intercambiaron comunicaciones con INTERMAX para validar que la información esté completa.', 'No Soporte', 6, 6, 4, 'Completo', 10, 0, 1, 6, '2025-08-08 21:25:30', '2025-08-08 21:25:30'),
(108, '2025-08-08', 'Análisis y revisión', 26, 'Elaboración de PPT para la reunión donde se revisara análisis de la Ley Antispam y Proyecto normativo a fin de establecer su impacto en los SMS A2P. Adicionalmente la denuncia del Secreto de las Telecomunicaciones y posibles caminos a seguir. Lo cual partio de la elaboración de informe ejecutivo que contempla el análisis de la Ley 32323 (que modifica la Ley del consumidor) y del proyecto normativo que para la lucha contra llamadas y SMS ilicitos; ello con la finalidad de evaluar su impacto en el servicio de SMS A2P con numeración alfanumérica provista por INTERMAX. Adicionalmente se incluyo la vinculación de la normativa citada con la solicitud de modificación de mandato trasladada por BITEL, centrando el cambio en la clausula de Mecanismos Anti-spam.', 'Soporte', 3, 3, 5, 'Completo', 5, 0, 1, 3, '2025-08-08 21:32:15', '2025-08-11 17:10:52'),
(109, '2025-08-07', 'Análisis y revisión', 19, 'Análisis y revisión de proyecto de norma que modifica Reglamento de Infracciones y Sanciones de OSIPTEL. Se elaboró correo recordatorio para todos los clientes sobre la elaboración de comentarios al proyecto considerando el plazo máximo de presentación indicado en la resolución.', 'Horas Internas', 6, 2, 1, 'Completo', 11, 0, 1, 2, '2025-08-08 21:43:26', '2025-08-08 21:43:26'),
(110, '2025-08-11', 'Análisis y revisión', 25, 'A solicitud de Kazhia, atendimos su consulta relacionada con la aplicación del formato F006-CIT-1A (infraestructura). Sugerimos enviar un correo aclaratorio al MTC para que considere cumplida la obligación de presentación de dicho formato y revierta su eliminación en el SIGIEP. Para esto, además, nos comunicamos previamente con el MTC. Adicionalmente, se envió un recordatorio sobre los formatos próximos a vencer (19.08)', 'Soporte', 3, 3, 1, 'Completo', 9, 0, 1, 6, '2025-08-11 21:45:49', '2025-08-11 21:45:49'),
(111, '2025-08-12', 'Análisis y revisión', 14, 'Análisis y elaboración de carta con comentarios a los descargos realizados por CLARO al proyecto de mandato de interconexión para la incorporación del protocolo SIP. Se analizo cada uno de los comentarios trasladados por CLA y se formulo un sustento regulatorio que sustente nuestra posición frente a ello.', 'Soporte', 6, 3, 3, 'Completo', 5, 0, 1, 3, '2025-08-12 21:33:54', '2025-08-12 21:33:54');
INSERT INTO `liquidacion` (`idliquidacion`, `fecha`, `asunto`, `tema`, `motivo`, `tipohora`, `acargode`, `lider`, `cantidahoras`, `estado`, `idcontratocli`, `idpresupuesto`, `activo`, `editor`, `registrado`, `modificado`) VALUES
(112, '2025-08-12', 'Análisis y revisión', 1, 'Revisión de El Peruano y envío de la alerta normativa (10 min diarios) (50 min)', 'Horas Internas', 5, 2, 1, 'En proceso', 11, 0, 1, 2, '2025-08-12 22:09:47', '2025-08-15 21:27:47'),
(113, '2025-08-12', 'Análisis y revisión', 1, 'A solicitud de los socios, se recopiló información vinculada a INTERMAX-BITEL: Medida correctiva - Secreto de la Telecomunicaciones - Contratos/Mandatos INTERMAX-BITEL', 'Horas Internas', 5, 2, 2, 'Completo', 11, 0, 1, 2, '2025-08-12 22:31:08', '2025-08-15 21:27:21'),
(114, '2025-08-13', 'Análisis y revisión', 14, 'Análisis y revisión de carta que se debe trasladar a BITEL  fin de sustentar la posibilidad de coexistencia de implementación de protocolo SIP y SS7. Se adicionaron sustentos respecto a la neutralidad tecnológica y lo establecido explícitamente en el mandato de interconexión.', 'Soporte', 3, 3, 1, 'Completo', 5, 0, 1, 3, '2025-08-13 16:54:27', '2025-08-13 16:54:27'),
(115, '2025-08-14', 'Análisis y revisión', 1, 'Se envió un correo informativo a todos los clientes sobre la consulta temprana respecto a la necesidad de adecuar la Norma de Requerimientos de Información Periódica (NRIP) (30min). \r\n', 'Horas Internas', 6, 2, 1, 'Completo', 11, 0, 1, 6, '2025-08-14 17:30:50', '2025-08-14 17:30:50'),
(116, '2025-08-15', 'Reunión', 25, 'Reunión con Kazhia donde absolvimos consultas vinculadas a los formatos del SIGIEP y SIGEP (30min)', 'Soporte', 6, 6, 1, 'Completo', 9, 0, 1, 2, '2025-08-15 22:45:18', '2025-08-16 06:31:12'),
(117, '2025-08-15', 'Análisis y revisión', 14, 'Elaboración de solicitud de emisión de mandatos para los servicios de SMS y telefonía. Se analizaron los puntos discrepantes y antecedentes a fin de precisar de manera clara dentro de la solicitud realizada. Adicionalmente se elaboraron los anexos correspondientes de acuerdo a la norma de emisión de mandatos,', 'Soporte', 3, 3, 2, 'Completo', 4, 0, 1, 3, '2025-08-15 23:57:31', '2025-08-15 23:57:31'),
(118, '2025-08-15', 'Reunión', 15, 'Reunión para evaluar comunicación de BITEL en la que se pretende dejar precedente la no implementación del mandato de acceso', 'Soporte', 3, 3, 1, 'Completo', 5, 0, 1, 3, '2025-08-16 00:05:19', '2025-08-16 00:07:21'),
(119, '2025-08-15', 'Reunión', 7, 'Reunión solicitada por Julio Cieza para presentar alcances sobre estrategias a seguir frente a la resolución de la OBC con INTEGRATEL. Se presentaron nuevos entregables y estrategias a seguir (denuncia por barreras burocráticas).', 'Soporte', 4, 4, 1, 'Completo', 6, 0, 1, 4, '2025-08-16 01:24:51', '2025-08-16 01:25:53');

--
-- Disparadores `liquidacion`
--
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planificacion`
--

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

--
-- Volcado de datos para la tabla `planificacion`
--

INSERT INTO `planificacion` (`Idplanificacion`, `idContratoCliente`, `nombreplan`, `fechaplan`, `horasplan`, `lider`, `comentario`, `activo`, `editor`, `registrado`, `modificado`) VALUES
(1, 1, 'Plan Mensual CALA -Mayo 2025', '2025-05-01', 10, 4, 'Plan Mensual CALA -Mayo 2025', 1, 2, '2025-07-17 18:29:55', '2025-07-25 18:04:09'),
(2, 5, 'Plan Mensual INTERMAX - Mayo 2025', '2025-05-01', 24, 3, 'Plan Mensual INTERMAX - Mayo', 1, 2, '2025-07-17 23:46:09', '2025-07-25 18:03:54'),
(3, 4, 'Plan Mensual FIBERMAX - Mayo 2025', '2025-05-01', 10, 6, 'Plan Mensual FIBERMAX - Mayo', 1, 2, '2025-07-18 01:19:16', '2025-07-25 18:03:38'),
(4, 2, 'Plan Mensual DOLPHIN - Mayo 2025', '2025-05-01', 10, 6, 'Plan Mensual DOLPHIN - Mayo', 1, 2, '2025-07-18 01:23:38', '2025-07-25 18:03:17'),
(5, 6, 'Plan Mensual PANGEACO - Julio 2025', '2025-07-01', 18, 4, 'Plan Mensual PANGEACO - Mayo 2025', 1, 2, '2025-07-18 01:31:36', '2025-07-25 18:02:00'),
(6, 1, 'Plan Mensual CALA - Julio 2025', '2025-07-01', 10, 4, 'Plan Mensual CALA - Julio 2025', 1, 2, '2025-07-18 01:39:44', '2025-07-25 18:01:46'),
(7, 8, 'Plan Mensual PRISONTEC - Julio 2025', '2025-07-01', 10, 4, 'Plan Mensual PRISONTEC - Julio 2025', 1, 2, '2025-07-18 01:45:28', '2025-07-25 18:01:32'),
(8, 4, 'Plan Mensual FIBERMAX - Julio 2025', '2025-07-01', 10, 6, 'Plan Mensual FIBERMAX - Julio 2025', 1, 2, '2025-07-18 01:55:12', '2025-07-25 18:01:18'),
(9, 10, 'Plan Mensual INTERMAX - NS - Julio 2025', '2025-06-01', 3, 6, 'Plan Mensual INTERMAX - NS - Julio 2025', 1, 2, '2025-07-18 02:00:13', '2025-07-30 22:01:22'),
(10, 5, 'Plan Mensual INTERMAX - Julio 2025', '2025-07-01', 24, 3, 'Plan Mensual INTERMAX - Julio 2025', 1, 2, '2025-07-18 02:05:20', '2025-07-25 18:01:04'),
(11, 3, 'Plan Mensual IPT - Julio 2025', '2025-07-01', 18, 6, 'Plan Mensual IPT - Julio 2025', 1, 2, '2025-07-18 02:16:17', '2025-07-25 18:00:21'),
(12, 2, 'Plan Mensual DOLPHIN - Julio 2025', '2025-07-01', 10, 6, 'Plan Mensual DOLPHIN - Julio 2025', 1, 2, '2025-07-18 02:19:30', '2025-07-25 18:00:07'),
(13, 11, 'Plan Mensual AMPARA - Julio 2025', '2025-06-01', 10, 2, 'Plan Mensual AMPARA - Julio 2025', 1, 2, '2025-07-18 02:20:21', '2025-07-30 22:01:32'),
(14, 9, 'Plan Mensual PUNTO DE ACCESO - Julio 2025', '2025-07-01', 10, 3, 'Plan Mensual PUNTO DE ACCESO - Julio 2025', 1, 11, '2025-07-23 00:49:05', '2025-07-23 00:49:05'),
(15, 11, 'Plan Mensual AMPARA Agosto 25', '2025-08-01', 7, 2, 'Plan Mensual AMPARA Agosto 25', 1, 2, '2025-08-08 16:18:50', '2025-08-13 15:21:43'),
(16, 5, 'Plan Mensual INTERMAX - Agosto 2025', '2025-08-01', 24, 3, NULL, 1, 2, '2025-08-13 15:17:23', '2025-08-13 15:17:23'),
(17, 6, 'Plan Mensual PANGEACO - Agosto 2025', '2025-08-01', 18, 4, NULL, 1, 2, '2025-08-13 15:17:51', '2025-08-13 15:17:51'),
(18, 3, 'Plan Mensual IPT - Agosto 2025', '2025-08-01', 18, 6, NULL, 1, 2, '2025-08-13 15:18:28', '2025-08-13 15:18:28'),
(19, 4, 'Plan Mensual FIBERMAX - Agosto 2025', '2025-08-01', 10, 3, NULL, 1, 2, '2025-08-13 15:18:52', '2025-08-13 15:18:52'),
(20, 9, 'Plan Mensual Punto de Acceso - Agosto 2025', '2025-08-01', 14, 6, NULL, 1, 2, '2025-08-13 15:19:34', '2025-08-13 15:19:34'),
(21, 8, 'Plan Mensual PRISONTEC - Agosto 2025', '2025-08-01', 10, 4, NULL, 1, 2, '2025-08-13 15:20:00', '2025-08-13 15:20:00'),
(22, 2, 'Plan Mensual DOLPHIN - Agosto 2025', '2025-08-01', 10, 6, NULL, 1, 2, '2025-08-13 15:20:43', '2025-08-13 15:20:43'),
(23, 10, 'Plan Mensual INTERMAX - NS - Agosto 2025', '2025-08-01', 4, 6, NULL, 1, 2, '2025-08-13 15:22:06', '2025-08-13 15:22:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuestocliente`
--

CREATE TABLE `presupuestocliente` (
  `idpresupuesto` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `fechainicio` date NOT NULL,
  `fechafin` date NOT NULL,
  `monto` decimal(7,2) NOT NULL,
  `activo` int(11) NOT NULL,
  `idcliente` int(11) NOT NULL,
  `acargode` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_log`
--

CREATE TABLE `sesiones_log` (
  `id` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `session_php_id` varchar(255) DEFAULT NULL,
  `timestamp_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `timestamp_fin` timestamp NULL DEFAULT NULL,
  `duracion_segundos` int(11) DEFAULT NULL,
  `ip_address_inicio` varchar(45) DEFAULT NULL,
  `ip_address_fin` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sesiones_log`
--

INSERT INTO `sesiones_log` (`id`, `idusuario`, `session_php_id`, `timestamp_inicio`, `timestamp_fin`, `duracion_segundos`, `ip_address_inicio`, `ip_address_fin`) VALUES
(1, 3, 'nat18p1d0aqqjjrsa91i7edtbu', '2025-07-02 20:22:20', '2025-07-03 18:26:09', 79429, '181.176.210.66', '200.121.25.166'),
(2, 3, 'jckal0g5ofg7hhq67tidpasa49', '2025-07-02 20:23:14', '2025-07-03 18:26:09', 79429, '181.176.210.66', '200.121.25.166'),
(3, 3, '107mmq8aogegg5nitjsc4mn3n4', '2025-07-02 21:27:28', '2025-07-03 18:26:09', 79429, '181.176.210.66', '200.121.25.166'),
(4, 4, 'cfis47jq15sfj8vjbtmp0c4q62', '2025-07-02 21:50:19', '2025-07-03 18:26:09', 79429, '181.176.210.66', '200.121.25.166'),
(5, 4, 'c7k19g02414ig16h88lk8g1nfg', '2025-07-02 21:51:30', '2025-07-03 18:26:09', 79429, '181.176.210.66', '200.121.25.166'),
(6, 4, '9doo1kagujph1nq0km7vsk0tqa', '2025-07-02 22:42:16', '2025-07-03 18:26:09', 79429, '181.176.210.66', '200.121.25.166'),
(7, 3, 'ho1huunfgqrr58omjc3505guvo', '2025-07-02 23:17:15', '2025-07-03 18:26:09', 79429, '38.25.18.25', '200.121.25.166'),
(8, 5, 'fqfunc89f2vp8qdkdtlnl0cale', '2025-07-03 01:33:32', '2025-07-03 18:26:09', 79429, '2803:a3e0:1737:7820:d1e0:2862:d5b4:3091', '200.121.25.166'),
(9, 5, 'egcp50aiqummno15pnr5th7s99', '2025-07-03 01:38:06', '2025-07-03 18:26:09', 79429, '2803:a3e0:1737:7820:d1e0:2862:d5b4:3091', '200.121.25.166'),
(10, 3, '63nhdmlklhilkj7pigpt9as7p6', '2025-07-03 10:49:43', '2025-07-03 18:26:09', 79429, '38.25.18.25', '200.121.25.166'),
(11, 3, 'ieg5mtsu2pse3c5389dpj64426', '2025-07-03 15:39:33', '2025-07-03 18:26:09', 79429, '200.121.25.166', '200.121.25.166'),
(12, 6, 'dleqfnck0v22dhhb87s11eliqr', '2025-07-03 17:08:34', '2025-07-03 18:26:09', 79429, '38.25.53.141', '200.121.25.166'),
(13, 3, '099pf09pe2tbtct1qqqlfol3tg', '2025-07-03 18:23:42', '2025-07-03 18:26:09', 79429, '200.121.25.166', '200.121.25.166'),
(14, 6, '6qbg9pv8j59pmd6tsgkiddmprf', '2025-07-03 18:24:41', '2025-07-03 18:26:09', 79429, '200.121.25.166', '200.121.25.166'),
(15, 3, 'mkbsjagb44uskk20ks2014k368', '2025-07-03 20:42:09', NULL, NULL, '181.176.210.66', NULL),
(16, 5, '1epnp3rbvv573nu5q47osu38j9', '2025-07-03 20:44:06', NULL, NULL, '2803:a3e0:1737:7820:d1e0:2862:d5b4:3091', NULL),
(17, 5, 'bt9i7mspoao2so2ql1iucvnt8t', '2025-07-03 21:34:04', '2025-07-03 21:35:09', 65, '181.176.210.66', '181.176.210.66'),
(18, 3, 'lkc88q9s7dvr66r8ltf2lq725i', '2025-07-03 21:41:49', '2025-07-03 21:47:30', 341, '181.176.210.66', '181.176.210.66'),
(19, 4, 'o4uor50ca082ad119ftr7se9up', '2025-07-03 21:58:42', NULL, NULL, '2800:200:e240:16a8:85b6:a22d:2eeb:977', NULL),
(20, 3, '9t4pjjcirhkv4ru844ld3pkf28', '2025-07-03 22:10:27', '2025-07-03 22:24:24', 837, '181.176.210.66', '181.176.210.66'),
(21, 3, 'i7gqjrh3q3t2o6f748meandm40', '2025-07-03 22:19:52', '2025-07-04 17:40:49', 69657, '200.121.25.166', '38.25.18.25'),
(22, 3, 'uctqv8g2c29gkekgihr2cakeo9', '2025-07-03 22:27:09', '2025-07-03 22:38:41', 692, '181.176.210.66', '181.176.210.66'),
(23, 3, 'ddtdf03ptm2h3tqkg4u4njfu4f', '2025-07-03 22:41:13', '2025-07-03 22:42:53', 100, '181.176.210.66', '181.176.210.66'),
(24, 3, 'c0f3fg6qp4kkg418u21jfjjq0b', '2025-07-03 22:47:16', '2025-07-03 22:54:28', 432, '181.176.210.66', '181.176.210.66'),
(25, 3, '0ub5rtohp95fa2idanvm3ihfn9', '2025-07-04 01:51:14', '2025-07-04 10:41:12', 31798, '181.64.193.235', '181.64.193.235'),
(26, 5, 'h166uii7qrkh0cn7baasvqem51', '2025-07-04 10:41:31', '2025-07-04 11:17:31', 2160, '181.64.193.235', '181.64.193.235'),
(27, 7, 'ougt7ck5fd07bh39ijlo32eelk', '2025-07-04 13:50:20', NULL, NULL, '2800:200:e840:2432:b047:a9b2:8d42:d112', NULL),
(28, 3, 'e3ioet6l8c40jf1ubp1k7bkmbu', '2025-07-04 17:41:10', '2025-07-04 22:30:37', 17367, '38.25.18.25', '38.25.18.25'),
(29, 5, '1epnp3rbvv573nu5q47osu38j9', '2025-07-04 17:46:11', NULL, NULL, '2803:a3e0:1731:c060:90df:9fa:d0ee:c918', NULL),
(30, 4, 'o4uor50ca082ad119ftr7se9up', '2025-07-04 20:15:39', '2025-07-04 20:16:38', 59, '2800:200:e240:16a8:65b6:6d9d:6d84:e982', '2800:200:e240:16a8:65b6:6d9d:6d84:e982'),
(31, 4, 'ubp0g3osjmrtm0m31jevi9hqdi', '2025-07-04 20:21:50', NULL, NULL, '2800:200:e240:16a8:65b6:6d9d:6d84:e982', NULL),
(32, 6, 'dleqfnck0v22dhhb87s11eliqr', '2025-07-04 20:54:30', NULL, NULL, '38.25.53.141', NULL),
(33, 7, 'ougt7ck5fd07bh39ijlo32eelk', '2025-07-04 21:59:28', '2025-07-04 22:34:41', 2113, '2800:200:e840:2432:98f6:4b41:6cf8:212', '2800:200:e840:2432:5109:7671:ad6b:9796'),
(34, 3, 'lve489jo107ndqepn020cqmlbu', '2025-07-04 22:30:56', '2025-07-07 14:54:53', 231837, '38.25.18.25', '38.25.18.25'),
(35, 7, 'l0vfglufs63humi0qiohe85djm', '2025-07-04 22:35:42', NULL, NULL, '2800:200:e840:2432:5109:7671:ad6b:9796', NULL),
(36, 5, 'atgbp1a6v2nuceclh5thj5r7ep', '2025-07-05 00:14:37', NULL, NULL, '2803:a3e0:1731:c060:90df:9fa:d0ee:c918', NULL),
(37, 3, 'rh5jah2pla88as1gmu2eravin7', '2025-07-07 13:27:08', NULL, NULL, '181.64.193.235', NULL),
(38, 6, 'j4po9u6r5nl9gn488vtd56vfvo', '2025-07-07 14:43:13', NULL, NULL, '38.25.53.141', NULL),
(39, 3, '63bg2dbb155cvj9c6ps3npj9li', '2025-07-07 14:55:12', NULL, NULL, '38.25.18.25', NULL),
(40, 5, '13gpln70dpinetau8d23jr31et', '2025-07-08 11:04:52', NULL, NULL, '181.64.193.235', NULL),
(41, 3, '63bg2dbb155cvj9c6ps3npj9li', '2025-07-08 14:44:22', '2025-07-08 15:22:51', 2309, '200.121.25.166', '200.121.25.166'),
(42, 4, 'ubp0g3osjmrtm0m31jevi9hqdi', '2025-07-08 15:05:25', '2025-07-08 15:15:57', 632, '2800:200:e240:16a8:d870:e88d:c8e9:b738', '2800:200:e240:16a8:d870:e88d:c8e9:b738'),
(43, 4, '16uho45brk7ro43hgant0fufqf', '2025-07-08 15:16:22', '2025-07-08 15:18:29', 127, '2800:200:e240:16a8:d870:e88d:c8e9:b738', '2800:200:e240:16a8:d870:e88d:c8e9:b738'),
(44, 3, 'k972fl9vb5e9plh99rplbf566a', '2025-07-08 15:23:28', '2025-07-09 18:23:02', 97174, '200.121.25.166', '2001:1388:18:317d:2c4f:8041:e385:3cf'),
(45, 6, 'j4po9u6r5nl9gn488vtd56vfvo', '2025-07-08 15:23:36', NULL, NULL, '38.25.53.141', NULL),
(46, 4, '28tnoqulefl955a77i5n0go33g', '2025-07-08 15:24:56', NULL, NULL, '2800:200:e240:16a8:d870:e88d:c8e9:b738', NULL),
(47, 5, 'atgbp1a6v2nuceclh5thj5r7ep', '2025-07-08 15:28:56', '2025-07-08 20:44:03', 18907, '2803:a3e0:1731:47a0:68dd:2135:c5fd:ac97', '2803:a3e0:1731:47a0:68dd:2135:c5fd:ac97'),
(48, 5, 'mg5se86bp6kj5vihhm58rf7p0f', '2025-07-08 16:02:43', '2025-07-08 16:03:44', 61, '181.176.210.66', '181.176.210.66'),
(49, 5, 'ai2b0p3h90bd15l33q33kqvp2h', '2025-07-08 16:04:06', '2025-07-08 16:05:28', 82, '181.176.210.66', '181.176.210.66'),
(50, 5, '4ul8v07ps0trcc6u1og5q901n2', '2025-07-08 16:05:46', '2025-07-08 16:05:51', 5, '181.176.210.66', '181.176.210.66'),
(51, 3, 'uaqgtssddlsgc0k5gq6v7dukne', '2025-07-08 16:06:17', NULL, NULL, '181.176.210.66', NULL),
(52, 2, '5v9gdevsf13kc579b6op50ssoi', '2025-07-08 17:04:23', NULL, NULL, '45.231.74.210', NULL),
(53, 7, 'l0vfglufs63humi0qiohe85djm', '2025-07-08 17:07:22', '2025-07-08 18:18:54', 4292, '2800:200:e840:2432:4914:d5ab:4151:7b6b', '2800:200:e840:2432:4914:d5ab:4151:7b6b'),
(54, 7, 'gomanh82bhpm29lq3cgdkolkhd', '2025-07-08 18:19:10', '2025-07-08 21:45:00', 12350, '2800:200:e840:2432:4914:d5ab:4151:7b6b', '2800:200:e840:2432:8999:29fd:c423:2dd1'),
(55, 3, '84v9r7n6vntu4smk5d9i5rbsaq', '2025-07-08 20:16:13', NULL, NULL, '181.176.210.66', NULL),
(56, 5, '3radheq4a6bf08adnm6nkbujq9', '2025-07-08 20:44:18', '2025-07-08 20:44:30', 12, '2803:a3e0:1731:47a0:68dd:2135:c5fd:ac97', '2803:a3e0:1731:47a0:68dd:2135:c5fd:ac97'),
(57, 5, 'q4tcc45dmh1ob5ilovgk4uv3ln', '2025-07-08 20:44:47', NULL, NULL, '2803:a3e0:1731:47a0:68dd:2135:c5fd:ac97', NULL),
(58, 7, 'jnej5bh36ue6skt1jt0qjfeo9q', '2025-07-08 21:45:13', '2025-07-08 21:55:46', 633, '2800:200:e840:2432:8999:29fd:c423:2dd1', '2800:200:e840:2432:8999:29fd:c423:2dd1'),
(59, 7, '7dhv9m055lu5d5srpakkhkgo9c', '2025-07-08 21:56:56', NULL, NULL, '2800:200:e840:2432:8999:29fd:c423:2dd1', NULL),
(60, 7, '5hi0dk3869e4eld2p9a4rp4h4d', '2025-07-09 13:49:18', '2025-07-09 14:08:18', 1140, '2001:1388:18:317d:9c11:b788:3fad:7092', '2001:1388:18:317d:9c11:b788:3fad:7092'),
(61, 7, '4l7drr92elk9bc39flr1orhobc', '2025-07-09 14:09:07', NULL, NULL, '2001:1388:18:317d:9c11:b788:3fad:7092', NULL),
(62, 6, 'fd60b5vs000nqk0olk5r17hdd8', '2025-07-09 14:23:14', NULL, NULL, '2001:1388:18:317d:d3d:b024:faa8:fc43', NULL),
(63, 3, '4mgcfa7cejj7htlvlvbg2oseu5', '2025-07-09 18:23:15', NULL, NULL, '2001:1388:18:317d:2c4f:8041:e385:3cf', NULL),
(64, 5, 'ev7q9n9l26eko3asij3aqker26', '2025-07-09 21:40:22', NULL, NULL, '2803:a3e0:1731:47a0:20f6:e6b0:3ef3:51d9', NULL),
(65, 6, 'l4p63e5kq0jps599vamsetc61k', '2025-07-10 20:40:15', NULL, NULL, '38.25.53.141', NULL),
(66, 6, 'p3bp5q6ohkvid2jvjbsak6o1kj', '2025-07-10 21:34:39', NULL, NULL, '38.25.53.141', NULL),
(67, 6, 'nhfq4ueujjgmfdr8m4cpbt3ppc', '2025-07-11 14:25:16', NULL, NULL, '38.25.53.141', NULL),
(68, 3, 'p6n5oophhfuc7unk33eajipesa', '2025-07-14 09:54:01', NULL, NULL, '181.64.193.235', NULL),
(69, 6, '759che4leilq3c3lt8v524h617', '2025-07-14 14:11:26', NULL, NULL, '2001:1388:6563:471b:a089:f510:f9d5:cd7f', NULL),
(70, 5, 'eo10185jre1tdov41k9u5g2im9', '2025-07-16 14:00:35', '2025-07-17 02:16:43', 44168, '2001:1388:18:317d:8947:e6f4:c985:a48b', '2803:a3e0:1732:2830:357c:562b:54f9:22e7'),
(71, 7, 'u9jd80urpihi501hjjlv7m22j8', '2025-07-17 13:41:34', NULL, NULL, '2800:200:e840:2432:7533:6395:b08d:5ae7', NULL),
(72, 5, 'a9k6s4flbkhk8ca4n1vq6p5h9f', '2025-07-17 14:31:42', NULL, NULL, '2803:a3e0:1732:2830:357c:562b:54f9:22e7', NULL),
(73, 6, 'solt6g6ndf1pmtf0pd60v9hsge', '2025-07-17 15:38:11', NULL, NULL, '2001:1388:6563:6673:51ba:4b6:187:7bf5', NULL),
(74, 3, 'jcftoujuhhek6h0h0pldrdmusk', '2025-07-18 05:31:37', '2025-07-18 12:29:56', 25099, '181.64.193.222', '181.64.193.222'),
(75, 3, 'oej44u975shb7nvilcu2eoi1lj', '2025-07-18 12:46:59', NULL, NULL, '181.176.83.90', NULL),
(76, 6, '1e3f0rs8b9q1ugmos2us70o580', '2025-07-18 18:32:53', NULL, NULL, '38.25.53.141', NULL),
(77, 6, 'ln5o0cvjn5vlnmudbq2d81rdm8', '2025-07-18 18:54:14', NULL, NULL, '38.25.53.141', NULL),
(78, 3, 'rhc04e2m51p92r312hpvp99gfm', '2025-07-18 20:27:50', NULL, NULL, '181.176.83.90', NULL),
(79, 3, 'rhc04e2m51p92r312hpvp99gfm', '2025-07-18 20:27:51', NULL, NULL, '181.176.83.90', NULL),
(80, 3, '7env88gartohk6qt8u9m8jenhe', '2025-07-19 15:00:12', '2025-07-22 23:38:06', 290274, '181.64.193.222', '181.64.193.222'),
(81, 3, '7kmbr5bt8hphcep7n982m200fc', '2025-07-19 23:01:10', NULL, NULL, '181.64.193.222', NULL),
(82, 3, '7kmbr5bt8hphcep7n982m200fc', '2025-07-19 23:01:11', NULL, NULL, '181.64.193.222', NULL),
(83, 6, '6imbi6mjok1j453uc9fblrs17p', '2025-07-21 13:30:26', NULL, NULL, '38.25.53.141', NULL),
(84, 5, 'tjvdfpq6fg7b277usllqfi918n', '2025-07-21 14:24:57', NULL, NULL, '2803:a3e0:1732:2830:8a:b90e:5099:cd14', NULL),
(85, 4, 'usm629naibbk1n2j6sdrloqp9b', '2025-07-21 18:06:38', NULL, NULL, '2800:200:e240:16a8:dd22:1c2f:14e6:2a8f', NULL),
(86, 4, 'j3h6716t290jbcp1mssbjn7k3t', '2025-07-21 18:06:38', NULL, NULL, '2800:200:e240:16a8:dd22:1c2f:14e6:2a8f', NULL),
(87, 4, 'rjo075ql6gve0ebom5pld3fsd3', '2025-07-21 18:06:39', NULL, NULL, '2800:200:e240:16a8:dd22:1c2f:14e6:2a8f', NULL),
(88, 4, 'bm0o9as2qnajq2505guljl852c', '2025-07-21 18:07:54', NULL, NULL, '2800:200:e240:16a8:dd22:1c2f:14e6:2a8f', NULL),
(89, 6, '18maalgp2rar71v7j6ttpds6q1', '2025-07-21 21:09:02', NULL, NULL, '38.25.53.141', NULL),
(90, 3, '4mgcfa7cejj7htlvlvbg2oseu5', '2025-07-22 13:44:30', NULL, NULL, '2001:1388:18:317d:841f:bf9c:b558:1bde', NULL),
(91, 6, 'k0un6kfu4i51rmgp1lhpnrpelh', '2025-07-22 15:07:12', NULL, NULL, '2001:1388:18:317d:6437:5e7:eec4:7911', NULL),
(92, 3, 'eikaoqpse3e6upr1b7gpkr6vcb', '2025-07-22 23:38:39', '2025-07-23 00:04:57', 1578, '181.64.193.222', '181.64.193.222'),
(93, 8, 'u12vheav840qarjaf848ovadc8', '2025-07-23 00:05:25', NULL, NULL, '181.64.193.222', NULL),
(94, 5, 'cin5k8bl43lcpara4dj3n8252v', '2025-07-23 00:15:08', '2025-07-23 05:58:45', 20617, '181.64.193.222', '181.64.193.222'),
(95, 2, '8onav6h2i5a16e1ni2q0t5kd39', '2025-07-24 14:29:01', '2025-07-24 14:40:20', 679, '190.232.101.219', '190.232.101.219'),
(96, 6, 'rs6agruio2l9jrlr7mq9acr1of', '2025-07-25 21:40:19', NULL, NULL, '38.25.53.141', NULL),
(97, 6, 'g4vaf7d9ofn8s07nr6ujslf8f1', '2025-07-25 23:36:14', NULL, NULL, '38.25.53.141', NULL),
(98, 7, 'b43hnmvq0tssgmhpn1rj4f7f37', '2025-07-30 12:48:58', NULL, NULL, '179.6.14.108', NULL),
(99, 4, 'g7lq3grlk36skuke36oui158go', '2025-07-30 13:40:15', NULL, NULL, '2800:200:e240:16a8:94d8:ff34:ec88:e5ab', NULL),
(100, 5, '01i8dgmsrliurmnbllhio6nvgu', '2025-07-30 15:16:57', '2025-07-31 05:36:32', 51575, '2803:a3e0:1733:1ec0:4477:93e0:17f1:2128', '2803:a3e0:1733:1ec0:54dd:6641:d5e0:8528'),
(101, 6, '3r1mtpa6vsi4fishgdjbgo7tas', '2025-07-30 21:57:06', NULL, NULL, '38.25.53.141', NULL),
(102, 5, 'rvtf1unknmi82fltq17qhgoseb', '2025-07-31 14:58:25', '2025-07-31 23:07:43', 29358, '2803:a3e0:1731:7070:4cf1:4652:a855:c27f', '2803:a3e0:1731:7070:4cf1:4652:a855:c27f'),
(103, 6, '0v8e9cirhq9pjn7hm3e2rmam3p', '2025-07-31 15:08:29', NULL, NULL, '38.25.53.141', NULL),
(104, 4, 'g7lq3grlk36skuke36oui158go', '2025-07-31 15:15:19', NULL, NULL, '2800:200:e240:16a8:75ff:fe13:a0c5:402', NULL),
(105, 3, 'u12vheav840qarjaf848ovadc8', '2025-07-31 20:18:23', NULL, NULL, '181.64.193.222', NULL),
(106, 3, '4mgcfa7cejj7htlvlvbg2oseu5', '2025-07-31 20:53:42', NULL, NULL, '38.25.18.25', NULL),
(107, 6, 'lmndc80gfonhai8epj2s6id757', '2025-07-31 21:16:19', NULL, NULL, '38.25.53.141', NULL),
(108, 7, 'b43hnmvq0tssgmhpn1rj4f7f37', '2025-07-31 21:28:17', NULL, NULL, '2800:200:e840:2432:99a5:7600:fe01:964b', NULL),
(109, 3, 'sl1gpkmbkqk00q663l4t8l0sin', '2025-07-31 21:35:54', NULL, NULL, '2800:4b0:4501:25a0:1:0:6e5e:901f', NULL),
(110, 3, 'sl1gpkmbkqk00q663l4t8l0sin', '2025-07-31 21:35:55', NULL, NULL, '2800:4b0:4501:25a0:1:0:6e5e:901f', NULL),
(111, 5, 'j363tmpgb50m696cibearn4bbj', '2025-08-01 14:50:04', '2025-08-01 23:24:32', 30868, '2803:a3e0:1730:35c0:9f:80d6:c641:2b83', '2803:a3e0:1730:35c0:9f:80d6:c641:2b83'),
(112, 6, '27hee93m7j7u0ikbvtl6qekr4n', '2025-08-01 18:29:52', NULL, NULL, '38.25.53.141', NULL),
(113, 5, 'l185gdminbu2cc4o8t4ncduflq', '2025-08-01 23:25:39', '2025-08-01 23:26:33', 54, '2803:a3e0:1730:35c0:9f:80d6:c641:2b83', '2803:a3e0:1730:35c0:9f:80d6:c641:2b83'),
(114, 4, 'g7lq3grlk36skuke36oui158go', '2025-08-04 13:39:54', NULL, NULL, '2800:200:e240:16a8:9c59:4778:8fb:9567', NULL),
(115, 5, 'njrsuttr347vr4io4ndd4511vc', '2025-08-04 14:01:52', NULL, NULL, '2803:a3e0:1730:35c0:71dc:6eb5:bfb6:83e0', NULL),
(116, 3, 'ao5absl6asocoti89lkgkqt862', '2025-08-04 14:53:57', NULL, NULL, '38.25.18.25', NULL),
(117, 3, 'ao5absl6asocoti89lkgkqt862', '2025-08-04 14:53:58', NULL, NULL, '38.25.18.25', NULL),
(118, 3, 'ao5absl6asocoti89lkgkqt862', '2025-08-04 14:53:59', NULL, NULL, '38.25.18.25', NULL),
(119, 3, 'nsmaalb2br2isu4p0608o75unk', '2025-08-04 16:17:44', NULL, NULL, '34.176.44.62', NULL),
(120, 5, 'n5lst2nsnbhuiej8spgacp15t5', '2025-08-05 15:06:46', NULL, NULL, '2803:a3e0:1730:35c0:71dc:6eb5:bfb6:83e0', NULL),
(121, 4, 'phtas3q6p5juk06i57t59ed5k9', '2025-08-05 15:12:06', NULL, NULL, '2800:200:e240:16a8:69fa:8f56:cd8a:4f61', NULL),
(122, 3, '4mgcfa7cejj7htlvlvbg2oseu5', '2025-08-07 15:04:18', '2025-08-13 12:34:00', 509382, '2001:1388:18:f5a:3521:72bd:f075:346a', '2800:4b0:4202:d226:acac:2d80:c328:5d4b'),
(123, 3, 'huurdq9e0pl6a09rop1jkb9oph', '2025-08-08 07:39:15', NULL, NULL, '181.64.193.222', NULL),
(124, 4, 'eaesjcct11hckk6cheq4ff0bon', '2025-08-08 15:06:19', NULL, NULL, '2800:200:e240:16a8:e9e1:2b81:45d0:e97c', NULL),
(125, 5, '22k7hhjsir8ovvme366vq13tee', '2025-08-08 15:53:28', '2025-08-08 22:43:05', 24577, '2803:a3e0:1730:7df0:1d3:bae2:d136:ada9', '2803:a3e0:1730:7df0:1d3:bae2:d136:ada9'),
(126, 6, 'docqr33he2duu43047cdakcc75', '2025-08-08 20:12:39', NULL, NULL, '38.25.53.141', NULL),
(127, 5, 'serp764m5j5d59r1n6va93aqjs', '2025-08-08 22:43:21', '2025-08-08 22:45:40', 139, '2803:a3e0:1730:7df0:1d3:bae2:d136:ada9', '2803:a3e0:1730:7df0:1d3:bae2:d136:ada9'),
(128, 5, 'cp4rmm31o5kej9ueedp08s6j6v', '2025-08-11 14:20:43', NULL, NULL, '2803:a3e0:1730:7df0:b767:41b8:4ead:538b', NULL),
(129, 6, 'ji4c9p125do5856nlruo26g3a4', '2025-08-11 21:31:29', NULL, NULL, '38.25.53.141', NULL),
(130, 3, 'l7q87o8e55gjn1ut475iltponl', '2025-08-12 14:49:29', NULL, NULL, '190.235.170.136', NULL),
(131, 5, 'd0mvuiq0h8t8j8a0ir37fs11k4', '2025-08-12 15:32:07', NULL, NULL, '2803:a3e0:1732:b50:24:2ce2:1164:e7d1', NULL),
(132, 6, 'ivbpbbno39c60p7mrhca5i1ek7', '2025-08-12 21:11:13', NULL, NULL, '38.25.53.141', NULL),
(133, 3, '2k845rsbbvnu5ir7s70pn2jmu4', '2025-08-13 11:39:05', NULL, NULL, '190.235.170.136', NULL),
(134, 3, '7ruug20v0f9ngl7enq1gc2387r', '2025-08-13 12:34:45', '2025-08-15 18:08:40', 192835, '2800:4b0:4202:d226:acac:2d80:c328:5d4b', '38.25.25.89'),
(135, 6, 'k1tcs0r6mll701mvpnrg3t9ad1', '2025-08-13 17:00:49', NULL, NULL, '38.43.130.74', NULL),
(136, 3, '5lj0989r3k8n3cfqhhob3j1knc', '2025-08-13 22:33:04', NULL, NULL, '181.176.210.66', NULL),
(137, 3, 'g1mrs0t8nmash5f57ifhinh8j5', '2025-08-15 18:09:03', NULL, NULL, '38.25.25.89', NULL),
(138, 6, '0t3c77d5eo01gss3akk7oscvvo', '2025-08-15 22:37:11', NULL, NULL, '38.25.53.141', NULL),
(139, 5, '206q4gk1h3q3rm90vgas78np3e', '2025-08-16 01:07:26', '2025-08-16 01:34:42', 1636, '2803:a3e0:1732:b50:11d5:1b55:c9f9:24e0', '2803:a3e0:1732:b50:11d5:1b55:c9f9:24e0'),
(140, 3, 'vnfqnf97hku14q7j7noqljf35p', '2025-08-16 06:30:43', NULL, NULL, '190.235.170.35', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tema`
--

CREATE TABLE `tema` (
  `idtema` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `idencargado` int(11) DEFAULT NULL,
  `comentario` varchar(500) NOT NULL,
  `activo` int(11) NOT NULL DEFAULT 1,
  `editor` int(11) NOT NULL DEFAULT 1,
  `registrado` int(11) NOT NULL DEFAULT current_timestamp(),
  `modificado` int(11) NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tema`
--

INSERT INTO `tema` (`idtema`, `descripcion`, `idencargado`, `comentario`, `activo`, `editor`, `registrado`, `modificado`) VALUES
(1, 'Asesoría Legal', 6, '', 1, 2, 2147483647, 2147483647),
(2, 'Agenda Regulatoria - Compliance', 6, '', 1, 2, 2147483647, 2147483647),
(3, 'Protección de Datos Personales', 6, '', 1, 2, 2147483647, 2147483647),
(4, 'Alerta Normativa', 6, '', 1, 2, 2147483647, 2147483647),
(5, 'Secreto de las Telecomunicaciones', 6, '', 1, 2, 2147483647, 2147483647),
(6, 'Condiciones de Uso | Reclamos', 4, '', 1, 1, 2147483647, 2147483647),
(7, 'Compartición de Infraestructura', 4, '', 1, 2, 2147483647, 2147483647),
(8, 'Página Web | Indicadores de Calidad de Usuario', 4, '', 1, 1, 2147483647, 2147483647),
(9, 'Contratación B2C', 4, '', 1, 1, 2147483647, 2147483647),
(10, 'Portabilidad', 4, '', 1, 1, 2147483647, 2147483647),
(11, 'CIPS | Marco Normativo de Establecimientos Penitenciarios', 4, '', 1, 1, 2147483647, 2147483647),
(12, 'Tarifas', 4, '', 1, 1, 2147483647, 2147483647),
(13, 'Uso Indebido | Uso Prohibido', 4, '', 1, 1, 2147483647, 2147483647),
(14, 'Interconexión', 3, '', 1, 2, 2147483647, 2147483647),
(15, 'OMV', 3, '', 1, 2, 2147483647, 2147483647),
(16, 'OIMR | PIP', 6, '', 1, 2, 2147483647, 2147483647),
(17, 'Contratación B2B y Mayorista', 4, '', 1, 2, 2147483647, 2147483647),
(18, 'Obligaciones Periódicas Contractuales (Plan de Cobertura | Contratos y Mandatos de Interconexión | Acceso)', 6, '', 1, 2, 2147483647, 2147483647),
(19, 'Procedimientos Administrativos Sancionador', 4, '', 1, 2, 2147483647, 2147483647),
(20, 'Clasificación de Servicios', 3, '', 1, 2, 2147483647, 2147483647),
(21, 'Reglamento de Indicadores de Calidad (Velocidad Mínima)', 3, '', 1, 2, 2147483647, 2147483647),
(22, 'RENTESEG y Normas de Emergencia (SISMATE | RECSE)', 3, '', 1, 2, 2147483647, 2147483647),
(23, 'Títulos Habilitantes', 3, '', 1, 1, 2147483647, 2147483647),
(24, 'PNAF (Espectro)', 3, '', 1, 2, 2147483647, 2147483647),
(25, 'Obligaciones Periódicas Normativas (NRIP | NRIS | Aportes | Secreto | Renteseg | Numeración | Canon)', 6, '', 1, 2, 2147483647, 2147483647),
(26, 'Normas de Numeración y Señalización', 3, '', 1, 2, 2147483647, 2147483647),
(27, 'Interrupciones y Devoluciones', 3, '', 1, 2, 2147483647, 2147483647),
(28, 'Neutralidad de Red', 3, '', 1, 2, 2147483647, 2147483647),
(29, 'Homologación e Internamiento de Equipos', 3, '', 1, 1, 2147483647, 2147483647),
(30, 'Obligaciones Proveedor de Capacidad Satelital (KINEIS)', 6, '', 1, 2, 2147483647, 2147483647),
(31, 'Boletín Regulatorio', 6, '', 1, 2, 2147483647, 2147483647),
(32, 'Compliance Regulatorio', 6, '', 1, 2, 2147483647, 2147483647),
(33, 'Obligaciones Económicas', 6, '', 1, 1, 2147483647, 2147483647),
(34, 'Normas Ambientales (SEIA)', 4, '', 1, 2, 2147483647, 2147483647),
(35, 'Norma de Metodología de Cálculo de Sanciones', 4, '', 1, 2, 2147483647, 2147483647),
(36, 'Mapa de Obligaciones', 6, '', 1, 2, 2147483647, 2147483647),
(37, 'RNI', 3, '', 1, 2, 2147483647, 2147483647);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trigger_debug_log`
--

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

--
-- Volcado de datos para la tabla `trigger_debug_log`
--

INSERT INTO `trigger_debug_log` (`log_id`, `trigger_name`, `log_timestamp`, `message`, `idliquidacion_val`, `iddetalle_val`, `estado_val`, `planificacion_id_val`, `distribucionhora_count`, `insert_attempted`) VALUES
(1, 'insert', '2025-07-19 06:42:50', 'Trigger START', 76, NULL, 'Completo', NULL, NULL, NULL),
(2, 'insert', '2025-07-19 06:42:50', 'After Planificacion SELECT', 76, NULL, NULL, 6, NULL, NULL),
(3, 'insert', '2025-07-19 06:42:50', 'After detalles_planificacion INSERT', 76, 128, 'Completo', NULL, NULL, NULL),
(4, 'insert', '2025-07-19 06:42:50', 'CONDITION MET for distrib_planif', 76, 128, 'Completo', NULL, NULL, 0),
(5, 'insert', '2025-07-19 06:42:50', 'Count from distribucionhora', 76, NULL, NULL, NULL, 0, NULL),
(6, 'insert', '2025-07-19 06:42:50', 'Skipped INSERT (no rows in distribucionhora)', 76, NULL, NULL, NULL, 0, 0),
(7, 'insert', '2025-07-19 06:42:50', 'Trigger END', 76, NULL, NULL, NULL, NULL, NULL),
(8, 'insert', '2025-07-21 13:25:31', 'Trigger START', 77, NULL, 'En proceso', NULL, NULL, NULL),
(9, 'insert', '2025-07-21 13:25:31', 'After Planificacion SELECT', 77, NULL, NULL, NULL, NULL, NULL),
(10, 'insert', '2025-07-21 13:25:31', 'v_idplanificacion IS NULL', 77, NULL, NULL, NULL, NULL, 0),
(11, 'insert', '2025-07-21 13:25:31', 'Trigger END', 77, NULL, NULL, NULL, NULL, NULL),
(12, 'insert', '2025-07-21 13:29:30', 'Trigger START', 78, NULL, 'En revisión', NULL, NULL, NULL),
(13, 'insert', '2025-07-21 13:29:30', 'After Planificacion SELECT', 78, NULL, NULL, 10, NULL, NULL),
(14, 'insert', '2025-07-21 13:29:30', 'After detalles_planificacion INSERT', 78, 129, 'En revisión', NULL, NULL, NULL),
(15, 'insert', '2025-07-21 13:29:30', 'CONDITION NOT MET for distrib_planif', 78, 129, 'En revisión', NULL, NULL, 0),
(16, 'insert', '2025-07-21 13:29:30', 'Trigger END', 78, NULL, NULL, NULL, NULL, NULL),
(17, 'insert', '2025-07-21 13:31:19', 'Trigger START', 79, NULL, 'Programado', NULL, NULL, NULL),
(18, 'insert', '2025-07-21 13:31:19', 'After Planificacion SELECT', 79, NULL, NULL, 10, NULL, NULL),
(19, 'insert', '2025-07-21 13:31:19', 'After detalles_planificacion INSERT', 79, 130, 'Programado', NULL, NULL, NULL),
(20, 'insert', '2025-07-21 13:31:19', 'CONDITION NOT MET for distrib_planif', 79, 130, 'Programado', NULL, NULL, 0),
(21, 'insert', '2025-07-21 13:31:19', 'Trigger END', 79, NULL, NULL, NULL, NULL, NULL),
(22, 'insert', '2025-07-21 13:47:22', 'Trigger START', 80, NULL, 'Programado', NULL, NULL, NULL),
(23, 'insert', '2025-07-21 13:47:22', 'After Planificacion SELECT', 80, NULL, NULL, 10, NULL, NULL),
(24, 'insert', '2025-07-21 13:47:22', 'After detalles_planificacion INSERT', 80, 131, 'Programado', NULL, NULL, NULL),
(25, 'insert', '2025-07-21 13:47:22', 'CONDITION NOT MET for distrib_planif', 80, 131, 'Programado', NULL, NULL, 0),
(26, 'insert', '2025-07-21 13:47:22', 'Trigger END', 80, NULL, NULL, NULL, NULL, NULL),
(27, 'insert', '2025-07-21 13:56:23', 'Trigger START', 81, NULL, 'Programado', NULL, NULL, NULL),
(28, 'insert', '2025-07-21 13:56:23', 'After Planificacion SELECT', 81, NULL, NULL, 10, NULL, NULL),
(29, 'insert', '2025-07-21 13:56:23', 'After detalles_planificacion INSERT', 81, 132, 'Programado', NULL, NULL, NULL),
(30, 'insert', '2025-07-21 13:56:23', 'CONDITION NOT MET for distrib_planif', 81, 132, 'Programado', NULL, NULL, 0),
(31, 'insert', '2025-07-21 13:56:23', 'Trigger END', 81, NULL, NULL, NULL, NULL, NULL),
(32, 'insert', '2025-07-21 14:00:25', 'Trigger START', 82, NULL, 'Completo', NULL, NULL, NULL),
(33, 'insert', '2025-07-21 14:00:25', 'After Planificacion SELECT', 82, NULL, NULL, 10, NULL, NULL),
(34, 'insert', '2025-07-21 14:00:25', 'After detalles_planificacion INSERT', 82, 133, 'Completo', NULL, NULL, NULL),
(35, 'insert', '2025-07-21 14:00:25', 'CONDITION MET for distrib_planif', 82, 133, 'Completo', NULL, NULL, 0),
(36, 'insert', '2025-07-21 14:00:25', 'Count from distribucionhora', 82, NULL, NULL, NULL, 0, NULL),
(37, 'insert', '2025-07-21 14:00:25', 'Skipped INSERT (no rows in distribucionhora)', 82, NULL, NULL, NULL, 0, 0),
(38, 'insert', '2025-07-21 14:00:25', 'Trigger END', 82, NULL, NULL, NULL, NULL, NULL),
(39, 'insert', '2025-07-21 14:08:01', 'Trigger START', 83, NULL, 'Programado', NULL, NULL, NULL),
(40, 'insert', '2025-07-21 14:08:01', 'After Planificacion SELECT', 83, NULL, NULL, 11, NULL, NULL),
(41, 'insert', '2025-07-21 14:08:01', 'After detalles_planificacion INSERT', 83, 134, 'Programado', NULL, NULL, NULL),
(42, 'insert', '2025-07-21 14:08:01', 'CONDITION NOT MET for distrib_planif', 83, 134, 'Programado', NULL, NULL, 0),
(43, 'insert', '2025-07-21 14:08:01', 'Trigger END', 83, NULL, NULL, NULL, NULL, NULL),
(44, 'insert', '2025-07-21 14:30:31', 'Trigger START', 84, NULL, 'Programado', NULL, NULL, NULL),
(45, 'insert', '2025-07-21 14:30:31', 'After Planificacion SELECT', 84, NULL, NULL, 8, NULL, NULL),
(46, 'insert', '2025-07-21 14:30:31', 'After detalles_planificacion INSERT', 84, 135, 'Programado', NULL, NULL, NULL),
(47, 'insert', '2025-07-21 14:30:31', 'CONDITION NOT MET for distrib_planif', 84, 135, 'Programado', NULL, NULL, 0),
(48, 'insert', '2025-07-21 14:30:31', 'Trigger END', 84, NULL, NULL, NULL, NULL, NULL),
(49, 'insert', '2025-07-21 15:23:40', 'Trigger START', 85, NULL, 'Completo', NULL, NULL, NULL),
(50, 'insert', '2025-07-21 15:23:40', 'After Planificacion SELECT', 85, NULL, NULL, 6, NULL, NULL),
(51, 'insert', '2025-07-21 15:23:40', 'After detalles_planificacion INSERT', 85, 136, 'Completo', NULL, NULL, NULL),
(52, 'insert', '2025-07-21 15:23:40', 'CONDITION MET for distrib_planif', 85, 136, 'Completo', NULL, NULL, 0),
(53, 'insert', '2025-07-21 15:23:40', 'Count from distribucionhora', 85, NULL, NULL, NULL, 0, NULL),
(54, 'insert', '2025-07-21 15:23:40', 'Skipped INSERT (no rows in distribucionhora)', 85, NULL, NULL, NULL, 0, 0),
(55, 'insert', '2025-07-21 15:23:40', 'Trigger END', 85, NULL, NULL, NULL, NULL, NULL),
(56, 'insert', '2025-07-21 18:10:33', 'Trigger START', 86, NULL, 'Programado', NULL, NULL, NULL),
(57, 'insert', '2025-07-21 18:10:33', 'After Planificacion SELECT', 86, NULL, NULL, 7, NULL, NULL),
(58, 'insert', '2025-07-21 18:10:33', 'After detalles_planificacion INSERT', 86, 137, 'Programado', NULL, NULL, NULL),
(59, 'insert', '2025-07-21 18:10:33', 'CONDITION NOT MET for distrib_planif', 86, 137, 'Programado', NULL, NULL, 0),
(60, 'insert', '2025-07-21 18:10:33', 'Trigger END', 86, NULL, NULL, NULL, NULL, NULL),
(61, 'insert', '2025-07-22 15:38:29', 'Trigger START', 87, NULL, 'Completo', NULL, NULL, NULL),
(62, 'insert', '2025-07-22 15:38:29', 'After Planificacion SELECT', 87, NULL, NULL, 8, NULL, NULL),
(63, 'insert', '2025-07-22 15:38:29', 'After detalles_planificacion INSERT', 87, 138, 'Completo', NULL, NULL, NULL),
(64, 'insert', '2025-07-22 15:38:29', 'CONDITION MET for distrib_planif', 87, 138, 'Completo', NULL, NULL, 0),
(65, 'insert', '2025-07-22 15:38:29', 'Count from distribucionhora', 87, NULL, NULL, NULL, 0, NULL),
(66, 'insert', '2025-07-22 15:38:29', 'Skipped INSERT (no rows in distribucionhora)', 87, NULL, NULL, NULL, 0, 0),
(67, 'insert', '2025-07-22 15:38:29', 'Trigger END', 87, NULL, NULL, NULL, NULL, NULL),
(68, 'insert', '2025-07-24 03:25:18', 'Trigger START', 88, NULL, 'En revisión', NULL, NULL, NULL),
(69, 'insert', '2025-07-24 03:25:18', 'After Planificacion SELECT', 88, NULL, NULL, 5, NULL, NULL),
(70, 'insert', '2025-07-24 03:25:18', 'After detalles_planificacion INSERT', 88, 140, 'En revisión', NULL, NULL, NULL),
(71, 'insert', '2025-07-24 03:25:18', 'CONDITION NOT MET for distrib_planif', 88, 140, 'En revisión', NULL, NULL, 0),
(72, 'insert', '2025-07-24 03:25:18', 'Trigger END', 88, NULL, NULL, NULL, NULL, NULL),
(73, 'insert', '2025-07-24 03:29:08', 'Trigger START', 89, NULL, 'En proceso', NULL, NULL, NULL),
(74, 'insert', '2025-07-24 03:29:08', 'After Planificacion SELECT', 89, NULL, NULL, 7, NULL, NULL),
(75, 'insert', '2025-07-24 03:29:08', 'After detalles_planificacion INSERT', 89, 141, 'En proceso', NULL, NULL, NULL),
(76, 'insert', '2025-07-24 03:29:08', 'CONDITION NOT MET for distrib_planif', 89, 141, 'En proceso', NULL, NULL, 0),
(77, 'insert', '2025-07-24 03:29:08', 'Trigger END', 89, NULL, NULL, NULL, NULL, NULL),
(78, 'insert', '2025-07-24 16:57:02', 'Trigger START', 90, NULL, 'Completo', NULL, NULL, NULL),
(79, 'insert', '2025-07-24 16:57:02', 'After Planificacion SELECT', 90, NULL, NULL, 11, NULL, NULL),
(80, 'insert', '2025-07-24 16:57:02', 'After detalles_planificacion INSERT', 90, 142, 'Completo', NULL, NULL, NULL),
(81, 'insert', '2025-07-24 16:57:02', 'CONDITION MET for distrib_planif', 90, 142, 'Completo', NULL, NULL, 0),
(82, 'insert', '2025-07-24 16:57:02', 'Count from distribucionhora', 90, NULL, NULL, NULL, 0, NULL),
(83, 'insert', '2025-07-24 16:57:02', 'Skipped INSERT (no rows in distribucionhora)', 90, NULL, NULL, NULL, 0, 0),
(84, 'insert', '2025-07-24 16:57:02', 'Trigger END', 90, NULL, NULL, NULL, NULL, NULL),
(85, 'insert', '2025-07-25 19:25:35', 'Trigger START', 91, NULL, 'Completo', NULL, NULL, NULL),
(86, 'insert', '2025-07-25 19:25:35', 'After Planificacion SELECT', 91, NULL, NULL, NULL, NULL, NULL),
(87, 'insert', '2025-07-25 19:25:35', 'v_idplanificacion IS NULL', 91, NULL, NULL, NULL, NULL, 0),
(88, 'insert', '2025-07-25 19:25:35', 'Trigger END', 91, NULL, NULL, NULL, NULL, NULL),
(89, 'insert', '2025-07-31 15:11:37', 'Trigger START', 92, NULL, 'Completo', NULL, NULL, NULL),
(90, 'insert', '2025-07-31 15:11:37', 'After Planificacion SELECT', 92, NULL, NULL, NULL, NULL, NULL),
(91, 'insert', '2025-07-31 15:11:37', 'v_idplanificacion IS NULL', 92, NULL, NULL, NULL, NULL, 0),
(92, 'insert', '2025-07-31 15:11:37', 'Trigger END', 92, NULL, NULL, NULL, NULL, NULL),
(93, 'insert', '2025-07-31 15:13:13', 'Trigger START', 93, NULL, 'Completo', NULL, NULL, NULL),
(94, 'insert', '2025-07-31 15:13:13', 'After Planificacion SELECT', 93, NULL, NULL, 8, NULL, NULL),
(95, 'insert', '2025-07-31 15:13:13', 'After detalles_planificacion INSERT', 93, 144, 'Completo', NULL, NULL, NULL),
(96, 'insert', '2025-07-31 15:13:13', 'CONDITION MET for distrib_planif', 93, 144, 'Completo', NULL, NULL, 0),
(97, 'insert', '2025-07-31 15:13:13', 'Count from distribucionhora', 93, NULL, NULL, NULL, 0, NULL),
(98, 'insert', '2025-07-31 15:13:13', 'Skipped INSERT (no rows in distribucionhora)', 93, NULL, NULL, NULL, 0, 0),
(99, 'insert', '2025-07-31 15:13:13', 'Trigger END', 93, NULL, NULL, NULL, NULL, NULL),
(100, 'insert', '2025-07-31 15:16:09', 'Trigger START', 94, NULL, 'En proceso', NULL, NULL, NULL),
(101, 'insert', '2025-07-31 15:16:09', 'After Planificacion SELECT', 94, NULL, NULL, 10, NULL, NULL),
(102, 'insert', '2025-07-31 15:16:09', 'After detalles_planificacion INSERT', 94, 145, 'En proceso', NULL, NULL, NULL),
(103, 'insert', '2025-07-31 15:16:09', 'CONDITION NOT MET for distrib_planif', 94, 145, 'En proceso', NULL, NULL, 0),
(104, 'insert', '2025-07-31 15:16:09', 'Trigger END', 94, NULL, NULL, NULL, NULL, NULL),
(105, 'insert', '2025-07-31 15:17:26', 'Trigger START', 95, NULL, 'Completo', NULL, NULL, NULL),
(106, 'insert', '2025-07-31 15:17:26', 'After Planificacion SELECT', 95, NULL, NULL, 10, NULL, NULL),
(107, 'insert', '2025-07-31 15:17:26', 'After detalles_planificacion INSERT', 95, 146, 'Completo', NULL, NULL, NULL),
(108, 'insert', '2025-07-31 15:17:26', 'CONDITION MET for distrib_planif', 95, 146, 'Completo', NULL, NULL, 0),
(109, 'insert', '2025-07-31 15:17:26', 'Count from distribucionhora', 95, NULL, NULL, NULL, 0, NULL),
(110, 'insert', '2025-07-31 15:17:26', 'Skipped INSERT (no rows in distribucionhora)', 95, NULL, NULL, NULL, 0, 0),
(111, 'insert', '2025-07-31 15:17:26', 'Trigger END', 95, NULL, NULL, NULL, NULL, NULL),
(112, 'insert', '2025-07-31 22:29:39', 'Trigger START', 96, NULL, 'Completo', NULL, NULL, NULL),
(113, 'insert', '2025-07-31 22:29:39', 'After Planificacion SELECT', 96, NULL, NULL, NULL, NULL, NULL),
(114, 'insert', '2025-07-31 22:29:39', 'v_idplanificacion IS NULL', 96, NULL, NULL, NULL, NULL, 0),
(115, 'insert', '2025-07-31 22:29:39', 'Trigger END', 96, NULL, NULL, NULL, NULL, NULL),
(116, 'insert', '2025-07-31 22:59:43', 'Trigger START', 97, NULL, 'En proceso', NULL, NULL, NULL),
(117, 'insert', '2025-07-31 22:59:43', 'After Planificacion SELECT', 97, NULL, NULL, 12, NULL, NULL),
(118, 'insert', '2025-07-31 22:59:43', 'After detalles_planificacion INSERT', 97, 147, 'En proceso', NULL, NULL, NULL),
(119, 'insert', '2025-07-31 22:59:43', 'CONDITION NOT MET for distrib_planif', 97, 147, 'En proceso', NULL, NULL, 0),
(120, 'insert', '2025-07-31 22:59:43', 'Trigger END', 97, NULL, NULL, NULL, NULL, NULL),
(121, 'insert', '2025-07-31 23:19:21', 'Trigger START', 98, NULL, 'Completo', NULL, NULL, NULL),
(122, 'insert', '2025-07-31 23:19:21', 'After Planificacion SELECT', 98, NULL, NULL, 8, NULL, NULL),
(123, 'insert', '2025-07-31 23:19:21', 'After detalles_planificacion INSERT', 98, 148, 'Completo', NULL, NULL, NULL),
(124, 'insert', '2025-07-31 23:19:21', 'CONDITION MET for distrib_planif', 98, 148, 'Completo', NULL, NULL, 0),
(125, 'insert', '2025-07-31 23:19:21', 'Count from distribucionhora', 98, NULL, NULL, NULL, 0, NULL),
(126, 'insert', '2025-07-31 23:19:21', 'Skipped INSERT (no rows in distribucionhora)', 98, NULL, NULL, NULL, 0, 0),
(127, 'insert', '2025-07-31 23:19:21', 'Trigger END', 98, NULL, NULL, NULL, NULL, NULL),
(128, 'insert', '2025-08-01 17:09:00', 'Trigger START', 99, NULL, 'Completo', NULL, NULL, NULL),
(129, 'insert', '2025-08-01 17:09:00', 'After Planificacion SELECT', 99, NULL, NULL, NULL, NULL, NULL),
(130, 'insert', '2025-08-01 17:09:00', 'v_idplanificacion IS NULL', 99, NULL, NULL, NULL, NULL, 0),
(131, 'insert', '2025-08-01 17:09:00', 'Trigger END', 99, NULL, NULL, NULL, NULL, NULL),
(132, 'insert', '2025-08-01 18:40:51', 'Trigger START', 100, NULL, 'Completo', NULL, NULL, NULL),
(133, 'insert', '2025-08-01 18:40:51', 'After Planificacion SELECT', 100, NULL, NULL, 14, NULL, NULL),
(134, 'insert', '2025-08-01 18:40:51', 'After detalles_planificacion INSERT', 100, 149, 'Completo', NULL, NULL, NULL),
(135, 'insert', '2025-08-01 18:40:51', 'CONDITION MET for distrib_planif', 100, 149, 'Completo', NULL, NULL, 0),
(136, 'insert', '2025-08-01 18:40:51', 'Count from distribucionhora', 100, NULL, NULL, NULL, 0, NULL),
(137, 'insert', '2025-08-01 18:40:51', 'Skipped INSERT (no rows in distribucionhora)', 100, NULL, NULL, NULL, 0, 0),
(138, 'insert', '2025-08-01 18:40:51', 'Trigger END', 100, NULL, NULL, NULL, NULL, NULL),
(139, 'insert', '2025-08-04 13:44:13', 'Trigger START', 101, NULL, 'Programado', NULL, NULL, NULL),
(140, 'insert', '2025-08-04 13:44:13', 'After Planificacion SELECT', 101, NULL, NULL, NULL, NULL, NULL),
(141, 'insert', '2025-08-04 13:44:13', 'v_idplanificacion IS NULL', 101, NULL, NULL, NULL, NULL, 0),
(142, 'insert', '2025-08-04 13:44:13', 'Trigger END', 101, NULL, NULL, NULL, NULL, NULL),
(143, 'insert', '2025-08-04 13:46:16', 'Trigger START', 102, NULL, 'En proceso', NULL, NULL, NULL),
(144, 'insert', '2025-08-04 13:46:16', 'After Planificacion SELECT', 102, NULL, NULL, NULL, NULL, NULL),
(145, 'insert', '2025-08-04 13:46:16', 'v_idplanificacion IS NULL', 102, NULL, NULL, NULL, NULL, 0),
(146, 'insert', '2025-08-04 13:46:16', 'Trigger END', 102, NULL, NULL, NULL, NULL, NULL),
(147, 'insert', '2025-08-04 13:48:07', 'Trigger START', 103, NULL, 'Programado', NULL, NULL, NULL),
(148, 'insert', '2025-08-04 13:48:07', 'After Planificacion SELECT', 103, NULL, NULL, NULL, NULL, NULL),
(149, 'insert', '2025-08-04 13:48:07', 'v_idplanificacion IS NULL', 103, NULL, NULL, NULL, NULL, 0),
(150, 'insert', '2025-08-04 13:48:07', 'Trigger END', 103, NULL, NULL, NULL, NULL, NULL),
(151, 'insert', '2025-08-04 13:51:55', 'Trigger START', 104, NULL, 'En proceso', NULL, NULL, NULL),
(152, 'insert', '2025-08-04 13:51:55', 'After Planificacion SELECT', 104, NULL, NULL, NULL, NULL, NULL),
(153, 'insert', '2025-08-04 13:51:55', 'v_idplanificacion IS NULL', 104, NULL, NULL, NULL, NULL, 0),
(154, 'insert', '2025-08-04 13:51:55', 'Trigger END', 104, NULL, NULL, NULL, NULL, NULL),
(155, 'insert', '2025-08-08 20:29:43', 'Trigger START', 105, NULL, 'Programado', NULL, NULL, NULL),
(156, 'insert', '2025-08-08 20:29:43', 'After Planificacion SELECT', 105, NULL, NULL, NULL, NULL, NULL),
(157, 'insert', '2025-08-08 20:29:43', 'v_idplanificacion IS NULL', 105, NULL, NULL, NULL, NULL, 0),
(158, 'insert', '2025-08-08 20:29:43', 'Trigger END', 105, NULL, NULL, NULL, NULL, NULL),
(159, 'insert', '2025-08-08 21:23:16', 'Trigger START', 106, NULL, 'Completo', NULL, NULL, NULL),
(160, 'insert', '2025-08-08 21:23:16', 'After Planificacion SELECT', 106, NULL, NULL, NULL, NULL, NULL),
(161, 'insert', '2025-08-08 21:23:16', 'v_idplanificacion IS NULL', 106, NULL, NULL, NULL, NULL, 0),
(162, 'insert', '2025-08-08 21:23:16', 'Trigger END', 106, NULL, NULL, NULL, NULL, NULL),
(163, 'insert', '2025-08-08 21:25:30', 'Trigger START', 107, NULL, 'Completo', NULL, NULL, NULL),
(164, 'insert', '2025-08-08 21:25:30', 'After Planificacion SELECT', 107, NULL, NULL, NULL, NULL, NULL),
(165, 'insert', '2025-08-08 21:25:30', 'v_idplanificacion IS NULL', 107, NULL, NULL, NULL, NULL, 0),
(166, 'insert', '2025-08-08 21:25:30', 'Trigger END', 107, NULL, NULL, NULL, NULL, NULL),
(167, 'insert', '2025-08-08 21:32:15', 'Trigger START', 108, NULL, 'Completo', NULL, NULL, NULL),
(168, 'insert', '2025-08-08 21:32:15', 'After Planificacion SELECT', 108, NULL, NULL, NULL, NULL, NULL),
(169, 'insert', '2025-08-08 21:32:15', 'v_idplanificacion IS NULL', 108, NULL, NULL, NULL, NULL, 0),
(170, 'insert', '2025-08-08 21:32:15', 'Trigger END', 108, NULL, NULL, NULL, NULL, NULL),
(171, 'insert', '2025-08-08 21:43:26', 'Trigger START', 109, NULL, 'Completo', NULL, NULL, NULL),
(172, 'insert', '2025-08-08 21:43:26', 'After Planificacion SELECT', 109, NULL, NULL, 15, NULL, NULL),
(173, 'insert', '2025-08-08 21:43:26', 'After detalles_planificacion INSERT', 109, 151, 'Completo', NULL, NULL, NULL),
(174, 'insert', '2025-08-08 21:43:26', 'CONDITION MET for distrib_planif', 109, 151, 'Completo', NULL, NULL, 0),
(175, 'insert', '2025-08-08 21:43:26', 'Count from distribucionhora', 109, NULL, NULL, NULL, 0, NULL),
(176, 'insert', '2025-08-08 21:43:26', 'Skipped INSERT (no rows in distribucionhora)', 109, NULL, NULL, NULL, 0, 0),
(177, 'insert', '2025-08-08 21:43:26', 'Trigger END', 109, NULL, NULL, NULL, NULL, NULL),
(178, 'insert', '2025-08-11 21:45:49', 'Trigger START', 110, NULL, 'Completo', NULL, NULL, NULL),
(179, 'insert', '2025-08-11 21:45:49', 'After Planificacion SELECT', 110, NULL, NULL, NULL, NULL, NULL),
(180, 'insert', '2025-08-11 21:45:49', 'v_idplanificacion IS NULL', 110, NULL, NULL, NULL, NULL, 0),
(181, 'insert', '2025-08-11 21:45:49', 'Trigger END', 110, NULL, NULL, NULL, NULL, NULL),
(182, 'insert', '2025-08-12 21:33:54', 'Trigger START', 111, NULL, 'Completo', NULL, NULL, NULL),
(183, 'insert', '2025-08-12 21:33:54', 'After Planificacion SELECT', 111, NULL, NULL, NULL, NULL, NULL),
(184, 'insert', '2025-08-12 21:33:54', 'v_idplanificacion IS NULL', 111, NULL, NULL, NULL, NULL, 0),
(185, 'insert', '2025-08-12 21:33:54', 'Trigger END', 111, NULL, NULL, NULL, NULL, NULL),
(186, 'insert', '2025-08-12 22:09:47', 'Trigger START', 112, NULL, 'En proceso', NULL, NULL, NULL),
(187, 'insert', '2025-08-12 22:09:47', 'After Planificacion SELECT', 112, NULL, NULL, NULL, NULL, NULL),
(188, 'insert', '2025-08-12 22:09:47', 'v_idplanificacion IS NULL', 112, NULL, NULL, NULL, NULL, 0),
(189, 'insert', '2025-08-12 22:09:47', 'Trigger END', 112, NULL, NULL, NULL, NULL, NULL),
(190, 'insert', '2025-08-12 22:31:08', 'Trigger START', 113, NULL, 'Completo', NULL, NULL, NULL),
(191, 'insert', '2025-08-12 22:31:08', 'After Planificacion SELECT', 113, NULL, NULL, NULL, NULL, NULL),
(192, 'insert', '2025-08-12 22:31:08', 'v_idplanificacion IS NULL', 113, NULL, NULL, NULL, NULL, 0),
(193, 'insert', '2025-08-12 22:31:08', 'Trigger END', 113, NULL, NULL, NULL, NULL, NULL),
(194, 'insert', '2025-08-13 16:54:27', 'Trigger START', 114, NULL, 'Completo', NULL, NULL, NULL),
(195, 'insert', '2025-08-13 16:54:27', 'After Planificacion SELECT', 114, NULL, NULL, 16, NULL, NULL),
(196, 'insert', '2025-08-13 16:54:27', 'After detalles_planificacion INSERT', 114, 154, 'Completo', NULL, NULL, NULL),
(197, 'insert', '2025-08-13 16:54:27', 'CONDITION MET for distrib_planif', 114, 154, 'Completo', NULL, NULL, 0),
(198, 'insert', '2025-08-13 16:54:27', 'Count from distribucionhora', 114, NULL, NULL, NULL, 0, NULL),
(199, 'insert', '2025-08-13 16:54:27', 'Skipped INSERT (no rows in distribucionhora)', 114, NULL, NULL, NULL, 0, 0),
(200, 'insert', '2025-08-13 16:54:27', 'Trigger END', 114, NULL, NULL, NULL, NULL, NULL),
(201, 'insert', '2025-08-14 17:30:50', 'Trigger START', 115, NULL, 'Completo', NULL, NULL, NULL),
(202, 'insert', '2025-08-14 17:30:50', 'After Planificacion SELECT', 115, NULL, NULL, 15, NULL, NULL),
(203, 'insert', '2025-08-14 17:30:50', 'After detalles_planificacion INSERT', 115, 170, 'Completo', NULL, NULL, NULL),
(204, 'insert', '2025-08-14 17:30:50', 'CONDITION MET for distrib_planif', 115, 170, 'Completo', NULL, NULL, 0),
(205, 'insert', '2025-08-14 17:30:50', 'Count from distribucionhora', 115, NULL, NULL, NULL, 0, NULL),
(206, 'insert', '2025-08-14 17:30:50', 'Skipped INSERT (no rows in distribucionhora)', 115, NULL, NULL, NULL, 0, 0),
(207, 'insert', '2025-08-14 17:30:50', 'Trigger END', 115, NULL, NULL, NULL, NULL, NULL),
(208, 'insert', '2025-08-15 22:45:18', 'Trigger START', 116, NULL, 'Completo', NULL, NULL, NULL),
(209, 'insert', '2025-08-15 22:45:18', 'After Planificacion SELECT', 116, NULL, NULL, 20, NULL, NULL),
(210, 'insert', '2025-08-15 22:45:18', 'After detalles_planificacion INSERT', 116, 171, 'Completo', NULL, NULL, NULL),
(211, 'insert', '2025-08-15 22:45:18', 'CONDITION MET for distrib_planif', 116, 171, 'Completo', NULL, NULL, 0),
(212, 'insert', '2025-08-15 22:45:18', 'Count from distribucionhora', 116, NULL, NULL, NULL, 0, NULL),
(213, 'insert', '2025-08-15 22:45:18', 'Skipped INSERT (no rows in distribucionhora)', 116, NULL, NULL, NULL, 0, 0),
(214, 'insert', '2025-08-15 22:45:18', 'Trigger END', 116, NULL, NULL, NULL, NULL, NULL),
(215, 'insert', '2025-08-15 23:57:31', 'Trigger START', 117, NULL, 'Completo', NULL, NULL, NULL),
(216, 'insert', '2025-08-15 23:57:31', 'After Planificacion SELECT', 117, NULL, NULL, 19, NULL, NULL),
(217, 'insert', '2025-08-15 23:57:31', 'After detalles_planificacion INSERT', 117, 172, 'Completo', NULL, NULL, NULL),
(218, 'insert', '2025-08-15 23:57:31', 'CONDITION MET for distrib_planif', 117, 172, 'Completo', NULL, NULL, 0),
(219, 'insert', '2025-08-15 23:57:31', 'Count from distribucionhora', 117, NULL, NULL, NULL, 0, NULL),
(220, 'insert', '2025-08-15 23:57:31', 'Skipped INSERT (no rows in distribucionhora)', 117, NULL, NULL, NULL, 0, 0),
(221, 'insert', '2025-08-15 23:57:31', 'Trigger END', 117, NULL, NULL, NULL, NULL, NULL),
(222, 'insert', '2025-08-16 00:05:19', 'Trigger START', 118, NULL, 'Completo', NULL, NULL, NULL),
(223, 'insert', '2025-08-16 00:05:19', 'After Planificacion SELECT', 118, NULL, NULL, 19, NULL, NULL),
(224, 'insert', '2025-08-16 00:05:19', 'After detalles_planificacion INSERT', 118, 173, 'Completo', NULL, NULL, NULL),
(225, 'insert', '2025-08-16 00:05:19', 'CONDITION MET for distrib_planif', 118, 173, 'Completo', NULL, NULL, 0),
(226, 'insert', '2025-08-16 00:05:19', 'Count from distribucionhora', 118, NULL, NULL, NULL, 0, NULL),
(227, 'insert', '2025-08-16 00:05:19', 'Skipped INSERT (no rows in distribucionhora)', 118, NULL, NULL, NULL, 0, 0),
(228, 'insert', '2025-08-16 00:05:19', 'Trigger END', 118, NULL, NULL, NULL, NULL, NULL),
(229, 'insert', '2025-08-16 01:24:51', 'Trigger START', 119, NULL, 'Completo', NULL, NULL, NULL),
(230, 'insert', '2025-08-16 01:24:51', 'After Planificacion SELECT', 119, NULL, NULL, 17, NULL, NULL),
(231, 'insert', '2025-08-16 01:24:51', 'After detalles_planificacion INSERT', 119, 174, 'Completo', NULL, NULL, NULL),
(232, 'insert', '2025-08-16 01:24:51', 'CONDITION MET for distrib_planif', 119, 174, 'Completo', NULL, NULL, 0),
(233, 'insert', '2025-08-16 01:24:51', 'Count from distribucionhora', 119, NULL, NULL, NULL, 0, NULL),
(234, 'insert', '2025-08-16 01:24:51', 'Skipped INSERT (no rows in distribucionhora)', 119, NULL, NULL, NULL, 0, 0),
(235, 'insert', '2025-08-16 01:24:51', 'Trigger END', 119, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idusuario` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `password` varchar(250) NOT NULL,
  `tipo` int(11) NOT NULL,
  `activo` int(11) NOT NULL,
  `idemp` int(11) DEFAULT NULL,
  `editor` int(11) NOT NULL DEFAULT 1,
  `registrado` timestamp NOT NULL DEFAULT current_timestamp(),
  `modificado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idusuario`, `nombre`, `password`, `tipo`, `activo`, `idemp`, `editor`, `registrado`, `modificado`) VALUES
(1, 'jcornejo', '$2y$10$jQsgtP.Ob.dpNMrOV40.N.r5na./hHzbolcBvQRxH114.ST/SCoAG', 1, 1, 8, 1, '2025-07-07 13:20:05', '2025-07-07 13:20:05'),
(2, 'gkou', '$2y$10$PBwbumenp2mP.AuzpWO6sell2NaeX2X17FODMEjk01tTlz/p4Vdre', 1, 1, 9, 1, '2025-07-07 13:20:05', '2025-07-07 13:20:05'),
(3, 'mgonzalez', '$2y$10$JvKZ7J7PMf5chD1TebcIJe4GU4AB33lgSXTC/VcDVFiGQdigNrN3S', 1, 1, 2, 1, '2025-07-07 13:20:05', '2025-07-07 13:20:05'),
(4, 'jrojas', '$2y$10$Dbe0gj5oCAqOjyqnaSRfQuL7RX/7CNqq73C9PX0EBnNqRHCG.HidO', 2, 1, 3, 2, '2025-07-07 13:20:05', '2025-07-08 16:16:22'),
(5, 'gramirez', '$2y$10$dlVD3Aiu4y4HiqzPA38gHuowS6.5EORjOheh7rDRTFXiVmeMkY3o6', 2, 1, 4, 1, '2025-07-07 13:20:05', '2025-07-07 13:20:05'),
(6, 'jtorres', '$2y$10$bSCVJ.yBZdtBdHEfI0.yBe1a0XUaRPywYnDcqt10GT1NVPxhrSroW', 2, 1, 6, 1, '2025-07-07 13:20:05', '2025-07-07 13:20:05'),
(7, 'knieto', '$2y$10$dw8MwCohjxqX09eUpbA99OFOOOzAEPLNRUmiYVxIojV.QglIWd.NG', 3, 0, 5, 2, '2025-07-07 13:20:05', '2025-08-15 18:11:43'),
(8, 'HORE', '$2y$10$Vn3aD85Qu3MF8mm1M5HoBe4P6liN8KhL1m3xfk/k6dBqL1dcgO/UC', 1, 1, 11, 2, '2025-07-23 00:04:46', '2025-07-23 00:04:46');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_planificacion_vs_participantes_completado`
-- (Véase abajo para la vista actual)
--
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

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_progreso_colaborador_vs_meta`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_progreso_colaborador_vs_meta` (
`idempleado` int(11)
,`NombreColaborador` varchar(50)
,`HorasMeta` int(11)
,`Anio` int(5)
,`Mes` int(3)
,`HorasCompletadas` decimal(32,2)
,`PorcentajeCumplimiento` decimal(39,6)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_reporte_planificacion_vs_liquidacion`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_reporte_planificacion_vs_liquidacion` (
`Idplanificacion` int(11)
,`NombrePlan` varchar(255)
,`MesPlan` varchar(7)
,`AnioPlan` int(5)
,`MesPlanNumerico` int(3)
,`idContratoCliente` int(11)
,`NombreCliente` varchar(50)
,`HorasPlanificadas` int(11)
,`EstadoLiquidacion` varchar(50)
,`HorasLiquidadasPorEstado` decimal(32,0)
,`TotalHorasLiquidadasMes` decimal(32,0)
,`PorcentajeConsumidoPorEstado` decimal(40,5)
,`PorcentajeTotalConsumidoMes` decimal(40,5)
);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `adendacliente`
--
ALTER TABLE `adendacliente`
  ADD PRIMARY KEY (`idadendacli`),
  ADD KEY `idcontratocli` (`idcontratocli`);

--
-- Indices de la tabla `adendaempleado`
--
ALTER TABLE `adendaempleado`
  ADD PRIMARY KEY (`idadendaemp`),
  ADD KEY `idcontratoemp` (`idcontratoemp`);

--
-- Indices de la tabla `anuncio`
--
ALTER TABLE `anuncio`
  ADD PRIMARY KEY (`idanuncio`),
  ADD KEY `acargode` (`acargode`);

--
-- Indices de la tabla `calendario`
--
ALTER TABLE `calendario`
  ADD PRIMARY KEY (`idcalendario`),
  ADD KEY `acargode` (`acargode`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idcliente`);

--
-- Indices de la tabla `contratocliente`
--
ALTER TABLE `contratocliente`
  ADD PRIMARY KEY (`idcontratocli`),
  ADD KEY `idcliente` (`idcliente`),
  ADD KEY `lider` (`lider`);

--
-- Indices de la tabla `contratoempleado`
--
ALTER TABLE `contratoempleado`
  ADD PRIMARY KEY (`idcontratoemp`),
  ADD KEY `idemp` (`idemp`);

--
-- Indices de la tabla `cuotahito`
--
ALTER TABLE `cuotahito`
  ADD PRIMARY KEY (`idcouta`),
  ADD KEY `idpresupuesto` (`idpresupuesto`);

--
-- Indices de la tabla `detalle`
--
ALTER TABLE `detalle`
  ADD KEY `idfacturacion` (`idfacturacion`);

--
-- Indices de la tabla `detalles_planificacion`
--
ALTER TABLE `detalles_planificacion`
  ADD PRIMARY KEY (`iddetalle`),
  ADD KEY `idx_Idplanificacion` (`Idplanificacion`),
  ADD KEY `idx_idliquidacion` (`idliquidacion`);

--
-- Indices de la tabla `distribucionhora`
--
ALTER TABLE `distribucionhora`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idliquidacion` (`idliquidacion`);

--
-- Indices de la tabla `distribucion_planificacion`
--
ALTER TABLE `distribucion_planificacion`
  ADD PRIMARY KEY (`iddistribucionplan`),
  ADD KEY `idx_iddetalle` (`iddetalle`),
  ADD KEY `idx_idparticipante` (`idparticipante`);

--
-- Indices de la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD PRIMARY KEY (`idempleado`);

--
-- Indices de la tabla `evento`
--
ALTER TABLE `evento`
  ADD PRIMARY KEY (`idevento`),
  ADD KEY `acargode` (`acargode`);

--
-- Indices de la tabla `facturacion`
--
ALTER TABLE `facturacion`
  ADD PRIMARY KEY (`idfacturacion`),
  ADD KEY `idcliente` (`idcliente`);

--
-- Indices de la tabla `liquidacion`
--
ALTER TABLE `liquidacion`
  ADD PRIMARY KEY (`idliquidacion`),
  ADD KEY `idcontratocli` (`idcontratocli`),
  ADD KEY `tema` (`tema`),
  ADD KEY `acargode` (`acargode`);

--
-- Indices de la tabla `planificacion`
--
ALTER TABLE `planificacion`
  ADD PRIMARY KEY (`Idplanificacion`),
  ADD KEY `idx_idContratoCliente` (`idContratoCliente`),
  ADD KEY `idx_lider` (`lider`),
  ADD KEY `idx_editor` (`editor`);

--
-- Indices de la tabla `presupuestocliente`
--
ALTER TABLE `presupuestocliente`
  ADD PRIMARY KEY (`idpresupuesto`),
  ADD KEY `idcliente` (`idcliente`),
  ADD KEY `acargode` (`acargode`);

--
-- Indices de la tabla `sesiones_log`
--
ALTER TABLE `sesiones_log`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tema`
--
ALTER TABLE `tema`
  ADD PRIMARY KEY (`idtema`);

--
-- Indices de la tabla `trigger_debug_log`
--
ALTER TABLE `trigger_debug_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idusuario`),
  ADD KEY `idemp` (`idemp`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `adendacliente`
--
ALTER TABLE `adendacliente`
  MODIFY `idadendacli` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `adendaempleado`
--
ALTER TABLE `adendaempleado`
  MODIFY `idadendaemp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `anuncio`
--
ALTER TABLE `anuncio`
  MODIFY `idanuncio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `calendario`
--
ALTER TABLE `calendario`
  MODIFY `idcalendario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `contratocliente`
--
ALTER TABLE `contratocliente`
  MODIFY `idcontratocli` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `contratoempleado`
--
ALTER TABLE `contratoempleado`
  MODIFY `idcontratoemp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `cuotahito`
--
ALTER TABLE `cuotahito`
  MODIFY `idcouta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_planificacion`
--
ALTER TABLE `detalles_planificacion`
  MODIFY `iddetalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT de la tabla `distribucionhora`
--
ALTER TABLE `distribucionhora`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=371;

--
-- AUTO_INCREMENT de la tabla `distribucion_planificacion`
--
ALTER TABLE `distribucion_planificacion`
  MODIFY `iddistribucionplan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2379;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `idempleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `evento`
--
ALTER TABLE `evento`
  MODIFY `idevento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `facturacion`
--
ALTER TABLE `facturacion`
  MODIFY `idfacturacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `liquidacion`
--
ALTER TABLE `liquidacion`
  MODIFY `idliquidacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT de la tabla `planificacion`
--
ALTER TABLE `planificacion`
  MODIFY `Idplanificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `presupuestocliente`
--
ALTER TABLE `presupuestocliente`
  MODIFY `idpresupuesto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sesiones_log`
--
ALTER TABLE `sesiones_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT de la tabla `tema`
--
ALTER TABLE `tema`
  MODIFY `idtema` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `trigger_debug_log`
--
ALTER TABLE `trigger_debug_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=236;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_planificacion_vs_participantes_completado`
--
DROP TABLE IF EXISTS `vista_planificacion_vs_participantes_completado`;

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `vista_planificacion_vs_participantes_completado`  AS WITH PlanificacionHorasCompletadas AS (SELECT `p`.`Idplanificacion` AS `Idplanificacion`, `p`.`nombreplan` AS `nombreplan`, `p`.`fechaplan` AS `fechaplan`, `p`.`idContratoCliente` AS `idContratoCliente`, `p`.`horasplan` AS `HorasPlanificadasGlobal`, sum(case when `dp`.`estado` = 'Completo' then `dp`.`cantidahoras` else 0 end) AS `TotalHorasLiquidadasCompletadas` FROM (`planificacion` `p` left join `detalles_planificacion` `dp` on(`p`.`Idplanificacion` = `dp`.`Idplanificacion`)) GROUP BY `p`.`Idplanificacion`, `p`.`nombreplan`, `p`.`fechaplan`, `p`.`idContratoCliente`, `p`.`horasplan`), HorasCompletadasPorParticipante AS (SELECT `dp`.`Idplanificacion` AS `Idplanificacion`, `dplan`.`idparticipante` AS `idparticipante`, sum(`dplan`.`horas_asignadas`) AS `HorasAsignadasAlParticipante` FROM (`detalles_planificacion` `dp` join `distribucion_planificacion` `dplan` on(`dp`.`iddetalle` = `dplan`.`iddetalle`)) WHERE `dp`.`estado` = 'Completo' GROUP BY `dp`.`Idplanificacion`, `dplan`.`idparticipante`)  SELECT `phc`.`Idplanificacion` AS `Idplanificacion`, `phc`.`nombreplan` AS `NombrePlan`, date_format(`phc`.`fechaplan`,'%Y-%m') AS `MesPlan`, `phc`.`idContratoCliente` AS `idContratoCliente`, `cli`.`nombrecomercial` AS `NombreCliente`, `phc`.`HorasPlanificadasGlobal` AS `HorasPlanificadasGlobal`, coalesce(`phc`.`TotalHorasLiquidadasCompletadas`,0) AS `TotalHorasLiquidadasCompletadas`, CASE WHEN `phc`.`HorasPlanificadasGlobal` is null OR `phc`.`HorasPlanificadasGlobal` = 0 THEN 0 ELSE coalesce(`phc`.`TotalHorasLiquidadasCompletadas`,0) * 100.0 / `phc`.`HorasPlanificadasGlobal` END AS `PorcentajePlanCompletado`, `hpp`.`idparticipante` AS `IdParticipante`, `emp`.`nombrecorto` AS `NombreParticipante`, coalesce(`hpp`.`HorasAsignadasAlParticipante`,0) AS `HorasCompletadasPorParticipante`, CASE WHEN coalesce(`phc`.`TotalHorasLiquidadasCompletadas`,0) = 0 THEN 0 ELSE coalesce(`hpp`.`HorasAsignadasAlParticipante`,0) * 100.0 / `phc`.`TotalHorasLiquidadasCompletadas` END AS `PorcentajeDelParticipanteEnCompletadas`, CASE WHEN `phc`.`HorasPlanificadasGlobal` is null OR `phc`.`HorasPlanificadasGlobal` = 0 THEN 0 ELSE coalesce(`hpp`.`HorasAsignadasAlParticipante`,0) * 100.0 / `phc`.`HorasPlanificadasGlobal` END AS `PorcentajeDelParticipanteEnPlanGlobal` FROM ((((`planificacionhorascompletadas` `phc` left join `horascompletadasporparticipante` `hpp` on(`phc`.`Idplanificacion` = `hpp`.`Idplanificacion`)) left join `empleado` `emp` on(`hpp`.`idparticipante` = `emp`.`idempleado`)) left join `contratocliente` `cc` on(`phc`.`idContratoCliente` = `cc`.`idcontratocli`)) left join `cliente` `cli` on(`cc`.`idcliente` = `cli`.`idcliente`)) ORDER BY date_format(`phc`.`fechaplan`,'%Y-%m') DESC, `phc`.`Idplanificacion` ASC, `emp`.`nombrecorto` ASC;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_progreso_colaborador_vs_meta`
--
DROP TABLE IF EXISTS `vista_progreso_colaborador_vs_meta`;

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `vista_progreso_colaborador_vs_meta`  AS SELECT `e`.`idempleado` AS `idempleado`, `e`.`nombrecorto` AS `NombreColaborador`, `e`.`horasmeta` AS `HorasMeta`, year(`l`.`fecha`) AS `Anio`, month(`l`.`fecha`) AS `Mes`, sum(`dh`.`calculo`) AS `HorasCompletadas`, sum(`dh`.`calculo`) / `e`.`horasmeta` * 100 AS `PorcentajeCumplimiento` FROM ((`distribucionhora` `dh` join `liquidacion` `l` on(`dh`.`idliquidacion` = `l`.`idliquidacion`)) join `empleado` `e` on(`dh`.`participante` = `e`.`idempleado`)) WHERE `l`.`estado` = 'Completo' GROUP BY `e`.`idempleado`, `e`.`nombrecorto`, `e`.`horasmeta`, year(`l`.`fecha`), month(`l`.`fecha`) ORDER BY year(`l`.`fecha`) DESC, month(`l`.`fecha`) DESC, `e`.`nombrecorto` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_reporte_planificacion_vs_liquidacion`
--
DROP TABLE IF EXISTS `vista_reporte_planificacion_vs_liquidacion`;

CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `vista_reporte_planificacion_vs_liquidacion`  AS WITH PlanificacionConTotalesLiquidadas AS (SELECT `p`.`Idplanificacion` AS `Idplanificacion`, `p`.`nombreplan` AS `nombreplan`, `p`.`fechaplan` AS `fechaplan`, `p`.`idContratoCliente` AS `idContratoCliente`, `p`.`horasplan` AS `horasplan`, sum(`dp`.`cantidahoras`) AS `TotalHorasLiquidadasMes` FROM (`planificacion` `p` left join `detalles_planificacion` `dp` on(`p`.`Idplanificacion` = `dp`.`Idplanificacion`)) GROUP BY `p`.`Idplanificacion`, `p`.`nombreplan`, `p`.`fechaplan`, `p`.`idContratoCliente`, `p`.`horasplan`), PlanificacionDetallePorEstado AS (SELECT `p`.`Idplanificacion` AS `Idplanificacion`, `dp`.`estado` AS `EstadoLiquidacion`, sum(`dp`.`cantidahoras`) AS `HorasLiquidadasPorEstado` FROM (`planificacion` `p` join `detalles_planificacion` `dp` on(`p`.`Idplanificacion` = `dp`.`Idplanificacion`)) GROUP BY `p`.`Idplanificacion`, `dp`.`estado`)  SELECT `ptl`.`Idplanificacion` AS `Idplanificacion`, `ptl`.`nombreplan` AS `NombrePlan`, date_format(`ptl`.`fechaplan`,'%Y-%m') AS `MesPlan`, year(`ptl`.`fechaplan`) AS `AnioPlan`, month(`ptl`.`fechaplan`) AS `MesPlanNumerico`, `ptl`.`idContratoCliente` AS `idContratoCliente`, `cli`.`nombrecomercial` AS `NombreCliente`, `ptl`.`horasplan` AS `HorasPlanificadas`, coalesce(`pdpe`.`EstadoLiquidacion`,'Sin Liquidaciones') AS `EstadoLiquidacion`, coalesce(`pdpe`.`HorasLiquidadasPorEstado`,0) AS `HorasLiquidadasPorEstado`, coalesce(`ptl`.`TotalHorasLiquidadasMes`,0) AS `TotalHorasLiquidadasMes`, CASE WHEN `ptl`.`horasplan` is null OR `ptl`.`horasplan` = 0 THEN 0 ELSE coalesce(`pdpe`.`HorasLiquidadasPorEstado`,0) * 100.0 / `ptl`.`horasplan` END AS `PorcentajeConsumidoPorEstado`, CASE WHEN `ptl`.`horasplan` is null OR `ptl`.`horasplan` = 0 THEN 0 ELSE coalesce(`ptl`.`TotalHorasLiquidadasMes`,0) * 100.0 / `ptl`.`horasplan` END AS `PorcentajeTotalConsumidoMes` FROM (((`planificacioncontotalesliquidadas` `ptl` left join `planificaciondetalleporestado` `pdpe` on(`ptl`.`Idplanificacion` = `pdpe`.`Idplanificacion`)) left join `contratocliente` `cc` on(`ptl`.`idContratoCliente` = `cc`.`idcontratocli`)) left join `cliente` `cli` on(`cc`.`idcliente` = `cli`.`idcliente`));

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `adendacliente`
--
ALTER TABLE `adendacliente`
  ADD CONSTRAINT `adendacliente_ibfk_1` FOREIGN KEY (`idcontratocli`) REFERENCES `contratocliente` (`idcontratocli`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `adendaempleado`
--
ALTER TABLE `adendaempleado`
  ADD CONSTRAINT `adendaempleado_ibfk_1` FOREIGN KEY (`idadendaemp`) REFERENCES `contratoempleado` (`idcontratoemp`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `anuncio`
--
ALTER TABLE `anuncio`
  ADD CONSTRAINT `anuncio_ibfk_1` FOREIGN KEY (`acargode`) REFERENCES `empleado` (`idempleado`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `calendario`
--
ALTER TABLE `calendario`
  ADD CONSTRAINT `calendario_ibfk_1` FOREIGN KEY (`acargode`) REFERENCES `empleado` (`idempleado`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `contratocliente`
--
ALTER TABLE `contratocliente`
  ADD CONSTRAINT `contratocliente_ibfk_1` FOREIGN KEY (`idcliente`) REFERENCES `cliente` (`idcliente`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `contratocliente_ibfk_2` FOREIGN KEY (`lider`) REFERENCES `empleado` (`idempleado`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `contratoempleado`
--
ALTER TABLE `contratoempleado`
  ADD CONSTRAINT `contratoempleado_ibfk_1` FOREIGN KEY (`idemp`) REFERENCES `empleado` (`idempleado`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `cuotahito`
--
ALTER TABLE `cuotahito`
  ADD CONSTRAINT `cuotahito_ibfk_1` FOREIGN KEY (`idpresupuesto`) REFERENCES `presupuestocliente` (`idpresupuesto`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle`
--
ALTER TABLE `detalle`
  ADD CONSTRAINT `detalle_ibfk_1` FOREIGN KEY (`idfacturacion`) REFERENCES `facturacion` (`idfacturacion`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalles_planificacion`
--
ALTER TABLE `detalles_planificacion`
  ADD CONSTRAINT `fk_detalles_planificacion_liquidacion` FOREIGN KEY (`idliquidacion`) REFERENCES `liquidacion` (`idliquidacion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalles_planificacion_planificacion` FOREIGN KEY (`Idplanificacion`) REFERENCES `planificacion` (`Idplanificacion`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `distribucionhora`
--
ALTER TABLE `distribucionhora`
  ADD CONSTRAINT `distribucionhora_ibfk_1` FOREIGN KEY (`idliquidacion`) REFERENCES `liquidacion` (`idliquidacion`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `distribucion_planificacion`
--
ALTER TABLE `distribucion_planificacion`
  ADD CONSTRAINT `fk_distribucion_planificacion_detalles` FOREIGN KEY (`iddetalle`) REFERENCES `detalles_planificacion` (`iddetalle`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_distribucion_planificacion_empleado` FOREIGN KEY (`idparticipante`) REFERENCES `empleado` (`idempleado`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `evento`
--
ALTER TABLE `evento`
  ADD CONSTRAINT `evento_ibfk_1` FOREIGN KEY (`acargode`) REFERENCES `empleado` (`idempleado`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `facturacion`
--
ALTER TABLE `facturacion`
  ADD CONSTRAINT `facturacion_ibfk_1` FOREIGN KEY (`idcliente`) REFERENCES `cliente` (`idcliente`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `liquidacion`
--
ALTER TABLE `liquidacion`
  ADD CONSTRAINT `liquidacion_ibfk_1` FOREIGN KEY (`idcontratocli`) REFERENCES `contratocliente` (`idcontratocli`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `liquidacion_ibfk_2` FOREIGN KEY (`tema`) REFERENCES `tema` (`idtema`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `liquidacion_ibfk_3` FOREIGN KEY (`acargode`) REFERENCES `empleado` (`idempleado`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `planificacion`
--
ALTER TABLE `planificacion`
  ADD CONSTRAINT `fk_planificacion_contratocliente` FOREIGN KEY (`idContratoCliente`) REFERENCES `contratocliente` (`idcontratocli`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_planificacion_editor` FOREIGN KEY (`editor`) REFERENCES `empleado` (`idempleado`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_planificacion_lider` FOREIGN KEY (`lider`) REFERENCES `empleado` (`idempleado`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `presupuestocliente`
--
ALTER TABLE `presupuestocliente`
  ADD CONSTRAINT `presupuestocliente_ibfk_1` FOREIGN KEY (`idcliente`) REFERENCES `cliente` (`idcliente`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `presupuestocliente_ibfk_2` FOREIGN KEY (`acargode`) REFERENCES `empleado` (`idempleado`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`idemp`) REFERENCES `empleado` (`idempleado`) ON DELETE NO ACTION ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
