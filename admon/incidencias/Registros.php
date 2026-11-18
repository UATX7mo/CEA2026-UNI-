<?php
require_once __DIR__ . '/acceso.php';


header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/Conexion.php';

$mensaje = "";
$tipo_mensaje = "";

// 1. Eliminar incidencia
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    try {
        $stmt_del = $pdo->prepare("DELETE FROM incidencias WHERE id = :id");
        $stmt_del->execute([':id' => $id_eliminar]);
        $mensaje = "Incidencia #$id_eliminar eliminada con éxito.";
        $tipo_mensaje = "exito";
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar la incidencia: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}

// 2. Editar/Guardar cambios completos de la incidencia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_edicion'])) {
    $id_edit                = (int)$_POST['id_edit'];
    $fecha_incidencia       = $_POST['fecha_incidencia'] ?? '';
    $hora_incidencia        = $_POST['hora_incidencia'] ?? '';
    $area_lugar             = trim($_POST['area_lugar'] ?? '');
    $tipo_incidencia        = trim($_POST['tipo_incidencia'] ?? '');
    $descripcion            = trim($_POST['descripcion'] ?? '');
    $nombre_involucrado     = trim($_POST['nombre_involucrado'] ?? '');
    $cargo_grupo            = trim($_POST['cargo_grupo'] ?? '');
    $responsable_atencion   = trim($_POST['responsable_atencion'] ?? '');
    $prioridad              = $_POST['prioridad'] ?? 'Baja';
    $accion_realizada       = trim($_POST['accion_realizada'] ?? '');
    $estado                 = $_POST['estado'] ?? 'Pendiente';
    $fecha_estado           = !empty($_POST['fecha_estado']) ? $_POST['fecha_estado'] : NULL;
    $observaciones          = trim($_POST['observaciones'] ?? '');

    try {
        if (isset($_FILES['archivo_firma']) && $_FILES['archivo_firma']['error'] === UPLOAD_ERR_OK) {
            $file_tmp  = $_FILES['archivo_firma']['tmp_name'];
            $file_name = $_FILES['archivo_firma']['name'];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $extensiones = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $extensiones)) {
                $dir = __DIR__ . '/uploads/';
                if (!is_dir($dir)) { mkdir($dir, 0777, true); }
                $nuevo_archivo = uniqid('firma_') . '.' . $file_ext;
                move_uploaded_file($file_tmp, $dir . $nuevo_archivo);

                $stmt_upd_file = $pdo->prepare("UPDATE incidencias SET archivo_evidencia = :archivo WHERE id = :id");
                $stmt_upd_file->execute([':archivo' => $nuevo_archivo, ':id' => $id_edit]);
            }
        }

        $sql_update = "UPDATE incidencias SET 
                       fecha_incidencia = :fecha_incidencia,
                       hora_incidencia = :hora_incidencia,
                       area_lugar = :area_lugar,
                       tipo_incidencia = :tipo_incidencia,
                       descripcion = :descripcion,
                       nombre_involucrado = :nombre_involucrado,
                       cargo_grupo = :cargo_grupo,
                       responsable_atencion = :responsable_atencion,
                       prioridad = :prioridad,
                       accion_realizada = :accion_realizada,
                       estado = :estado,
                       fecha_estado = :fecha_estado,
                       observaciones = :observaciones
                       WHERE id = :id";

        $stmt_upd = $pdo->prepare($sql_update);
        $stmt_upd->execute([
            ':fecha_incidencia'     => $fecha_incidencia,
            ':hora_incidencia'      => $hora_incidencia,
            ':area_lugar'           => $area_lugar,
            ':tipo_incidencia'      => $tipo_incidencia,
            ':descripcion'          => $descripcion,
            ':nombre_involucrado'   => $nombre_involucrado,
            ':cargo_grupo'          => $cargo_grupo,
            ':responsable_atencion' => $responsable_atencion,
            ':prioridad'            => $prioridad,
            ':accion_realizada'     => $accion_realizada,
            ':estado'               => $estado,
            ':fecha_estado'         => $fecha_estado,
            ':observaciones'        => $observaciones,
            ':id'                   => $id_edit
        ]);

        $mensaje = "Incidencia #$id_edit actualizada completamente.";
        $tipo_mensaje = "exito";
    } catch (PDOException $e) {
        $mensaje = "Error al guardar los cambios: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}

// Búsqueda y consulta de incidencias
$busqueda = trim($_GET['buscar'] ?? '');
$sql_list = "SELECT * FROM incidencias";
$params = [];

if (!empty($busqueda)) {
    $sql_list .= " WHERE nombre_involucrado LIKE :b OR area_lugar LIKE :b OR tipo_incidencia LIKE :b OR responsable_atencion LIKE :b";
    $params[':b'] = '%' . $busqueda . '%';
}

$sql_list .= " ORDER BY id DESC";
$stmt_list = $pdo->prepare($sql_list);
$stmt_list->execute($params);
$incidencias = $stmt_list->fetchAll();

// Cargar datos para el formulario de edición si viene por GET
$incidencia_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = (int)$_GET['editar'];
    $stmt_get = $pdo->prepare("SELECT * FROM incidencias WHERE id = :id LIMIT 1");
    $stmt_get->execute([':id' => $id_editar]);
    $incidencia_editar = $stmt_get->fetch();
}

include 'encabezado.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
        /* 
          Regla de diseño 60-30-10:
          - 60% Fondos claros (#F8FAFC / blanco)
          - 30% Negro institucional (linear-gradient(90deg, #000000, #1a1a1a))
          - 10% Dorado institucional (#C9A227 / #FFD700)
        */
        .page {
            --color-60-bg: #F8FAFC;
            --color-60-card: #FFFFFF;
            --color-30-dark: linear-gradient(90deg, #000000, #1a1a1a);
            --color-30-solid: #0d0d0d;
            --color-10-gold: #C9A227;
            --color-10-gold-hover: #FFD700;
            --text-dark: #1E293B;
            --text-muted: #64748B;
            --border-light: #E2E8F0;
            --success: #16A34A;
            --danger: #DC2626;
            --shadow-pro: 0 20px 40px -15px rgba(0, 0, 0, 0.2);
        }

        :where(.page, .page *) {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Ocupa todo el ancho y alto disponible entre encabezado y pie */
        .page {
            width: 100%;
            min-height: calc(100vh - 140px);
            display: flex;
            flex-direction: column;
            color: var(--text-dark);
            background: var(--color-60-bg);
            padding: 20px 28px 28px;
            gap: 20px;
        }

        /* ===== MENÚ HORIZONTAL ===== */
        .page .top-menu {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            width: 100%;
            padding: 10px 18px;
            background: var(--color-30-dark);
            border: 1px solid var(--color-10-gold);
            border-radius: 14px;
            box-shadow: 0 8px 20px -10px rgba(0, 0, 0, 0.5);
        }

        .page .brand-top {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page .hero-logo {
            width: 34px;
            height: 34px;
            object-fit: contain;
        }

        .page .brand-name strong {
            font-size: 14px;
            color: var(--color-10-gold);
            letter-spacing: -0.2px;
            font-weight: 800;
        }

        .page .nav-menu {
            display: flex;
            flex-direction: row;
            gap: 8px;
        }

        .page .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(201, 162, 39, 0.3);
            border-radius: 999px;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            transition: all 0.3s ease;
        }

        .page .nav-link:hover {
            color: var(--color-10-gold-hover) !important;
            border-color: var(--color-10-gold);
            background: rgba(201, 162, 39, 0.1);
        }

        .page .nav-link.active {
            border-color: var(--color-10-gold);
            background: linear-gradient(45deg, #C9A227, #FFD700);
            color: #000000 !important;
        }

        /* ===== ÁREA PRINCIPAL (ancho completo) ===== */
        .page .main-area {
            flex: 1;
            width: 100%;
            min-width: 0;
        }

        .page .card {
            width: 100%;
            background: var(--color-60-card);
            border-radius: 20px;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-pro);
            padding: 32px 40px;
            margin-bottom: 24px;
        }

        .page .title-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
            margin-bottom: 24px;
            border-bottom: 2px solid var(--border-light);
            padding-bottom: 18px;
        }

        .page .title-bar h1 {
            color: var(--text-dark);
            font-size: 24px;
            font-weight: 800;
        }

        .page .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 520px;
            min-width: 260px;
        }

        .page .search-box input {
            flex: 1;
            padding: 12px 16px;
            background: #F8FAFC;
            border: 1.5px solid var(--border-light);
            color: var(--text-dark);
            border-radius: 10px;
            font-size: 13px;
            outline: none;
            transition: all 0.25s ease;
        }

        .page .search-box input:focus {
            border-color: var(--color-10-gold);
            background: var(--color-60-card);
            box-shadow: 0 0 0 4px rgba(201, 162, 39, 0.15);
        }

        .page .btn-gold-pro {
            padding: 12px 22px;
            background: linear-gradient(45deg, #C9A227, #FFD700);
            color: #000000;
            border: none;
            border-radius: 10px;
            font-weight: 800;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.25s ease;
        }

        .page .btn-gold-pro:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(201, 162, 39, 0.4);
        }

        .page .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--border-light);
        }

        .page table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }

        .page th {
            background: var(--color-30-dark);
            color: var(--color-10-gold);
            padding: 14px 16px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 800;
            white-space: nowrap;
            text-align: left;
            border-bottom: 2px solid var(--color-10-gold);
        }

        .page td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
            color: var(--text-dark);
        }

        .page tr:hover td {
            background: #FFFBEB;
        }

        .page .badge-id {
            color: var(--color-10-gold);
            font-weight: 800;
        }

        .page .btn-action {
            padding: 7px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 11px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-right: 4px;
            transition: all 0.2s ease;
        }

        .page .btn-pdf-sm { background: var(--color-30-solid); color: var(--color-10-gold); border: 1px solid var(--color-10-gold); }
        .page .btn-pdf-sm:hover { background: var(--color-10-gold); color: #000000; }

        .page .btn-edit-sm { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
        .page .btn-edit-sm:hover { background: #1D4ED8; color: #FFFFFF; }

        .page .btn-del-sm { background: #FEF2F2; color: var(--danger); border: 1px solid #FCA5A5; }
        .page .btn-del-sm:hover { background: var(--danger); color: #FFFFFF; }

        .page .alerta {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
        }

        .page .alerta.exito { background: #DCFCE7; color: var(--success); border: 1px solid #86EFAC; }
        .page .alerta.error { background: #FEF2F2; color: var(--danger); border: 1px solid #FCA5A5; }

        /* ===== Formulario de edición ===== */
        .page .card-dark-pro {
            width: 100%;
            background: var(--color-60-card);
            color: var(--text-dark);
            padding: 32px 40px;
            border-radius: 20px;
            margin-bottom: 24px;
            border: 2px solid var(--color-10-gold);
            box-shadow: var(--shadow-pro);
        }

        .page .titulo-gold {
            color: var(--text-dark);
            font-size: 18px;
            margin-bottom: 22px;
            border-bottom: 2px solid var(--color-10-gold);
            padding-bottom: 10px;
            font-weight: 800;
        }

        .page .grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 18px 22px;
            margin-bottom: 24px;
        }

        .page .col-3  { grid-column: span 3; }
        .page .col-4  { grid-column: span 4; }
        .page .col-6  { grid-column: span 6; }
        .page .col-12 { grid-column: span 12; }

        .page .form-group {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .page label {
            font-size: 12px;
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 7px;
        }

        .page input[type="text"],
        .page input[type="date"],
        .page input[type="time"],
        .page input[type="file"],
        .page select,
        .page textarea {
            width: 100%;
            padding: 13px 16px;
            background: #F8FAFC;
            border: 1.5px solid var(--border-light);
            color: var(--text-dark);
            border-radius: 10px;
            font-size: 13px;
            outline: none;
            transition: all 0.2s ease;
        }

        .page .card-dark-pro input:focus,
        .page .card-dark-pro select:focus,
        .page .card-dark-pro textarea:focus {
            border-color: var(--color-10-gold);
            background: #FFFFFF;
            box-shadow: 0 0 0 4px rgba(201, 162, 39, 0.15);
        }

        .page textarea {
            resize: vertical;
            line-height: 1.6;
        }

        .page .btn-save {
            padding: 14px 26px;
            background: linear-gradient(45deg, #C9A227, #FFD700);
            color: #000000;
            border: none;
            border-radius: 10px;
            font-weight: 800;
            cursor: pointer;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
            transition: all 0.2s ease;
        }

        .page .btn-save:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(201, 162, 39, 0.5);
        }

        .page .btn-cancel {
            padding: 14px 26px;
            background: #F1F5F9;
            color: var(--text-dark);
            text-decoration: none;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
            margin-left: 10px;
            border: 1px solid var(--border-light);
        }

        .page .btn-cancel:hover {
            background: #E2E8F0;
        }

        @media (max-width: 1100px) {
            .page .col-3,
            .page .col-4 { grid-column: span 6; }
            .page .col-6 { grid-column: span 12; }
        }

        @media (max-width: 700px) {
            .page {
                padding: 14px 14px 20px;
                gap: 14px;
            }

            .page .top-menu {
                justify-content: center;
            }

            .page .card,
            .page .card-dark-pro {
                padding: 22px 18px;
            }

            .page .col-3,
            .page .col-4,
            .page .col-6 { grid-column: span 12; }

            .page .search-box {
                max-width: 100%;
                width: 100%;
            }
        }
</style>

<div class="page">

    <!-- MENÚ HORIZONTAL -->
    <div class="top-menu">
        <div class="brand-top">
            <div class="brand-name">
                <strong>Centro Escolar Aparicio</strong>
            </div>
        </div>

        <nav class="nav-menu">
            <a href="/CEA2026/admon/incidencias/Formulario.php" class="nav-link">📝 Registrar Incidencia</a>
            <a href="/CEA2026/admon/incidencias/Registros.php" class="nav-link active">📋 Lista de Registros</a>
        </nav>
    </div>

    <main class="main-area">

        <!-- PANEL DE EDICIÓN DE INCIDENCIA COMPLETO -->
        <?php if ($incidencia_editar): ?>
        <div class="card-dark-pro">
            <h2 class="titulo-gold">✏️ Editar Incidencia #<?= $incidencia_editar['id'] ?></h2>
            <form method="POST" action="/CEA2026/admon/incidencias/Registros.php" enctype="multipart/form-data">
                <input type="hidden" name="id_edit" value="<?= $incidencia_editar['id'] ?>">

                <div class="grid">
                    <div class="form-group col-3">
                        <label>Fecha:</label>
                        <input type="date" name="fecha_incidencia" value="<?= $incidencia_editar['fecha_incidencia'] ?>" required>
                    </div>
                    <div class="form-group col-3">
                        <label>Hora:</label>
                        <input type="time" name="hora_incidencia" value="<?= $incidencia_editar['hora_incidencia'] ?>" required>
                    </div>
                    <div class="form-group col-6">
                        <label>Área y Lugar:</label>
                        <input type="text" name="area_lugar" value="<?= htmlspecialchars($incidencia_editar['area_lugar']) ?>" required>
                    </div>

                    <div class="form-group col-4">
                        <label>Tipo de Incidencia:</label>
                        <input type="text" name="tipo_incidencia" value="<?= htmlspecialchars($incidencia_editar['tipo_incidencia']) ?>" required>
                    </div>
                    <div class="form-group col-4">
                        <label>Persona Involucrada:</label>
                        <input type="text" name="nombre_involucrado" value="<?= htmlspecialchars($incidencia_editar['nombre_involucrado']) ?>" required>
                    </div>
                    <div class="form-group col-4">
                        <label>Cargo / Grupo:</label>
                        <input type="text" name="cargo_grupo" value="<?= htmlspecialchars($incidencia_editar['cargo_grupo']) ?>">
                    </div>

                    <div class="form-group col-12">
                        <label>Descripción:</label>
                        <textarea name="descripcion" rows="4" required><?= htmlspecialchars($incidencia_editar['descripcion']) ?></textarea>
                    </div>

                    <div class="form-group col-3">
                        <label>Responsable de Atención:</label>
                        <input type="text" name="responsable_atencion" value="<?= htmlspecialchars($incidencia_editar['responsable_atencion']) ?>" required>
                    </div>
                    <div class="form-group col-3">
                        <label>Prioridad:</label>
                        <select name="prioridad">
                            <option value="Baja" <?= $incidencia_editar['prioridad'] === 'Baja' ? 'selected' : '' ?>>Baja</option>
                            <option value="Media" <?= $incidencia_editar['prioridad'] === 'Media' ? 'selected' : '' ?>>Media</option>
                            <option value="Alta" <?= $incidencia_editar['prioridad'] === 'Alta' ? 'selected' : '' ?>>Alta</option>
                            <option value="Crítica" <?= $incidencia_editar['prioridad'] === 'Crítica' ? 'selected' : '' ?>>Crítica</option>
                        </select>
                    </div>
                    <div class="form-group col-3">
                        <label>Estado:</label>
                        <select name="estado">
                            <option value="Pendiente" <?= $incidencia_editar['estado'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="En proceso" <?= $incidencia_editar['estado'] === 'En proceso' ? 'selected' : '' ?>>En proceso</option>
                            <option value="Resuelto" <?= $incidencia_editar['estado'] === 'Resuelto' ? 'selected' : '' ?>>Resuelto</option>
                            <option value="Cerrada" <?= $incidencia_editar['estado'] === 'Cerrada' ? 'selected' : '' ?>>Cerrada</option>
                        </select>
                    </div>
                    <div class="form-group col-3">
                        <label>Fecha de Actualización/Cierre:</label>
                        <input type="date" name="fecha_estado" value="<?= $incidencia_editar['fecha_estado'] ?>">
                    </div>

                    <div class="form-group col-6">
                        <label>Acción Realizada:</label>
                        <textarea name="accion_realizada" rows="3"><?= htmlspecialchars($incidencia_editar['accion_realizada']) ?></textarea>
                    </div>
                    <div class="form-group col-6">
                        <label>Observaciones:</label>
                        <textarea name="observaciones" rows="3"><?= htmlspecialchars($incidencia_editar['observaciones']) ?></textarea>
                    </div>

                    <div class="form-group col-12">
                        <label>Reemplazar Adjuntar Firma (Opcional Imagen / PDF):</label>
                        <input type="file" name="archivo_firma" accept=".pdf,image/*">
                    </div>
                </div>

                <button type="submit" name="guardar_edicion" class="btn-save">Guardar Cambios</button>
                <a href="/CEA2026/admon/incidencias/Registros.php" class="btn-cancel">Cancelar</a>
            </form>
        </div>
        <?php endif; ?>

        <!-- TABLA PRINCIPAL DE REGISTROS -->
        <div class="card">
            <div class="title-bar">
                <h1>Historial General de Incidencias</h1>

                <form method="GET" action="" class="search-box">
                    <input type="text" name="buscar" placeholder="Buscar por nombre, área..." value="<?= htmlspecialchars($busqueda) ?>">
                    <button type="submit" class="btn-gold-pro">Buscar</button>
                </form>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="alerta <?= $tipo_mensaje ?>"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha/Hora</th>
                            <th>Lugar</th>
                            <th>Involucrado</th>
                            <th>Tipo</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($incidencias) > 0): ?>
                            <?php foreach ($incidencias as $inc): ?>
                                <tr>
                                    <td><span class="badge-id">#<?= $inc['id'] ?></span></td>
                                    <td><?= htmlspecialchars($inc['fecha_incidencia']) ?><br><small style="color:var(--text-muted)"><?= htmlspecialchars($inc['hora_incidencia']) ?></small></td>
                                    <td><?= htmlspecialchars($inc['area_lugar']) ?></td>
                                    <td><strong><?= htmlspecialchars($inc['nombre_involucrado']) ?></strong><br><small style="color:var(--text-muted)"><?= htmlspecialchars($inc['cargo_grupo']) ?></small></td>
                                    <td><?= htmlspecialchars($inc['tipo_incidencia']) ?></td>
                                    <td><?= htmlspecialchars($inc['prioridad']) ?></td>
                                    <td><?= htmlspecialchars($inc['estado']) ?></td>
                                    <td>
                                        <a href="/CEA2026/admon/incidencias/Registros.php?editar=<?= $inc['id'] ?>" class="btn-action btn-edit-sm">✏️ Editar</a>
                                        <a href="/CEA2026/admon/incidencias/Generar_pdf.php?id=<?= $inc['id'] ?>&amp;auto=1" target="_blank" class="btn-action btn-pdf-sm">📄 PDF</a>
                                        <a href="/CEA2026/admon/incidencias/Registros.php?eliminar=<?= $inc['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar esta incidencia?')" class="btn-action btn-del-sm">🗑️</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding: 24px; color: var(--text-muted);">No se encontraron incidencias registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
(function () {
    function ajustarPagina() {
        var nav = document.querySelector('nav.navbar');
        var page = document.querySelector('.page');
        if (!nav || !page) return;

        page.style.marginTop = '0px';

        var esFija = window.getComputedStyle(nav).position === 'fixed';
        var scroll = window.pageYOffset || 0;
        var finNav = nav.getBoundingClientRect().bottom + (esFija ? 0 : scroll);
        var inicioPage = page.getBoundingClientRect().top + scroll;

        page.style.marginTop = (finNav - inicioPage) + 'px';
    }

    window.addEventListener('load', ajustarPagina);
    window.addEventListener('resize', ajustarPagina);
})();
</script>

<?php
include_once 'pie.php';
?>
