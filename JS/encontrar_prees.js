// Espera a que el DOM cargue
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("buscar");

    form.addEventListener("submit", async (e) => {
        e.preventDefault(); // Evita recargar la página

        const curpInput = document.getElementById("curp");
        const curp = curpInput.value.trim().toUpperCase();

        if (!curp) {
            Swal.fire("Error", "Ingresa un CURP", "warning");
            return;
        }

        // Enviar POST al PHP
        const datos = new FormData();
        datos.append("busca_prees", curp); // coincide con PHP

        try {
            const response = await fetch("validar_curp_prees.php", {
                method: "POST",
                body: datos
            });

            const resultado = (await response.text()).trim();

            if (resultado === "0") {
                Swal.fire("Error", "CURP no registrado", "error");
                return;
            }

            // CURP válido: abrir PDF
            window.open("formato_preescolar.php?curp=" + encodeURIComponent(curp), "_blank");
        } catch (error) {
            console.error(error);
            Swal.fire("Error", "Hubo un problema al validar el CURP", "error");
        }
    });
});
