-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-11-2026 a las 02:25:46
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
-- Base de datos: `sistema_escolar`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumnos`
--

CREATE TABLE `alumnos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `grado` varchar(10) DEFAULT NULL,
  `grupo` varchar(10) DEFAULT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `puntos_actuales` int(11) NOT NULL DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alumnos`
--

INSERT INTO `alumnos` (`id`, `nombre`, `grado`, `grupo`, `codigo`, `estado`, `puntos_actuales`) VALUES
(1, 'Juan Perez', '3', 'A', '2025A01', 'rojo', 45);

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

--
-- Volcado de datos para la tabla `asistencia`
--

INSERT INTO `asistencia` (`id`, `nombre`, `matricula`, `grado`, `grupo`, `fecha`, `hora`) VALUES
(50, 'Joshua', '', '3 grado', 'grupo A', '2026-10-26', '11:51:18'),
(51, 'alexander mi dios', '', 'tercero', 'grupo A', '2026-10-26', '11:52:21'),
(52, 'gesis sayuri gomes acosta', '', 'tercero', 'grupo A', '2026-10-26', '11:53:11'),
(53, 'Joshua', '', '3 grado', 'grupo A', '2026-10-28', '08:03:41'),
(54, 'alexander mi dios', '', 'tercero', 'grupo A', '2026-10-28', '17:42:37'),
(55, 'Juan Perez', '2026-A12', '3', 'B', '2026-10-28', '18:55:01'),
(56, 'Zaid oswaldo', '2026-A', '1', 'Z', '2026-10-28', '18:57:02'),
(57, 'JZTC', '2026-AA', '6', 'A', '2026-10-28', '18:58:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id` int(11) NOT NULL,
  `alumno_id` int(10) UNSIGNED NOT NULL,
  `tipo_falta_id` int(10) UNSIGNED NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date NOT NULL,
  `responsable` varchar(100) DEFAULT 'Trabajo Social',
  `puntos` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reportes`
--

INSERT INTO `reportes` (`id`, `alumno_id`, `tipo_falta_id`, `descripcion`, `fecha`, `responsable`, `puntos`, `created_at`) VALUES
(2, 1, 1, 'Prueba de sistema', '2026-03-24', 'Trabajo Social', 5, '2026-03-25 04:05:23'),
(3, 1, 10, 'Apuñalamiento a un compañero', '2026-04-01', '', 10, '2026-04-01 19:36:54'),
(4, 1, 15, 'Pocesion de sustancias psico adictivas', '2026-04-13', '', 15, '2026-04-13 02:41:05'),
(5, 1, 3, 'Falta de respeto a la compañera', '2026-04-13', '', 10, '2026-04-13 15:23:50'),
(6, 1, 7, 'Poderio', '2026-04-13', '', 15, '2026-04-13 15:24:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_falta`
--

CREATE TABLE `tipos_falta` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `puntos` int(11) NOT NULL,
  `categoria` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_falta`
--

INSERT INTO `tipos_falta` (`id`, `nombre`, `puntos`, `categoria`) VALUES
(1, 'Indisciplina en honores a la bandera', 5, 'Disciplina'),
(2, 'Tres retardos a la hora de entrada de la jornada escolar', 0, 'Administrativa'),
(3, 'Falta de respeto a miembros de la comunidad escolar, verbal, física y/o moralmente', 10, 'Conducta'),
(4, 'Salirse de clases y/o de la escuela sin permiso', 5, 'Conducta'),
(5, 'Destruir infraestructura y/o mobiliario', 10, 'Grave'),
(6, 'Daño al medio ambiente y/o ecosistema', 5, 'Conducta'),
(7, 'Causar daño psicológico y/o físico a miembros de la comunidad escolar', 15, 'Grave'),
(8, 'No portar el uniforme o portarlo incorrectamente durante la jornada escolar', 3, 'Disciplina'),
(9, 'Incumplir con los criterios de aseo personal', 0, 'Administrativa'),
(10, 'Evitar las demostraciones inapropiadas (tocamientos lascivos que afecten la moral de la comunidad es', 10, 'Conducta'),
(11, 'Apuestas y retos malintencionados', 5, 'Conducta'),
(12, 'Mentir, dar información falsa o engañar al personal escolar', 0, 'Administrativa'),
(13, 'Ingresar a la escuela sin autorización de la autoridad educativa, fuera de la jornada escolar', 0, 'Administrativa'),
(14, 'Portar e ingresar artefactos, sustancias explosivas y armas dentro de la institución', 10, 'Grave'),
(15, 'Posesión y consumo de sustancias tóxico-adictivas', 15, 'Grave'),
(16, 'Distribución de sustancias tóxico-adictivas', 100, 'Grave'),
(17, 'Divulgar pornografía', 15, 'Grave'),
(18, 'Apropiarse deliberadamente y sin autorización de bienes que pertenecen a otros', 15, 'Grave');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `password`, `rol`) VALUES
(2, 'admin', '$2y$10$vzXi0HMBlsjqyqzdFHxthO7OvqsWkSS9vOWmeDLL8JPfFiqgjR.Om', 'admin');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alumno_id` (`alumno_id`),
  ADD KEY `tipo_falta_id` (`tipo_falta_id`);

--
-- Indices de la tabla `tipos_falta`
--
ALTER TABLE `tipos_falta`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tipos_falta`
--
ALTER TABLE `tipos_falta`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`),
  ADD CONSTRAINT `reportes_ibfk_2` FOREIGN KEY (`tipo_falta_id`) REFERENCES `tipos_falta` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
