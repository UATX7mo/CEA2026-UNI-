-- Esquema exclusivo del módulo Acta de Hechos.
CREATE TABLE IF NOT EXISTS `actas_hechos` (
  `id_acta` int NOT NULL AUTO_INCREMENT,

  -- Folio consecutive AH-000001. Lo asigna la aplicacion con
  -- pims_registrar_acta() a partir de id_acta, no lo escribe el usuario.
  `folio` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,

  `matricula` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_alumno` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_emision` date NOT NULL,

  -- Formato oficial: hora y minutos van separados en el documento.
  `hora_evento` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'HH:MM',
  `suscriptor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre de quien suscribe',

  `nivel` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grado` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grupo` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivo` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hechos` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `acciones` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hora_conclusion` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'HH:MM',

  -- Auditoria
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `capturado_por` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actualizado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id_acta`),

  -- El folio es unico: dos actas no pueden compartir numero.
  UNIQUE KEY `folio` (`folio`),
  KEY `idx_matricula` (`matricula`),
  KEY `idx_fecha` (`fecha_emision`),
  KEY `idx_nivel` (`nivel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
