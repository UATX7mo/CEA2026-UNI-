// Espera a que todo el documento HTML esté completamente cargado
document.addEventListener("DOMContentLoaded", () => {
  // Referencia al formulario principal
  const form = document.getElementById("actualizar");

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
    Object.values(obj).every((v) => v.trim() === "" || v === null);

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
      grado: form.grado.value,
      grupo: form.grupo.value,
      municipioNac: form.muni_alum_nac.value,
      estadoNac: form.estado_alum_nac.value,
      escuela: form.escuela_proce.value,
    };

    // Recorre cada campo del alumno
    for (const [campo, valor] of Object.entries(alumno)) {
      // Si el campo está vacío → error
      if (!valor.trim()) {
        errores.push(`• ${campo} vacío`);
      }
    }

    // Validación del CURP
    if (alumno.curp && !validarCURP(alumno.curp)) {
      errores.push("• CURP inválido");
    }

    // =====================
    // VALIDACIÓN PERSONAS
    // =====================
    const personas = [
      {
        obj: {
          curp: form.curp_papa.value,
          nombre: form.nombre_papa.value,
        },
        rol: "padre",
      },
      {
        obj: {
          curp: form.curp_mama.value,
          nombre: form.nombre_mama.value,
        },
        rol: "madre",
      },
      {
        obj: {
          curp: form.curp_tutor.value,
          nombre: form.nombre_tutor.value,
        },
        rol: "tutor",
      },
    ];

    // Si los tres están completamente vacíos → error obligatorio
    if (personas.every((p) => camposVacios(p.obj))) {
      errores.push("• Debe capturar al menos padre, madre o tutor");
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
      const response = await fetch("../admon/modif_pres.php", {
        method: "POST",
        body: datos,
      });

      // Espera el texto que devuelve el servidor
      const r = (await response.text()).trim();

      // ============================
      // MANEJO DE RESPUESTA
      // ============================

      if (r === "3") {
        // Espera a que el usuario cierre el modal
        await Swal.fire("Éxito", "Actualizacion Exitosa", "success");

        // Redirige después de cerrar el modal
        window.location.href = "index.php";
      } else if (r === "4") {
        Swal.fire("Error", "Falló en la Actualizacion", "error");
      }
    } catch (error) {
      // Si hay error de conexión o del servidor
      console.error("Error en la petición:", error);

      Swal.fire("Error", "Error en la conexión", "error");
    }
  });
});
