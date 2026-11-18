<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/institucional.php';



pims_exigir_login();
pims_exigir_permiso('acta.registrar');

$institucion = pims_institucion();
$activo = 'sistema';

// El folio lo asigna la base de datos al guardar, no el usuario.
// Aqui solo se anuncia que es automatico.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Sistema Escolar | Acta de Hechos
    </title>

    <link
        rel="icon"
        type="image/png"
        href="assets/img/logo.png"
    >

    <link
        rel="apple-touch-icon"
        href="assets/img/logo.png"
    >

    <link
        rel="stylesheet"
        href="assets/css/tokens.css?v=iconos-1"
    >

    <link
        rel="stylesheet"
        href="assets/css/sistema.css?v=fondo-blanco-1"
    >
    <link rel="stylesheet" href="assets/css/iconos.css?v=iconos-1">
<?php pims_institucional_assets(); ?>
</head>

<body class="cea-shell">

<div class="app">

    <!-- =========================
         HEADER
    ========================== -->

    <?php pims_institucional_cabecera(true); ?>


    <!-- =========================
         CONTENIDO
    ========================== -->

    <div class="layout layout-sin-sidebar">



        <!-- =====================
             ÁREA PRINCIPAL
        ====================== -->

        <main class="main">

            <div class="breadcrumb">
                Inicio / Acta de hechos
            </div>


            <div class="page-header">

                <div>

                    <h2>
                        Acta de Hechos
                    </h2>

                    <p>
                        Registra la información correspondiente
                        al acontecimiento escolar.
                    </p>

                </div>


                <div class="folio">
                    FOLIO AUTOMÁTICO AL GUARDAR
                </div>

            </div>

            <form
                id="actaForm"
                class="acta-form-ui"
                action="guardar_acta.php"
                method="post"
            >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(pims_csrf_token()) ?>"
            >


            <!-- =====================
                 DATOS DEL ALUMNO
            ====================== -->

            <section class="card">

                <div class="card-header">

                    <div class="section-number">
                        1
                    </div>

                    <div>

                        <h3>
                            Datos del alumno
                        </h3>

                        <p>
                            Información general del estudiante
                        </p>

                    </div>

                </div>


                <div class="card-body">

                    <div class="grid-2">

                        <div class="field full">
                            <label for="nivel">Nivel educativo <span class="required">*</span></label>
                            <select id="nivel" name="nivel" required>
                                <option value="">Seleccionar nivel</option>
                                <?php foreach ($institucion['niveles'] as $nivel): ?>
                                    <option value="<?= e($nivel) ?>" data-clave="<?= e($institucion['claves_sep'][$nivel] ?? '') ?>"><?= e($nivel) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="field-hint">Selecciona el nivel antes de buscar la matrícula.</span>
                        </div>

                        <div class="field full">

                            <label for="matricula">

                                Matricula del alumno

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                type="text"
                                id="matricula"
                                name="matricula"
                                value="<?= e(preg_match('/^[A-Za-z0-9]{1,5}$/', pims_input($_GET, 'matricula')) ? strtoupper(pims_input($_GET, 'matricula')) : '') ?>"
                                placeholder="Ingrese la matricula del alumno"
                                inputmode="text"
                                autocomplete="off"
                                maxlength="5"
                                pattern="[A-Za-z0-9]{1,5}"
                                aria-describedby="alumnoLookupStatus"
                                required
                            >

                        </div>


                        <div class="field">

                            <label for="nombre">
                                Nombre completo
                            </label>

                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                placeholder="Nombre completo del alumno"
                                maxlength="255"
                                readonly
                                required
                            >

                        </div>


                        <div class="field">

                            <label for="fecha">
                                Fecha de emisión
                            </label>

                            <input
                                type="date"
                                id="fecha"
                                name="fecha"
                                value="<?= e(date('Y-m-d')) ?>"
                                required
                            >

                        </div>

                    </div>

                    <p
                        id="alumnoLookupStatus"
                        class="lookup-status"
                        role="status"
                        aria-live="polite"
                    ></p>


                    <div class="grid-2 second-grid">
                        <div class="field">

                            <label for="grado">
                                Grado
                            </label>

                            <select
                                id="grado"
                                name="grado"
                            >

                                <option value="">
                                    Seleccionar grado
                                </option>

                            </select>

                            <span
                                class="field-hint"
                                id="gradoHint"
                            >
                                Elige primero el nivel educativo.
                            </span>

                        </div>


                        <div class="field">

                            <label for="grupo">
                                Grupo
                            </label>

                            <select
                                id="grupo"
                                name="grupo"
                            >

                                <option value="">
                                    Seleccionar grupo
                                </option>

                                <?php foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $letra): ?>
                                    <option value="<?= e($letra) ?>">
                                        <?= e($letra) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                </div>

            </section>



            <!-- =====================
                 MOTIVO
            ====================== -->

            <section class="card">

                <div class="card-header">

                    <div class="section-number">
                        2
                    </div>

                    <div>

                        <h3>
                            Motivo del acta
                        </h3>

                        <p>
                            Seleccione el motivo correspondiente
                        </p>

                    </div>

                </div>


                <div class="card-body">

                    <div class="option-grid">


                        <label class="option-card">

                            <input
                                type="radio"
                                name="motivo"
                                value="expulsion"
                            >

                            <div class="option-info">

                                <strong>
                                    Expulsión
                                </strong>

                                <span>
                                    Separación del plantel
                                </span>

                            </div>

                        </label>


                        <label class="option-card">

                            <input
                                type="radio"
                                name="motivo"
                                value="suspension"
                            >

                            <div class="option-info">

                                <strong>
                                    Suspensión
                                </strong>

                                <span>
                                    Suspensión temporal
                                </span>

                            </div>

                        </label>


                        <label class="option-card">

                            <input
                                type="radio"
                                name="motivo"
                                value="salida"
                            >

                            <div class="option-info">

                                <strong>
                                    Salida
                                </strong>

                                <span>
                                    Salida extraordinaria
                                </span>

                            </div>

                        </label>


                        <label class="option-card">

                            <input
                                type="radio"
                                name="motivo"
                                value="permiso"
                            >

                            <div class="option-info">

                                <strong>
                                    Permiso
                                </strong>

                                <span>
                                    Autorización especial
                                </span>

                            </div>

                        </label>

                    </div>

                </div>

            </section>



            <!-- =====================
                 DESCRIPCIÓN
            ====================== -->

            <section class="card">

                <div class="card-header">

                    <div class="section-number">
                        3
                    </div>

                    <div>

                        <h3>
                            Descripción de los hechos
                        </h3>

                        <p>
                            Registre detalladamente lo ocurrido
                        </p>

                    </div>

                </div>


                <div class="card-body">

                    <div class="field">

                        <label for="hechos">

                            Relato de los hechos

                            <span class="required">
                                *
                            </span>

                        </label>

                        <textarea
                            id="hechos"
                            name="hechos"
                            placeholder="Describa de forma clara, objetiva y cronológica los hechos ocurridos..."
                            required
                        ></textarea>

                    </div>


                    <div class="field field-spacing">

                        <label for="acciones">
                            En virtud de lo anterior, se procede a
                        </label>

                        <textarea
                            id="acciones"
                            name="acciones"
                            class="textarea-small"
                            placeholder="Indique las medidas, acuerdos o acciones tomadas..."
                        ></textarea>

                    </div>

                </div>

            </section>



            <!-- =====================
                 COMPARECENCIA Y CIERRE
            ====================== -->

            <section class="card">

                <div class="card-header">

                    <div class="section-number">
                        4
                    </div>

                    <div>

                        <h3>
                            Comparecencia y cierre
                        </h3>

                        <p>
                            Quién suscribe y cuándo concluye el acta
                        </p>

                    </div>

                </div>


                <div class="card-body">

                    <div class="field">

                        <label for="suscriptor">

                            Nombre de quien suscribe

                            <span class="required">
                                *
                            </span>

                        </label>

                        <input
                            type="text"
                            id="suscriptor"
                            name="suscriptor"
                            placeholder="Nombre completo"
                            maxlength="255"
                            value="<?= e($_SESSION['pims_nombre'] ?? '') ?>"
                            required
                        >

                    </div>

                    <div class="grid-2 field-spacing">

                        <div class="field">

                            <label for="hora">
                                Hora del acontecimiento
                            </label>

                            <input
                                type="time"
                                id="hora"
                                name="hora"
                                class="signature-input"
                            >

                            <span class="field-hint">
                                En el documento se imprime desglosada en hora y minutos.
                            </span>

                        </div>

                        <div class="field">

                            <label for="hora_conclusion">
                                Hora de conclusión
                            </label>

                            <input
                                type="time"
                                id="hora_conclusion"
                                name="hora_conclusion"
                            >

                        </div>

                    </div>

                </div>

            </section>


            <!-- =====================
                 BOTONES
            ====================== -->

            <div class="form-actions">

                <button
                    class="btn btn-secondary"
                    type="reset"
                >
                    <?= pims_icono('limpiar') ?> Limpiar
                </button>

                <button
                    class="btn btn-preview"
                    type="button"
                    id="previewActa"
                >
                    <?= pims_icono('vista') ?> Vista previa PDF
                </button>

                <button
                    class="btn btn-primary"
                    type="submit"
                    id="saveActa"
                >
                    <?= pims_icono('guardar') ?> Guardar acta
                </button>

            </div>

            <p id="formStatus" class="form-status" role="status" aria-live="polite"></p>

            </form>

        </main>

    </div>


    <!-- =========================
         FOOTER
    ========================== -->
    <?php pims_institucional_pie(); ?>

</div>

<div id="previewModal" class="preview-modal" hidden>
    <div class="preview-modal-backdrop" data-close-preview></div>
    <section class="preview-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="previewModalTitle">
        <header class="preview-modal-header">
            <h2 id="previewModalTitle">Vista previa del acta</h2>
            <button type="button" class="preview-modal-close" data-close-preview aria-label="Cerrar vista previa">×</button>
        </header>
        <iframe
            id="previewFrame"
            name="actaPreviewFrame"
            title="Vista previa del acta"
            loading="eager"
        ></iframe>
    </section>
</div>

<script src="assets/js/acta-form.js?v=matricula-alfanumerica-1"></script>
</body>
</html>
