CREATE TABLE IF NOT EXISTS inscripcion2026.reportes_alumnos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nivel ENUM('preescolar', 'primaria', 'secundaria') NOT NULL,
    nombre_alumno VARCHAR(150) NOT NULL,
    grado VARCHAR(20) NOT NULL,
    grupo VARCHAR(20) NOT NULL,
    fecha_reporte VARCHAR(100) NOT NULL,
    conducta TEXT NOT NULL,
    quien_reporta VARCHAR(150) NOT NULL,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_reportes_nivel_fecha (nivel, fecha_registro),
    KEY idx_reportes_alumno (nombre_alumno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
