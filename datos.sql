-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-11-2026 a las 14:32:18
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
-- Base de datos: `datos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `matricula` varchar(30) NOT NULL,
  `grado` varchar(20) NOT NULL,
  `grupo` varchar(10) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `urls_sistema`
--

CREATE TABLE `urls_sistema` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `categoria` varchar(20) NOT NULL DEFAULT 'administrador'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `urls_sistema`
--

INSERT INTO `urls_sistema` (`id`, `nombre`, `url`, `estado`, `categoria`) VALUES
(1, 'index_fichas', 'index.php', 1, 'administrador'),
(2, 'Preescolar Llenado formatos', 'preescolar2026.php', 1, 'preescolar'),
(3, 'Formato de preescolar', 'busca_preescolar.php', 1, 'preescolar'),
(4, 'Primaria Llenado formatos', 'primaria2026.php', 1, 'primaria'),
(5, 'Primaria formato', 'busca_primaria.php', 1, 'primaria'),
(6, 'Secundaria Llenado de formato', 'secundaria2026.php', 1, 'secundaria'),
(7, 'Secundaria Formato', 'busca_secundaria.php', 1, 'secundaria'),
(8, 'Vista Inscripciones Secundaria', 'admon/vista_inscripciones_secu.php', 1, 'secundaria'),
(9, 'Vista Inscripciones Preescolar', 'admon/vista_inscripciones_prees.php', 1, 'preescolar'),
(10, 'Acta de Hechos', 'admon/acta_hechos/public/sistema.php', 1, 'administrador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nombre_user` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `tipo_user` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `nombre_user`, `pass`, `tipo_user`) VALUES
(1, 'Jonathan', '$2y$10$M.nwfJU7nSKF1CGHO5cOdOC3DWVe0p7iP2REBpmlache/ORt/WYuG', 'administrador'),
(2, 'Eric', '$2y$10$Ut5BuTUnHoO0g7vekIXgYOMX5zCofx7i/72xnfljWQl6Ya4ijEojC', 'administrador'),
(4, 'Laisha', '$2y$10$A9A.2t1YF3Vm6sFc4ngKmeyOFRSnQ1UK4rGF/.PV7I9PiMDJBtxBW', 'administrador'),
(5, 'nataly', '$2y$10$bLJB/tYmxkA0Acaa3Bmvve3FOJs2vri1fRZKm59oVK.QJxJnDTOlO', 'administrador'),
(6, 'primaria2026', '$2y$10$Yy/k0ok.4mCpI7EPkTqoNejqnKLjrC1TRusy.EhbnCufoF8AOo7cS', 'primaria'),
(7, 'secundaria2026', '$2y$10$VDf99Xmtafyd1yVn1i6Z0ech/jZCvrAWcDcQJQHzuimi.xHrk2G4W', 'secundaria'),
(8, 'preescolar2026', '$2y$10$JrUz1EoVLgM8X2dylqqCb.ASyiGaRZx/LqV8drgGsLuM28tfURWuK', 'preescolar');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `urls_sistema`
--
ALTER TABLE `urls_sistema`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `urls_sistema`
--
ALTER TABLE `urls_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
