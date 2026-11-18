(function () {
  const form = document.querySelector("#actaForm");

  if (!form) {
    return;
  }

  const GRADOS = {
    "Preescolar": ["1°", "2°", "3°"],
    "Primaria": ["1°", "2°", "3°", "4°", "5°", "6°"],
    "Secundaria": ["1°", "2°", "3°"]
  };

  const nivel = form.querySelector("#nivel");
  const grado = form.querySelector("#grado");
  const grupo = form.querySelector("#grupo");
  const gradoHint = document.querySelector("#gradoHint");
  const matricula = form.querySelector("#matricula");
  const nombreAlumno = form.querySelector("#nombre");
  const lookupStatus = document.querySelector("#alumnoLookupStatus");
  const previewButton = form.querySelector("#previewActa");
  const previewModal = document.querySelector("#previewModal");
  const previewFrame = document.querySelector("#previewFrame");
  const formStatus = document.querySelector("#formStatus");
  let consultaActual = null;
  let guardandoActa = false;

  function llenarGrados(conservar) {
    const opciones = GRADOS[nivel.value] || [];

    grado.innerHTML = "";

    const vacio = document.createElement("option");
    vacio.value = "";
    vacio.textContent = opciones.length > 0 ? "Seleccionar grado" : "Elige primero el nivel";
    grado.appendChild(vacio);

    opciones.forEach(function (etiqueta) {
      const opcion = document.createElement("option");
      opcion.value = etiqueta;
      opcion.textContent = etiqueta;
      grado.appendChild(opcion);
    });

    if (conservar && opciones.indexOf(conservar) !== -1) {
      grado.value = conservar;
    }

    if (gradoHint) {
      gradoHint.textContent = opciones.length > 0
        ? "Grados de " + nivel.value + "."
        : "Selecciona un nivel para ver los grados.";
    }

    grado.disabled = opciones.length === 0;
  }

  function mostrarEstadoFormulario(mensaje, tipo) {
    if (!formStatus) {
      return;
    }
    formStatus.textContent = mensaje;
    formStatus.dataset.tipo = tipo || "";
  }

  function cerrarVistaPrevia() {
    if (previewModal) {
      previewModal.hidden = true;
    }
    if (previewFrame) {
      previewFrame.src = "about:blank";
    }
  }

  function abrirVistaPreviaGuardada(id) {
    if (!previewModal || !previewFrame || !id) {
      return;
    }

    previewFrame.name = "actaPreviewFrame";
    previewModal.hidden = false;
    previewFrame.src = "vista-previa.php?id=" + encodeURIComponent(id);
  }

  function abrirVistaPrevia() {
    if (!previewModal || !previewFrame) {
      return;
    }

    const datos = new FormData(form);
    const formulario = document.createElement("form");
    formulario.method = "post";
    formulario.action = "vista-previa.php";
    formulario.target = "actaPreviewFrame";
    formulario.hidden = true;

    datos.forEach(function (valor, clave) {
      const campo = document.createElement("input");
      campo.type = "hidden";
      campo.name = clave;
      campo.value = typeof valor === "string" ? valor : "";
      formulario.appendChild(campo);
    });

    previewFrame.name = "actaPreviewFrame";
    document.body.appendChild(formulario);
    previewModal.hidden = false;
    formulario.submit();
    window.setTimeout(function () {
      formulario.remove();
    }, 1000);
  }

  function mostrarEstadoAlumno(mensaje, tipo) {
    if (!lookupStatus) {
      return;
    }
    lookupStatus.textContent = mensaje;
    lookupStatus.dataset.tipo = tipo || "";
  }

  function limpiarDatosAlumno() {
    if (nombreAlumno) {
      nombreAlumno.value = "";
      nombreAlumno.readOnly = false;
    }
    if (nivel) {
      nivel.disabled = false;
      llenarGrados("");
    }
    if (grado) {
      grado.disabled = false;
    }
    if (grupo) {
      grupo.value = "";
      grupo.disabled = false;
    }
    form.querySelectorAll(".alumno-catalogo-hidden").forEach(function (campo) {
      campo.remove();
    });
  }

  function agregarValorProtegido(nombre, valor) {
    const campo = document.createElement("input");
    campo.type = "hidden";
    campo.name = nombre;
    campo.value = valor || "";
    campo.className = "alumno-catalogo-hidden";
    form.appendChild(campo);
  }

  function bloquearDatosAlumno(alumno) {
    if (nombreAlumno) {
      nombreAlumno.readOnly = true;
    }
    if (nivel) {
      nivel.disabled = true;
    }
    if (grado) {
      grado.disabled = true;
    }
    if (grupo) {
      grupo.disabled = true;
    }

    agregarValorProtegido("nivel", alumno.nivel);
    agregarValorProtegido("grado", alumno.grado);
    agregarValorProtegido("grupo", alumno.grupo);
  }

  function desbloquearDatosAlumno() {
    if (nombreAlumno) {
      nombreAlumno.readOnly = false;
    }
    if (nivel) {
      nivel.disabled = false;
    }
    if (grado) {
      grado.disabled = false;
    }
    if (grupo) {
      grupo.disabled = false;
    }
  }

  async function buscarAlumno() {
    if (!matricula) {
      return;
    }

    const valor = matricula.value.trim();

    if (!/^[A-Za-z0-9]{1,5}$/.test(valor) || !nivel || !nivel.value) {
      limpiarDatosAlumno();
      mostrarEstadoAlumno(valor === "" ? "" : (!nivel || !nivel.value ? "Selecciona primero el nivel educativo." : "Escribe de 1 a 5 letras o números."), "error");
      return;
    }

    if (consultaActual) {
      consultaActual.abort();
    }

    consultaActual = new AbortController();
    mostrarEstadoAlumno("Buscando alumno...", "loading");

    try {
      const respuesta = await fetch(
        "buscar_alumno.php?matricula=" + encodeURIComponent(valor) + "&nivel=" + encodeURIComponent(nivel.value),
        {
          headers: { Accept: "application/json" },
          signal: consultaActual.signal
        }
      );
      const datos = await respuesta.json();

      if (!respuesta.ok) {
        limpiarDatosAlumno();
        mostrarEstadoAlumno(datos.error || "No se encontró el alumno.", "error");
        return;
      }

      const alumno = datos.alumno;
      if (nombreAlumno) {
        nombreAlumno.value = alumno.nombre || "";
      }
      if (nivel) {
        nivel.value = alumno.nivel || "";
        llenarGrados(alumno.grado || "");
      }
      if (grupo) {
        grupo.value = alumno.grupo || "";
      }
      form.querySelectorAll(".alumno-catalogo-hidden").forEach(function (campo) {
        campo.remove();
      });
      bloquearDatosAlumno(alumno);
      mostrarEstadoAlumno("Alumno encontrado.", "success");
    } catch (error) {
      if (error.name !== "AbortError") {
        limpiarDatosAlumno();
        mostrarEstadoAlumno("No se pudo consultar la matrícula.", "error");
      }
    }
  }

  if (nivel && grado) {
    nivel.addEventListener("change", function () {
      llenarGrados("");
      desbloquearDatosAlumno();
      buscarAlumno();
    });

    llenarGrados("");
  }

  if (matricula) {
    matricula.addEventListener("input", function () {
      matricula.value = matricula.value.replace(/[^a-zA-Z0-9]/g, "").toUpperCase().slice(0, 5);
      desbloquearDatosAlumno();
      limpiarDatosAlumno();
      if (nivel && nivel.value) buscarAlumno();
    });
    matricula.addEventListener("blur", buscarAlumno);
  }

  if (previewButton) {
    previewButton.addEventListener("click", function () {
      abrirVistaPrevia();
    });
  }

  document.querySelectorAll("[data-close-preview]").forEach(function (elemento) {
    elemento.addEventListener("click", cerrarVistaPrevia);
  });

  document.addEventListener("keydown", function (evento) {
    if (evento.key === "Escape" && previewModal && !previewModal.hidden) {
      cerrarVistaPrevia();
    }
  });

  // El motivo es obligatorio: se marca con error si nadie elige uno.
  form.addEventListener("submit", function (evento) {
    if (evento.submitter && evento.submitter.id === "saveActa") {
      evento.preventDefault();
      guardarActa();
      return;
    }

    const tarjetas = form.querySelectorAll(".option-card");
    const elegido = form.querySelector('input[name="motivo"]:checked');

    tarjetas.forEach(function (tarjeta) {
      tarjeta.classList.toggle("invalid", elegido === null);
    });

    async function guardarActa() {
      if (guardandoActa || !form.reportValidity()) {
        return;
      }

      guardandoActa = true;
      const botonGuardar = form.querySelector('#saveActa');
      if (botonGuardar) botonGuardar.disabled = true;
      form.setAttribute('aria-busy', 'true');
      mostrarEstadoFormulario("Guardando acta...", "loading");

      try {
        const respuesta = await fetch("guardar_acta.php", {
          method: "POST",
          body: new FormData(form),
          headers: { Accept: "application/json" }
        });
        const datos = await respuesta.json();

        if (!respuesta.ok) {
          mostrarEstadoFormulario(datos.error || "No se pudo guardar el acta.", "error");
          return;
        }

        mostrarEstadoFormulario("Acta guardada correctamente. Folio: " + datos.folio, "success");
        form.reset();
        abrirVistaPreviaGuardada(datos.id);
      } catch (error) {
        mostrarEstadoFormulario("No se pudo guardar el acta. Intenta nuevamente.", "error");
      } finally {
        guardandoActa = false;
        if (botonGuardar) botonGuardar.disabled = false;
        form.removeAttribute('aria-busy');
      }
    }

    if (!elegido) {
      evento.preventDefault();

      if (tarjetas.length > 0) {
        tarjetas[0].scrollIntoView({ behavior: "smooth", block: "center" });
      }
    }
  });

  // Refleja la seleccion del motivo en la tarjeta, sin depender de CSS.
  form.querySelectorAll('input[name="motivo"]').forEach(function (radio) {
    radio.addEventListener("change", function () {
      form.querySelectorAll(".option-card").forEach(function (tarjeta) {
        tarjeta.classList.remove("selected");
      });

      if (radio.checked) {
        radio.closest(".option-card").classList.add("selected");
      }
    });
  });

  form.addEventListener("reset", function () {
    window.setTimeout(function () {
      llenarGrados("");
      limpiarDatosAlumno();
      mostrarEstadoAlumno("");

      form.querySelectorAll(".option-card").forEach(function (tarjeta) {
        tarjeta.classList.remove("selected", "invalid");
      });

    }, 0);
  });
})();
const matriculaInicial = document.getElementById("matricula");
if (matriculaInicial && matriculaInicial.value) matriculaInicial.dispatchEvent(new Event("input", { bubbles: true }));
