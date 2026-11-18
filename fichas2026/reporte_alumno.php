<?php
require_once __DIR__ . '/../admon/autorizacion.php';
if (empty($_SESSION['admin']) || !in_array(rolAdmonActual(), ['admin', 'primaria', 'secundaria', 'preescolar'], true)) {
    if (empty($_SESSION['admin'])) {
        header('Location: /CEA2026/login.php');
    } else {
        header('Location: /CEA2026/admon/' . rutaInicialAdmon(rolAdmonActual()));
    }
    exit;
}
$rolReporte = rolAdmonActual();
$nivelInicialReporte = $rolReporte === 'admin' ? 'primaria' : $rolReporte;
$gradosPorNivelReporte = [
    'preescolar' => ['1°', '2°', '3°'],
    'primaria' => ['1°', '2°', '3°', '4°', '5°', '6°'],
    'secundaria' => ['1°', '2°', '3°'],
];
$adminBasePath = true;
$adminControlUrlsPage = true;
require __DIR__ . '/../admon/encabezado.php';
?>
<style>
    .reporte-page { min-height: 70vh; padding: 28px 18px 48px; background: #f3f4f6; }
    .reporte-shell { max-width: 940px; margin: 0 auto; }
    .reporte-hero { display: flex; align-items: center; gap: 16px; padding: 20px 24px; border-bottom: 4px solid #c9a227; border-radius: 16px 16px 0 0; background: linear-gradient(110deg, #080808, #242424); color: #fff; box-shadow: 0 8px 20px rgba(0,0,0,.16); }
    .reporte-escudo { display: grid; flex: 0 0 78px; width: 78px; height: 78px; place-items: center; border-radius: 50%; background: #fff; }
    .reporte-escudo img { width: 68px; height: 68px; object-fit: contain; }
    .reporte-hero h1 { margin: 0; color: #fff; font-size: clamp(1.45rem, 3vw, 2rem); font-weight: 750; }
    .reporte-hero p { margin: 6px 0 0; color: #dedede; }
    .reporte-card { padding: clamp(18px, 3vw, 30px); border-radius: 0 0 16px 16px; background: #fff; box-shadow: 0 10px 28px rgba(0,0,0,.10); }
    .reporte-intro { margin-bottom: 22px; }
    .reporte-intro strong { display: inline-block; margin-bottom: 5px; color: #8a6b12; font-size: .78rem; letter-spacing: .1em; text-transform: uppercase; }
    .reporte-intro p { margin: 0; color: #626262; }
    .reporte-section { margin: 0 0 18px; padding: 18px; border: 1px solid #e6e6e6; border-left: 4px solid #c9a227; border-radius: 12px; background: #fff; }
    .reporte-section-title { display: flex; align-items: center; gap: 10px; margin: 0 0 15px; color: #171717; font-size: 1rem; font-weight: 750; }
    .reporte-section-title i { display: grid; flex: 0 0 36px; width: 36px; height: 36px; place-items: center; border-radius: 10px; background: #111; color: #f0ca54; }
    .reporte-page .form-label { margin-bottom: 7px; color: #353535; font-weight: 650; }
    .reporte-page .form-control, .reporte-page .form-select, .reporte-page .input-group-text { min-height: 42px; border-color: #d6d8dc; background-color: #fff; }
    .reporte-page .input-group-text { min-width: 46px; justify-content: center; color: #8a6b12; }
    .reporte-page .form-control:focus, .reporte-page .form-select:focus { border-color: #c9a227; box-shadow: 0 0 0 .2rem rgba(201,162,39,.18); }
    .reporte-hint { display: block; margin-top: 6px; color: #717781; font-size: .82rem; }
    .reporte-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 20px; border-top: 1px solid #e7e7e7; }
    .reporte-cancel { border-color: #222; color: #222; }
    .reporte-cancel:hover { background: #222; color: #fff; }
    .reporte-submit { min-height: 48px; padding-inline: 22px; border: 0; background: linear-gradient(45deg, #c9a227, #f1d36a); color: #151515; font-weight: 750; }
    .reporte-submit:hover { background: linear-gradient(45deg, #b28b16, #e6c44e); color: #111; }
    @media (max-width: 575.98px) {
        .reporte-page { padding: 22px 12px 42px; }
        .reporte-hero { align-items: flex-start; padding: 20px 16px; }
        .reporte-escudo { flex-basis: 58px; width: 58px; height: 58px; }
        .reporte-escudo img { width: 50px; height: 50px; }
        .reporte-section { padding: 17px 14px; }
        .reporte-actions { flex-direction: column-reverse; }
        .reporte-actions > * { width: 100%; }
    }
</style>

<main class="reporte-page">
    <div class="reporte-shell">
        <header class="reporte-hero">
            <div class="reporte-escudo"><img src="/CEA2026/IMG/logo.png" alt="Escudo del Centro Escolar Aparicio" width="68" height="68"></div>
            <div>
                <h1>Generar hoja de reporte</h1>
                <p>Captura la información para generar el reporte disciplinario del alumno.</p>
            </div>
        </header>

        <section class="reporte-card">
            <div class="reporte-intro">
                <strong>Nuevo reporte</strong>
                <p>Completa los datos del alumno, la fecha y la descripción de la conducta reportada.</p>
            </div>

            <form action="/CEA2026/fichas2026/generar_pdf_reporte.php" method="POST" target="_blank" onsubmit="return validarFormulario();">
                <section class="reporte-section" aria-labelledby="reporte-alumno-titulo">
                    <h2 class="reporte-section-title" id="reporte-alumno-titulo"><i class="fas fa-user-graduate" aria-hidden="true"></i>Información del alumno</h2>
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label" for="nivel">Categoría escolar</label>
                            <?php if ($rolReporte === 'admin') { ?>
                            <select id="nivel" name="nivel" class="form-select" required>
                                <option value="preescolar">Preescolar</option>
                                <option value="primaria" selected>Primaria</option>
                                <option value="secundaria">Secundaria</option>
                            </select>
                            <?php } else { ?>
                            <input type="hidden" name="nivel" value="<?= htmlspecialchars($nivelInicialReporte, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="form-control bg-light" aria-label="Categoría escolar"><?= htmlspecialchars(ucfirst($nivelInicialReporte), ENT_QUOTES, 'UTF-8') ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="nombre_alumno">Nombre completo del alumno(a)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user" aria-hidden="true"></i></span>
                                <input id="nombre_alumno" type="text" name="nombre_alumno" class="form-control" placeholder="Ej. Juan Pérez González" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="grado">Grado</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-graduation-cap" aria-hidden="true"></i></span>
                                <select id="grado" name="grado" class="form-select" required>
                                    <option value="" selected disabled>Seleccionar grado</option>
                                    <?php foreach ($gradosPorNivelReporte[$nivelInicialReporte] as $gradoOpcion) { ?>
                                    <option value="<?= htmlspecialchars($gradoOpcion, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($gradoOpcion, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="grupo">Grupo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-users" aria-hidden="true"></i></span>
                                <select id="grupo" name="grupo" class="form-select" required>
                                    <option value="" selected disabled>Seleccionar grupo</option>
                                    <option value="A">A</option><option value="B">B</option><option value="C">C</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="reporte-section" aria-labelledby="reporte-fecha-titulo">
                    <h2 class="reporte-section-title" id="reporte-fecha-titulo"><i class="fas fa-calendar-alt" aria-hidden="true"></i>Fecha de emisión</h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="dia">Día</label>
                            <input id="dia" type="number" name="dia" class="form-control" placeholder="Ej. 15" min="1" max="31" value="<?php echo date('d'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="mes">Mes</label>
                            <select id="mes" name="mes" class="form-select" required>
                                <option value="" selected disabled>Seleccionar mes</option>
                                <option value="Enero">Enero</option><option value="Febrero">Febrero</option><option value="Marzo">Marzo</option>
                                <option value="Abril">Abril</option><option value="Mayo">Mayo</option><option value="Junio">Junio</option>
                                <option value="Julio">Julio</option><option value="Agosto">Agosto</option><option value="Septiembre">Septiembre</option>
                                <option value="Octubre">Octubre</option><option value="Noviembre">Noviembre</option><option value="Diciembre">Diciembre</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="anio">Año</label>
                            <input type="text" name="anio" id="anio" class="form-control" value="<?php echo date('y'); ?>" placeholder="Ej. 26" maxlength="2" minlength="2" pattern="[0-9]{2}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2)" title="Ingrese únicamente dos números" required>
                            <small class="reporte-hint">Escribe los dos últimos dígitos del año.</small>
                        </div>
                    </div>
                </section>

                <section class="reporte-section" aria-labelledby="reporte-descripcion-titulo">
                    <h2 class="reporte-section-title" id="reporte-descripcion-titulo"><i class="fas fa-clipboard-list" aria-hidden="true"></i>Descripción del reporte</h2>
                    <div class="mb-3">
                        <label class="form-label" for="conducta">Conducta reportada</label>
                        <textarea name="conducta" id="conducta" class="form-control" rows="4" placeholder="Describa el motivo o la falta reportada..." oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '')" title="Solo se permiten letras y espacios" required></textarea>
                        <small class="reporte-hint">Solo se permiten letras y espacios.</small>
                    </div>
                    <div>
                        <label class="form-label" for="quien_reporta">Nombre y cargo de quien reporta</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user-shield" aria-hidden="true"></i></span>
                            <input type="text" name="quien_reporta" id="quien_reporta" class="form-control" placeholder="Ej. Carlos Ramírez, docente" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '')" title="Solo se permiten letras y espacios" required>
                        </div>
                        <small class="reporte-hint">Solo se permiten letras y espacios.</small>
                    </div>
                </section>

                <div class="reporte-actions">
                    <a href="/CEA2026/admon/<?php echo htmlspecialchars(rutaInicialAdmon(rolAdmonActual()), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary btn-lg reporte-cancel"><i class="fas fa-times me-2" aria-hidden="true"></i>Volver</a>
                    <a href="/CEA2026/admon/historial_reportes.php" class="btn btn-outline-dark btn-lg"><i class="fas fa-folder-open me-2" aria-hidden="true"></i>Ver reportes</a>
                    <button type="submit" class="btn btn-lg reporte-submit"><i class="fas fa-file-pdf me-2" aria-hidden="true"></i>Guardar y generar PDF</button>
                </div>
            </form>
        </section>
    </div>
</main>

<script>
function validarFormulario() {
    const anio = document.getElementById('anio').value;
    const conducta = document.getElementById('conducta').value;
    const quienReporta = document.getElementById('quien_reporta').value;

    if (!/^[0-9]{2}$/.test(anio)) {
        alert('El año debe contener exactamente dos números.');
        document.getElementById('anio').focus();
        return false;
    }
    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(conducta)) {
        alert('La conducta reportada solo debe contener letras y espacios.');
        document.getElementById('conducta').focus();
        return false;
    }
    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/.test(quienReporta)) {
        alert('El nombre de quien reporta solo debe contener letras y espacios.');
        document.getElementById('quien_reporta').focus();
        return false;
    }
    return true;
}

const nivelReporteSelect = document.getElementById('nivel');
const gradoReporteSelect = document.getElementById('grado');
if (nivelReporteSelect && gradoReporteSelect) {
    const gradosPorNivel = {
        preescolar: ['1°', '2°', '3°'],
        primaria: ['1°', '2°', '3°', '4°', '5°', '6°'],
        secundaria: ['1°', '2°', '3°']
    };
    nivelReporteSelect.addEventListener('change', () => {
        gradoReporteSelect.innerHTML = '<option value="" selected disabled>Seleccionar grado</option>';
        (gradosPorNivel[nivelReporteSelect.value] || []).forEach(grado => {
            const opcion = document.createElement('option');
            opcion.value = grado;
            opcion.textContent = grado;
            gradoReporteSelect.appendChild(opcion);
        });
    });
}
</script>

<?php require __DIR__ . '/../admon/pie.php'; ?>
