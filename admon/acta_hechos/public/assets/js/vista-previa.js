(function () {
  "use strict";

  const fuente = document.querySelector("#documentSource");
  const paginas = document.querySelector("#documentPages");
  const plantilla = document.querySelector("#pageTemplate");
  const boton = document.querySelector("#printDocument");
  const descargar = document.querySelector("#downloadDocument");
  const zoomOut = document.querySelector("#zoomOut");
  const zoomIn = document.querySelector("#zoomIn");
  const zoomValue = document.querySelector("#zoomValue");
  const errorImpresion = document.querySelector("#printError");
  const folio = document.body.dataset.folio || "Borrador";
  const actaId = document.body.dataset.actaId || "";
  const membretes = JSON.parse(document.body.dataset.membretes || "{}");
  const motivos = JSON.parse(document.body.dataset.motivos || "{}");
  const imprimirAutomaticamente = document.body.dataset.imprimir === "true";
  const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio",
    "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
  let nivel = document.body.dataset.nivel;
  let matricula = plantilla.content.querySelector("[data-page-matricula]").textContent;
  let pendiente = 0;
  let imprimiendo = false;
  let escala = 1;

  const tituloImpresion = "Acta de hechos - " + folio;
  document.title = tituloImpresion;
  if (window.parent !== window) {
    try {
      window.parent.document.title = tituloImpresion;
    } catch (error) {
      // El iframe puede estar alojado en otro origen durante una integración externa.
    }
  }

  function fechaTexto(valor) {
    const partes = /^(\d{4})-(\d{2})-(\d{2})$/.exec(valor);
    if (!partes) return "";
    const fecha = new Date(Number(partes[1]), Number(partes[2]) - 1, Number(partes[3]));
    if (fecha.getFullYear() !== Number(partes[1]) || fecha.getMonth() !== Number(partes[2]) - 1 ||
        fecha.getDate() !== Number(partes[3])) return "";
    return Number(partes[3]) + " de " + meses[Number(partes[2]) - 1] + " de " + partes[1];
  }

  function actualizarZoom() {
    document.querySelector(".document-area").style.setProperty("--preview-zoom", String(escala));
    zoomValue.textContent = Math.round(escala * 100) + "%";
    zoomOut.disabled = escala <= 0.5;
    zoomIn.disabled = escala >= 1.5;
  }

  function rutaMembrete() {
    return Object.prototype.hasOwnProperty.call(membretes, nivel) ? membretes[nivel] : "";
  }

  function nuevaPagina() {
    const pagina = plantilla.content.firstElementChild.cloneNode(true);
    const imagen = pagina.querySelector(".letterhead");
    const ruta = rutaMembrete();
    imagen.hidden = !ruta;
    if (ruta) imagen.src = ruta;
    else imagen.removeAttribute("src");
    pagina.querySelector(".plain-letterhead").hidden = Boolean(ruta);
    pagina.querySelector("[data-page-matricula]").textContent = matricula || "—";
    paginas.appendChild(pagina);
    return pagina.querySelector(".document-content");
  }

  function cabe(contenido) {
    const ultimo = contenido.lastElementChild;
    // La geometría es idéntica en pantalla e impresión; se deja una tolerancia subpíxel.
    return !ultimo || ultimo.getBoundingClientRect().bottom <= contenido.getBoundingClientRect().bottom + 0.2;
  }

  /** Divide únicamente los relatos. El cierre y la firma siempre viajan juntos. */
  function distribuirRelato(original, contenido) {
    const campo = original.dataset.splitField;
    let resto = Array.from(original.querySelector("[data-preview-field]").textContent);
    let continuacion = false;

    while (resto.length) {
      const bloque = original.cloneNode(true);
      const texto = bloque.querySelector("[data-preview-field]");
      if (continuacion) bloque.querySelector("h2").textContent += " (continuación)";
      texto.textContent = resto.join("");
      contenido.appendChild(bloque);
      if (cabe(contenido)) break;

      // Búsqueda binaria del mayor fragmento que cabe, sin perder caracteres Unicode.
      let inferior = 0;
      let superior = resto.length;
      while (inferior < superior) {
        const medio = Math.ceil((inferior + superior) / 2);
        texto.textContent = resto.slice(0, medio).join("");
        if (cabe(contenido)) inferior = medio;
        else superior = medio - 1;
      }

      let corte = inferior;
      // Mantener párrafos completos cuando su final queda cerca del borde de la hoja.
      for (let i = corte - 1; i >= Math.max(0, corte - 180, Math.floor(corte * 0.8)); i--) {
        if (resto[i] === "\n") { corte = i + 1; break; }
      }
      // Preferir fin de palabra. Si una palabra supera una hoja, se permite dividirla.
      if (corte && corte < resto.length && !/\s/u.test(resto[corte])) {
        for (let i = corte - 1; i > 0; i--) {
          if (/\s/u.test(resto[i])) { corte = i + 1; break; }
        }
      }
      texto.textContent = resto.slice(0, corte).join("");
      const tieneOtrosBloques = contenido.children.length > 1;
      const dosLineas = parseFloat(getComputedStyle(texto).lineHeight) * 2;
      if (corte === 0 || (tieneOtrosBloques && texto.getBoundingClientRect().height < dosLineas)) {
        bloque.remove();
        if (!tieneOtrosBloques) throw new Error("No se pudo distribuir el campo " + campo + ".");
        contenido = nuevaPagina();
        continue;
      }
      resto = resto.slice(corte);
      continuacion = true;
      contenido = nuevaPagina();
    }
    return contenido;
  }

  function paginar() {
    cancelAnimationFrame(pendiente);
    pendiente = 0;
    paginas.hidden = false;
    paginas.replaceChildren();
    let contenido = nuevaPagina();

    fuente.querySelectorAll("[data-document-block]").forEach(function (original) {
      if (original.dataset.splitField) {
        contenido = distribuirRelato(original, contenido);
        return;
      }
      const bloque = original.cloneNode(true);
      contenido.appendChild(bloque);
      if (!cabe(contenido) && contenido.children.length > 1) {
        bloque.remove();
        contenido = nuevaPagina();
        contenido.appendChild(bloque);
      }
      if (!cabe(contenido)) throw new Error("Un bloque del acta supera el espacio de la hoja.");
    });

    const total = paginas.children.length;
    paginas.querySelectorAll(".page-number").forEach(function (pie, indice) {
      pie.textContent = "Página " + (indice + 1) + " de " + total;
    });
    document.querySelector("#levelWarning").hidden = Boolean(rutaMembrete());
    fuente.hidden = true;
    document.body.dataset.paginas = String(total);
    boton.disabled = !actaId;
    descargar.disabled = !actaId;
    zoomOut.disabled = false;
    zoomIn.disabled = false;
  }

  function mostrarError(error) {
    errorImpresion.textContent = "No se pudo preparar el PDF. " + error.message;
    errorImpresion.hidden = false;
  }

  function actualizarPaginas() {
    try {
      paginar();
      errorImpresion.hidden = true;
    } catch (error) {
      // Nunca ofrecer una salida recortada como si estuviese lista.
      boton.disabled = true;
      mostrarError(error);
    }
  }

  function pintar(entrada) {
    // Lista explícita: un mensaje de borrador no puede inventar folio ni autoría.
    const datos = {};
    ["matricula", "nombre", "fecha", "nivel", "grado", "grupo", "motivo", "hechos", "acciones",
      "hora", "hora_conclusion", "suscriptor"].forEach(function (clave) {
      datos[clave] = typeof entrada[clave] === "string" ? entrada[clave].trim() : "";
    });
    nivel = datos.nivel;
    matricula = datos.matricula;
    const hora = /^([01]\d|2[0-3]):([0-5]\d)$/.exec(datos.hora);
    datos.hora_hora = hora ? hora[1] : "";
    datos.hora_minutos = hora ? hora[2] : "";
    datos.fecha = fechaTexto(datos.fecha);
    datos.motivo = Object.prototype.hasOwnProperty.call(motivos, datos.motivo) ? motivos[datos.motivo] : "";

    fuente.querySelectorAll("[data-preview-field]").forEach(function (nodo) {
      nodo.textContent = datos[nodo.dataset.previewField] || "—";
    });
    const aviso = document.querySelector("[data-preview-warnings]");
    if (aviso) {
      aviso.hidden = ["matricula", "nombre", "fecha", "hechos", "suscriptor", "motivo"].every(function (clave) {
        return datos[clave] !== "";
      });
    }
    cancelAnimationFrame(pendiente);
    pendiente = requestAnimationFrame(actualizarPaginas);
  }

  async function imprimir() {
    if (imprimiendo) return;
    imprimiendo = true;
    boton.disabled = true;
    try {
      await document.fonts.ready;
      paginar();
      boton.disabled = true;
      await Promise.all(Array.from(paginas.querySelectorAll(".letterhead:not([hidden])"), function (imagen) {
        return imagen.decode().catch(function () {
          throw new Error("No se pudo cargar el membrete. Vuelve a intentar la impresión.");
        });
      }));
      errorImpresion.hidden = true;
      document.title = tituloImpresion;
      if (window.parent !== window) {
        try {
          window.parent.document.title = tituloImpresion;
        } catch (error) {
          // La impresión continúa usando el título del iframe.
        }
      }
      window.focus();
      window.print();
    } catch (error) {
      mostrarError(error);
    } finally {
      imprimiendo = false;
      boton.disabled = !actaId;
    }
  }

  window.addEventListener("message", function (evento) {
    if (evento.origin !== window.location.origin || evento.source !== window.parent ||
        document.body.dataset.guardada === "true") return;
    if (!evento.data || evento.data.pims !== "acta") return;
    pintar(evento.data);
    if (evento.data.imprimir === true) imprimir();
  });
  window.addEventListener("beforeprint", function () {
    // Conserva las imágenes ya decodificadas; solo recalcula si hay una edición pendiente.
    if (pendiente) actualizarPaginas();
  });
  boton.addEventListener("click", imprimir);
  descargar.addEventListener("click", function () {
    if (actaId) {
      window.location.href = "descargar_acta.php?id=" + encodeURIComponent(actaId);
    }
  });
  zoomOut.addEventListener("click", function () {
    escala = Math.max(0.5, Math.round((escala - 0.1) * 10) / 10);
    actualizarZoom();
  });
  zoomIn.addEventListener("click", function () {
    escala = Math.min(1.5, Math.round((escala + 0.1) * 10) / 10);
    actualizarZoom();
  });
  actualizarZoom();
  actualizarPaginas();
  document.fonts.ready.then(function () {
    actualizarPaginas();
    // La impresión solo se inicia cuando el usuario pulsa el botón.
  });
})();
