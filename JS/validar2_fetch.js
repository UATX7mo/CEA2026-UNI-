document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById("formdatos");
    const btnGuardar = document.getElementById('btnguardar');

    // ===========================
    // Funciones auxiliares
    // ===========================
    const soloLetras = texto => /^[\p{L}\s]+$/u.test(texto);
    const soloNumeros = texto => /^[0-9]+$/.test(texto);
    const validarCURP = curp => /^[A-Z]{4}\d{6}[HM]{1}[A-Z]{5}[0-9A-Z]{2}$/i.test(curp);
    const camposVacios = obj => Object.values(obj).every(v => v.trim() === "" || v === null);

    const validarPersona = (persona, rol) => {
        let errores = [];
        const camposObligatorios = Object.entries(persona).filter(([k, v]) => !camposVacios({ [k]: v }));
        if (!camposVacios(persona) && camposObligatorios.length !== Object.keys(persona).length) {
            errores.push(`• Si captura datos del ${rol} debe llenar todos los campos requeridos`);
        }
        if (persona.curp && persona.curp.length !== 18) {
            errores.push(`• El CURP del ${rol} debe tener 18 caracteres`);
        } else if (persona.curp && !validarCURP(persona.curp)) {
            errores.push(`• CURP del ${rol} inválido`);
        }
        return errores;
    };

    // ===========================
    // Función principal de validación
    // ===========================
    const validar = () => {
        let errores = [];

        // Alumno
        const alumno = {
            nombre: form.nombre_alum.value,
            apePat: form.ape_pat.value,
            apeMat: form.ape_mat.value,
            curp: form.curp_alum.value.replace(/\s+/g, ""),
            sexo: form.sexo.value,
            nacimiento: form.nacimiento_alum.value,
            grado: form.grado.value,
            grupo: form.grupo.value,
            municipioNac: form.muni_alum_nac.value,
            estadoNac: form.estado_alum_nac.value,
            escuela: form.escuela_proce.value
        };
        for (const [campo, valor] of Object.entries(alumno)) {
            if (!valor.trim()) errores.push(`• ${campo} vacío`);
        }
        if (alumno.curp && !validarCURP(alumno.curp)) errores.push("• CURP inválido");

        // Domicilio
        const domicilio = {
            calle: form.calle_alum.value,
            colonia: form.colo_alum.value,
            municipio: form.muni_alum_rad.value,
            estado: form.estado_alum_rad.value,
            cp: form.cp.value,
            telCasa: form.tel_casa_alum.value,
            telCel: form.tel_cel_alum.value,
            peso: form.peso.value,
            talla: form.talla.value
        };
        for (const [campo, valor] of Object.entries(domicilio)) {
            if (!valor.trim()) errores.push(`• ${campo} vacío`);
            if ((campo === 'cp' || campo.includes('tel') || campo === 'peso' || campo === 'talla') && valor && !soloNumeros(valor)) {
                errores.push(`• ${campo} solo debe contener números`);
            }
        }

        // Salud
        const salud = {
            alergias: form.alergias.value,
            sangre: form.sangre.value,
            enfermedades: form.enfermedades.value,
            religion: form.religion.value
        };
        for (const [campo, valor] of Object.entries(salud)) {
            if (!valor.trim()) errores.push(`• ${campo} vacío`);
        }

        // Personas
        const personas = [
            { obj: { curp: form.curp_papa.value, nombre: form.nombre_papa.value, nacimiento: form.nacimiento_papa.value, estado: form.estado_papa.value, calle: form.calle_papa.value, colonia: form.colonia_papa.value, civil: form.estado_civ_papa.value, ocupacion: form.ocupacion_papa.value, escolaridad: form.escolaridad_papa.value, telCasa: form.tel_casa_papa.value, email: form.email_papa.value }, rol: 'padre' },
            { obj: { curp: form.curp_mama.value, nombre: form.nombre_mama.value, nacimiento: form.nacimiento_mama.value, estado: form.estado_mama.value, calle: form.calle_mama.value, colonia: form.colo_mama.value, civil: form.civil_mama.value, ocupacion: form.ocupacion_mama.value, escolaridad: form.esco_mama.value, telCasa: form.tel_casa_mama.value, email: form.email_mama.value }, rol: 'madre' },
            { obj: { curp: form.curp_tutor.value, nombre: form.nombre_tutor.value, nacimiento: form.nacimiento_tutor.value, estado: form.estado_tutor.value, calle: form.calle_tutor.value, colonia: form.colo_tutor.value, civil: form.estado_civ_tutor.value, ocupacion: form.ocupacion_tutor.value, escolaridad: form.esco_tutor.value, telCasa: form.tel_casa_tutor.value, email: form.email_tutor.value }, rol: 'tutor' }
        ];

        if (personas.every(p => camposVacios(p.obj))) {
            errores.push("• Debe capturar los datos al menos de una persona: padre, madre o tutor");
        }

        personas.forEach(p => {
            errores.push(...validarPersona(p.obj, p.rol));
        });

        return errores;
    };

    // ===========================
    // Evento botón guardar
    // ===========================
    btnGuardar.addEventListener('click', (e) => {
        e.preventDefault();
        const errores = validar();

        if (errores.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Se encontraron errores',
                html: errores.join("<br>"),
                confirmButtonText: 'Aceptar'
            });
            return; // No continuar si hay errores
        }

        // No hay errores → enviar datos
        const datos = new FormData(form);
        fetch('insertar_ficha_primaria.php', { method: 'POST', body: datos })
            .then(response => response.text())
            .then(r => {
                if (r.trim() === '1') {
                    Swal.fire('Éxito', 'Registro exitoso', 'success').then(() => {
                        window.location.href = 'index.php';
                    });
                } else if (r.trim() === '0') {
                    Swal.fire('Error', 'Falló el registro', 'error');
                } else{
                    Swal.fire('Atención', 'CURP ya registrado', 'warning');
                }
            })
            .catch(error => {
                console.error('Error en la petición:', error);
                Swal.fire('Error', 'Error en la conexión', 'error');
            });
    });

});
