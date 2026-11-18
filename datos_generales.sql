-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-11-2026 a las 14:32:34
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
-- Base de datos: `datos_generales`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `civil`
--

CREATE TABLE `civil` (
  `id` int(11) NOT NULL,
  `civil` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `civil`
--

INSERT INTO `civil` (`id`, `civil`) VALUES
(1, 'Soltero(a)'),
(2, 'Casado(a)'),
(3, 'Divorciado(a)'),
(4, 'Viudo(a)'),
(5, 'Unión Libre');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados`
--

CREATE TABLE `estados` (
  `id_estado` int(11) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estados`
--

INSERT INTO `estados` (`id_estado`, `estado`) VALUES
(1, 'Aguascalientes'),
(2, 'Baja California'),
(3, 'Baja California Sur '),
(4, 'Campeche'),
(5, 'Chiapas'),
(6, 'Chihuahua'),
(7, 'Ciudad de México'),
(8, 'Coahuila'),
(9, 'Colima'),
(10, 'Durango'),
(11, 'Estado de México'),
(12, 'Guanajuato '),
(13, 'Guerrero'),
(14, 'Hidalgo'),
(15, 'Jalisco '),
(16, 'Michoacán'),
(17, 'Morelos  '),
(18, 'Nayarit'),
(19, 'Nuevo León '),
(20, 'Oaxaca'),
(21, 'Puebla'),
(22, 'Querétaro'),
(23, 'Quintana Roo'),
(24, 'San Luis Potosí'),
(25, 'Sinaloa'),
(26, 'Sonora'),
(27, 'Tabasco'),
(28, 'Tamaulipas'),
(29, 'Tlaxcala'),
(30, 'Veracruz '),
(31, 'Yucatán'),
(32, 'Zacatecas ');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_sangre`
--

CREATE TABLE `tipo_sangre` (
  `id_sangre` int(11) NOT NULL,
  `tipo_sanguineo` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_sangre`
--

INSERT INTO `tipo_sangre` (`id_sangre`, `tipo_sanguineo`) VALUES
(1, 'A+'),
(2, 'B+'),
(3, 'AB+'),
(4, 'O+'),
(5, 'A-'),
(6, 'B-'),
(7, 'AB-'),
(8, 'O-');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `civil`
--
ALTER TABLE `civil`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipo_sangre`
--
ALTER TABLE `tipo_sangre`
  ADD PRIMARY KEY (`id_sangre`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `civil`
--
ALTER TABLE `civil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tipo_sangre`
--
ALTER TABLE `tipo_sangre`
  MODIFY `id_sangre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
