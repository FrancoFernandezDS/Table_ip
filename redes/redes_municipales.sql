-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-09-2025 a las 14:05:46
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `redes_municipales`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `redes`
--

CREATE TABLE `redes` (
  `id` int(11) NOT NULL,
  `nombre_red` varchar(100) NOT NULL,
  `tipo_red` varchar(50) NOT NULL,
  `direccion_ip` varchar(15) DEFAULT NULL,
  `mascara_subred` varchar(15) DEFAULT NULL,
  `gateway` varchar(15) DEFAULT NULL,
  `dns_primario` varchar(15) DEFAULT NULL,
  `dns_secundario` varchar(15) DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `departamento` varchar(50) DEFAULT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  `fecha_instalacion` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('activa','inactiva') DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `redes`
--

INSERT INTO `redes` (`id`, `nombre_red`, `tipo_red`, `direccion_ip`, `mascara_subred`, `gateway`, `dns_primario`, `dns_secundario`, `ubicacion`, `departamento`, `responsable`, `telefono_contacto`, `fecha_instalacion`, `observaciones`, `estado`) VALUES
(1, 'Informatica', 'Terminales', '172.20.99.223', '255.255.254.0', '172.20.99.9', '8.8.8.8', '', 'Palacio Municipal', 'Informatica', 'Franco Fernandez', '2241576208', '2025-09-11', '', 'activa'),
(3, 'Informatica', 'Impresoras', '172.20.99.182', '255.255.254.0', '172.20.99.9', '8.8.8.8', '', 'Palacio Municipal', 'Informatica', 'Hp Laser Jet Pro 4003dw', '', '2025-09-12', '', 'activa'),
(4, 'Rentas', 'Router', '172.20.99.23', '', '', '', NULL, 'Rentas', 'Rentas', 'Toto-link', '', '0000-00-00', '', 'activa'),
(5, 'Casa de Campo', 'Relojes', '172.20.99.239', '', '', '', '', 'Produccion', 'Desarrollo Sustentable', 'RRHH', '', '2025-09-12', '', 'activa'),
(6, 'Servidor WEB', 'Servidor', '172.20.99.5', '', '', '', '', 'Palacio Municipal', 'Informatica', 'Tecnico', 'W7', '2025-09-12', '', 'activa'),
(7, 'Camaras', 'Otro', '172.20.99.91', '', '', '', '', 'Palacio Municipal', 'Tesoreria', 'Tesoreria', 'DVR Dahua 16ch', '2025-09-12', '', 'activa'),
(8, 'Iomega', 'Servidor', '172.20.99.1', '', '', '', '', 'Sala de Servidores', 'Informatica', 'Tecnico', '', '2025-09-12', '', 'activa'),
(9, 'Servidor Sifim', 'Servidor', '172.20.99.2', NULL, NULL, NULL, NULL, 'Sala de Servidores', 'Informatica', 'Tecnico', 'WS 2008', '2025-09-12', '', 'activa'),
(10, 'Servidor Rafam', 'Servidor', '172.20.99.3', NULL, NULL, NULL, NULL, 'Sala de Servidores', 'Informatica', 'Tecnico', 'WS 2008', '2025-09-12', '', 'activa'),
(11, 'Servidor Remoto ', 'Servidor', '172.20.99.4', NULL, NULL, NULL, NULL, 'Sala de Servidores', 'Informatica', 'Tecnico', 'W7', '2025-09-12', '', 'activa'),
(12, 'Servidor Debian', 'Servidor', '172.20.99.6', NULL, NULL, NULL, NULL, 'Sala de Servidores', 'Informatica', 'Tecnico', 'LINUX', '2025-09-12', '', 'activa'),
(13, 'Servidor Fomuvi', 'Servidor', '172.20.99.7', NULL, NULL, NULL, NULL, 'Sala de Servidores', 'Informatica', 'Tecnico', 'LINUX', '2025-09-12', '', 'activa'),
(14, 'Compras', 'Terminales', '172.20.99.11', NULL, NULL, NULL, NULL, 'Palacio Municipal', 'Compras', 'Vanesa Astobiza', 'Compra-009588', '2025-09-12', 'W10', 'activa'),
(15, 'Compras', 'Terminales', '172.20.99.12', '', '', '', '', 'Palacio Municipal', 'Compras', 'Ivana Graffigna', 'compra-Igraff', '2025-09-12', 'W10', 'activa'),
(16, 'Rentas', 'Terminales', '172.20.99.40', NULL, NULL, NULL, NULL, 'Rentas', 'Atencion al Publico', 'Daniela Mollo', 'Renta-023447', '2025-09-12', 'W11', 'activa'),
(17, 'Asesoría Legal ', 'Impresoras', '172.20.99.74', NULL, NULL, NULL, NULL, 'Palacio Municipal', 'Asesoría Legal ', 'HP M130FW', '', '2025-09-12', '', 'activa'),
(18, 'Rentas', 'Impresoras', '172.20.99.102', NULL, NULL, NULL, NULL, 'Rentas', 'Procesamiento de Datos', 'HP P4015', '', '2025-09-12', '', 'activa'),
(19, 'Colonia', 'Router', '172.20.99.140', '', '', '', '', 'Dest Polc Colonia', 'Policia Colonia', 'TP-LINK', '', '2025-09-12', '', 'activa'),
(20, 'Informatica', 'Terminales', '172.20.99.220', NULL, NULL, NULL, NULL, 'Palacio Municipal', 'Informatica', 'Alejandro Cabral ', '', '2025-09-12', '', 'activa'),
(21, 'Palacio Municipal', 'Relojes', '172.20.99.240', NULL, NULL, NULL, NULL, 'Palacio Municipal', 'Municipalidad', 'RRHH', '', '2025-09-12', '', 'activa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rol` enum('administrador','tecnico','consulta') DEFAULT 'consulta',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `departamento` varchar(50) DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `usuario`, `password`, `nombre`, `email`, `rol`, `activo`, `fecha_creacion`, `ultimo_acceso`, `telefono`, `departamento`, `fecha_actualizacion`) VALUES
(1, 'admin', 'admin', 'Administrador', 'informatica@generalbelgrano,gob,ar', 'administrador', 1, '2025-09-12 00:11:12', NULL, '+54 2241 123456', 'Sistemas', '2025-09-12 11:58:46'),
(2, 'tecnico', 'infor911', 'Técnico Redes', 'soportetecnico@generalbelgrano.gob.ar', 'tecnico', 1, '2025-09-12 00:11:12', NULL, '+54 2241 654321', 'Redes', '2025-09-12 12:13:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rol` enum('admin','tecnico','consulta') DEFAULT 'consulta',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `redes`
--
ALTER TABLE `redes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `redes`
--
ALTER TABLE `redes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
