-- Agrega categorías a los módulos administrados desde Control de URLs.
ALTER TABLE urls_sistema
    ADD COLUMN categoria VARCHAR(20) NOT NULL DEFAULT 'administrador' AFTER estado;

UPDATE urls_sistema
SET categoria = CASE
    WHEN LOWER(CONCAT(nombre, ' ', url)) LIKE '%preescolar%' THEN 'preescolar'
    WHEN LOWER(CONCAT(nombre, ' ', url)) LIKE '%primaria%' THEN 'primaria'
    WHEN LOWER(CONCAT(nombre, ' ', url)) LIKE '%secundaria%' THEN 'secundaria'
    ELSE 'administrador'
END;
