// Manejo delegado para que los módulos agregados durante esta sesión
// también puedan activarse o desactivarse sin recargar la página.
document.addEventListener('click', async evento => {
    const boton = evento.target.closest?.('.btnCambiar');
    if (!boton || boton.disabled) return;

    // El cambio se guarda por fetch, sin enviar formularios ni recargar la página.
    evento.preventDefault();

    const id = boton.dataset.id;
    const estadoActual = boton.dataset.estado;
    const nuevoEstado = estadoActual == 1 ? 0 : 1;
    boton.disabled = true;
    const datos = new FormData();
    datos.append('id', id);
    datos.append('estado', nuevoEstado);

    try {
        const response = await fetch('actualizar_url.php', {
            method: 'POST',
            body: datos,
            cache: 'no-store',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });
        const textoRespuesta = await response.text();
        let respuesta;
        try {
            respuesta = JSON.parse(textoRespuesta);
        } catch (_) {
            if (response.redirected) {
                throw new Error('Solo una sesión de administrador puede cambiar el estado de los links. Inicia sesión con la cuenta administradora.');
            }
            throw new Error(`El servidor respondió con un formato inesperado (HTTP ${response.status}).`);
        }

        if (!response.ok || !respuesta.ok || Number(respuesta.estado) !== nuevoEstado) {
            throw new Error(respuesta.mensaje || 'No se pudo actualizar el estado.');
        }

        // Refleja el estado confirmado por el servidor inmediatamente, sin recargar.
        boton.dataset.estado = String(respuesta.estado);
        boton.classList.toggle('btn-success', Number(respuesta.estado) === 1);
        boton.classList.toggle('btn-danger', Number(respuesta.estado) === 0);
        boton.textContent = Number(respuesta.estado) === 1 ? 'Activo' : 'Desactivado';
    } catch (error) {
        console.error(error);
        Swal.fire('Error', error.message || 'Error de conexión', 'error');
    } finally {
        boton.disabled = false;
    }
});
