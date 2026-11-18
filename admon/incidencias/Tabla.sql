
CREATE TABLE IF NOT EXISTS incidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_incidencia DATE NOT NULL,
    hora_incidencia TIME NOT NULL,
    area_lugar VARCHAR(150) NOT NULL,
    tipo_incidencia VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    nombre_involucrado VARCHAR(150) NOT NULL,
    cargo_grupo VARCHAR(100) NULL,
    responsable_atencion VARCHAR(150) NOT NULL,
    prioridad ENUM('Baja', 'Media', 'Alta', 'Crítica') NOT NULL,
    accion_realizada TEXT NULL,
    estado ENUM('Pendiente', 'En proceso', 'Resuelto', 'Cerrada') NOT NULL DEFAULT 'Pendiente',
    fecha_estado DATE NULL,
    observaciones TEXT NULL,
    archivo_evidencia VARCHAR(255) NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

