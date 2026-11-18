// Espera que cargue el DOM
document.addEventListener("DOMContentLoaded", () => {

    // Obtiene TODOS los botones eliminar
    const botonesEliminar = document.querySelectorAll(".btnEliminar");

    // Recorre cada botón
    botonesEliminar.forEach((boton) => {

        boton.addEventListener("click", async () => {

            // Obtiene el ID del registro
            const id = boton.dataset.id;

            // Confirmación SweetAlert
            const confirmacion = await Swal.fire({
                title: "¿Eliminar registro?",
                text: "Esta acción no se puede deshacer",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            });

            // Si cancela
            if (!confirmacion.isConfirmed) return;

            try {

                // Creamos FormData
                const datos = new FormData();
                datos.append("id_insc", id);

                // Petición fetch
                const response = await fetch("eliminar_prees.php", {
                    method: "POST",
                    body: datos
                });

                // Respuesta PHP
                const r = (await response.text()).trim();

                // Si eliminó correctamente
                if (r === "1") {

                    await Swal.fire(
                        "Eliminado",
                        "Registro eliminado correctamente",
                        "success"
                    );

                    // Recarga la página
                    location.reload();

                } else {

                    Swal.fire(
                        "Error",
                        "No se pudo eliminar",
                        "error"
                    );

                }

            } catch (error) {

                console.error(error);

                Swal.fire(
                    "Error",
                    "Error de conexión",
                    "error"
                );

            }

        });

    });

});