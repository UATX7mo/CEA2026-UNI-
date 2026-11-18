-- Cuentas de acceso por sección para la base `datos`.
-- Ejecutar si se necesita recrear las cuentas en otra instalación.
INSERT INTO users (nombre_user, pass, tipo_user)
SELECT 'primaria2026', '$2y$10$Yy/k0ok.4mCpI7EPkTqoNejqnKLjrC1TRusy.EhbnCufoF8AOo7cS', 'primaria'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE nombre_user = 'primaria2026');

INSERT INTO users (nombre_user, pass, tipo_user)
SELECT 'secundaria2026', '$2y$10$VDf99Xmtafyd1yVn1i6Z0ech/jZCvrAWcDcQJQHzuimi.xHrk2G4W', 'secundaria'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE nombre_user = 'secundaria2026');

INSERT INTO users (nombre_user, pass, tipo_user)
SELECT 'preescolar2026', '$2y$10$JrUz1EoVLgM8X2dylqqCb.ASyiGaRZx/LqV8drgGsLuM28tfURWuK', 'preescolar'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE nombre_user = 'preescolar2026');

UPDATE users
SET pass = '$2y$10$JrUz1EoVLgM8X2dylqqCb.ASyiGaRZx/LqV8drgGsLuM28tfURWuK'
WHERE nombre_user = 'preescolar2026' AND tipo_user = 'preescolar';
