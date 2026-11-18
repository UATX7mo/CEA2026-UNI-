(function () {
  const modal = document.querySelector("#previewModal");
  const frame = document.querySelector("#previewFrame");

  if (!modal || !frame) {
    return;
  }

  function cerrarVistaPrevia() {
    modal.hidden = true;
    frame.src = "about:blank";
  }

  document.querySelectorAll(".preview-acta-form").forEach(function (formulario) {
    formulario.addEventListener("submit", function (evento) {
      evento.preventDefault();

      const boton = formulario.querySelector("[data-preview-id]");
      const id = boton ? boton.dataset.previewId : "";

      if (!id) {
        return;
      }

      frame.name = "listadoPreviewFrame";
      modal.hidden = false;
      frame.src = "vista-previa.php?id=" + encodeURIComponent(id);
    });
  });

  document.querySelectorAll("[data-delete-confirm]").forEach(function (boton) {
    boton.addEventListener("click", function (evento) {
      if (!window.confirm("¿Seguro que deseas eliminar esta acta? Esta acción no se puede deshacer.")) {
        evento.preventDefault();
      }
    });
  });

  document.querySelectorAll("[data-close-preview]").forEach(function (elemento) {
    elemento.addEventListener("click", cerrarVistaPrevia);
  });

  document.addEventListener("keydown", function (evento) {
    if (evento.key === "Escape" && !modal.hidden) {
      cerrarVistaPrevia();
    }
  });
})();
