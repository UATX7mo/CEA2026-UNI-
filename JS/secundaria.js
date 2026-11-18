// Espera a que todo el documento HTML esté completamente cargado
document.addEventListener("DOMContentLoaded", () => {
  // Referencia al formulario principal
  const form = document.getElementById("formdatos");

  // Referencia al botón Guardar
  const btnGuardar = document.getElementById("btnguardar");

  // =====================================================
  // FUNCIONES AUXILIARES DE VALIDACIÓN
  // =====================================================

  // Valida que solo existan letras y espacios
  // \p{L} permite letras con acentos (á, é, ñ, etc.)
  const soloLetras = (texto) => /^[\p{L}\s]+$/u.test(texto);

  // Valida que solo existan números
  const soloNumeros = (texto) => /^[0-9]+$/.test(texto);

  // Valida el formato oficial del CURP mexicano
  const validarCURP = (curp) =>
    /^[A-Z]{4}\d{6}[HM]{1}[A-Z]{5}[0-9A-Z]{2}$/i.test(curp);

  // Verifica si todos los valores de un objeto están vacíos
  // Devuelve true si TODOS están vacíos
  const camposVacios = (obj) =>
    Object.values(obj).every((v) => !v || v.trim() === "");

  // =====================================================
  // FUNCIÓN PARA VALIDAR PADRE / MADRE / TUTOR
  // =====================================================
  const validarPersona = (persona, rol) => {
    let errores = [];

    // Si la persona tiene algún campo capturado
    if (!camposVacios(persona)) {
      // Verifica si hay algún campo vacío
      const incompletos = Object.values(persona).some((v) => v.trim() === "");

      // Si hay campos incompletos → error
      if (incompletos) {
        errores.push(
          `• Si captura datos del ${rol} debe llenar todos los campos`,
        );
      }

      // Validación del CURP si existe
      if (persona.curp) {
        // Longitud incorrecta
        if (persona.curp.length !== 18) {
          errores.push(`• El CURP del ${rol} debe tener 18 caracteres y sin espacios`);
        }
        // Formato incorrecto
        else if (!validarCURP(persona.curp)) {
          errores.push(`• CURP del ${rol} inválido`);
        }
      }

      // SOLO LETRAS
      if (persona.nombre && !soloLetras(persona.nombre)) {
        errores.push(`• El nombre del ${rol} solo debe contener letras`);
      }

      // SOLO NUMEROS
      if (persona.telefono && !soloNumeros(persona.telefono)) {
        errores.push(`• El teléfono del ${rol} solo debe contener números`);
      }
    }

    return errores;
  };

  // =====================================================
  // FUNCIÓN PRINCIPAL DE VALIDACIÓN GENERAL
  // =====================================================
  const validar = () => {
    let errores = [];

    // =====================
    // VALIDACIÓN DEL ALUMNO
    // =====================
    const alumno = {
      nombre: form.nombre_alum.value,
      apePat: form.ape_pat.value,
      apeMat: form.ape_mat.value,
      // Elimina espacios en blanco del CURP
      curp: form.curp_alum.value.replace(/\s+/g, ""),
      sexo: form.sexo.value,
      nacimiento: form.nacimiento_alum.value,
      municipioNac: form.muni_alum_nac.value,
      estadoNac: form.estado_alum_nac.value,
      calle: form.calle_alum.value,
      colonia: form.colo_alum.value,
      muni_alum: form.muni_alum_rad.value,
      estado_alum_rad: form.estado_alum_rad.value,
      cp: form.cp.value,
      tel_casa_alum: form.tel_casa_alum.value,
      tel_cel_alum: form.tel_cel_alum.value,
      alergias: form.alergias.value,
      sangre: form.sangre.value,
      peso: form.peso.value,
      talla: form.talla.value,
      enfermedades: form.enfermedades.value,
      grado: form.grado.value,
      grupo: form.grupo.value,
      escuela: form.escuela_proce.value,
      religion: form.religion.value,
    };

    // Recorre cada campo del alumno
    for (const [campo, valor] of Object.entries(alumno)) {
      // Si el campo está vacío → error
      if (!valor.trim()) {
        errores.push(`• ${campo} vacío`);
      }
    }

    // =====================
    // SOLO LETRAS (ALUMNO)
    // =====================
    const camposSoloLetrasAlumno = [
      "nombre",
      "apePat",
      "apeMat",
      "muni_alum_nac",
      "muni_alum_rad",
      "alergias",
      "enfermedades",
      "religion",
      "nombre_papa",
      "ocupacion_papa",
      "escolaridad_papa",
      "nombre_mama",
      "nacimiento_mama",
      "ocupacion_mama",
      "esco_mama",
      "nombre_tutor",
      "ocupacion_tutor",
      "esco_tutor",
    ];

    camposSoloLetrasAlumno.forEach((campo) => {
      if (alumno[campo] && !soloLetras(alumno[campo])) {
        errores.push(`• ${campo} solo debe contener letras`);
      }
    });

    // Validación del CURP
    if (alumno.curp && !validarCURP(alumno.curp)) {
      errores.push("• CURP inválido");
    }

    // =====================
    // SOLO NUMEROS (ALUMNO)
    // =====================
    const camposSoloNumerosAlumno = [
      "cp",
      "tel_casa_alum",
      "tel_cel_alum",
      "tel_casa_papa",
      "tel_casa_mama",
      "tel_casa_tutor",
    ];

    camposSoloNumerosAlumno.forEach((campo) => {
      if (alumno[campo] && !soloNumeros(alumno[campo])) {
        errores.push(`• ${campo} solo debe contener números`);
      }
    });

    // =====================
    // VALIDACIÓN PERSONAS
    // =====================
    const personas = [
      {
        obj: {
          curp: form.curp_papa.value,
          nombre: form.nombre_papa.value,
          nacimiento: form.nacimiento_papa.value,
          estado: form.estado_papa.value,
          calle: form.calle_papa.value,
          colonia: form.colonia_papa.value,
          estado_civ: form.estado_civ_papa.value,
          ocupacion: form.ocupacion_papa.value,
          escolaridad: form.escolaridad_papa.value,
          telefono: form.tel_casa_papa.value,
          email: form.email_papa.value,
        },
        rol: "padre",
      },
      {
        obj: {
          curp: form.curp_mama.value,
          nombre: form.nombre_mama.value,
          nacimiento: form.nacimiento_mama.value,
          estado: form.estado_mama.value,
          calle: form.calle_mama.value,
          colonia: form.colo_mama.value,
          estado_civ: form.civil_mama.value,
          ocupacion: form.ocupacion_mama.value,
          escolaridad: form.esco_mama.value,
          telefono: form.tel_casa_mama.value,
          email: form.email_mama.value,
        },
        rol: "madre",
      },
      {
        obj: {
          curp: form.curp_tutor.value,
          nombre: form.nombre_tutor.value,
          nacimiento: form.nacimiento_tutor.value,
          estado: form.estado_tutor.value,
          calle: form.calle_tutor.value,
          colonia: form.colo_tutor.value,
          estado_civ: form.estado_civ_tutor.value,
          ocupacion: form.ocupacion_tutor.value,
          escolaridad: form.esco_tutor.value,
          telefono: form.tel_casa_tutor.value,
          email: form.email_tutor.value,
        },
        rol: "tutor",
      },
    ];

    // Si los tres están completamente vacíos → error obligatorio
    if (personas.every((p) => camposVacios(p.obj))) {
      errores.push("• Debe capturar los datos de algun familiar ya sea padre, madre o tutor");
    }

    // Valida cada persona individualmente
    personas.forEach((p) => {
      errores.push(...validarPersona(p.obj, p.rol));
    });

    return errores;
  };

  // =====================================================
  // EVENTO DEL BOTÓN GUARDAR CON ASYNC/AWAIT
  // =====================================================
  btnGuardar.addEventListener("click", async (e) => {
    // Evita envío automático del formulario
    e.preventDefault();

    // Ejecuta validaciones
    const errores = validar();

    // Si hay errores, muestra modal y detiene ejecución
    if (errores.length > 0) {
      Swal.fire({
        icon: "error",
        title: "Se encontraron errores",
        html: errores.join("<br>"),
      });

      return; // NO continúa
    }

    // =====================================================
    // SI NO HAY ERRORES → ENVÍA DATOS AL SERVIDOR
    // =====================================================

    try {
      // Crea objeto FormData con todos los datos del formulario
      const datos = new FormData(form);

      // Envia datos al archivo PHP
      // await hace que espere la respuesta antes de continuar
      const response = await fetch("insertar_ficha_secundaria.php", {
        method: "POST",
        body: datos,
      });

      // Espera el texto que devuelve el servidor
      const r = (await response.text()).trim();

      // ============================
      // MANEJO DE RESPUESTA
      // ============================

      if (r === "1") {
        // Espera a que el usuario cierre el modal
        await Swal.fire("Éxito", "Registro exitoso", "success");

        // Redirige después de cerrar el modal
        window.location.href = "index.php";
      } else if (r === "0") {
        Swal.fire("Error", "Falló el registro", "error");
      } else {
        Swal.fire("Atención", "CURP ya registrado", "warning");
      }
    } catch (error) {
      // Si hay error de conexión o del servidor
      console.error("Error en la petición:", error);

      Swal.fire("Error", "Error en la conexión", "error");
    }
  });
});
