(() => {
    const script = document.currentScript;
    if (!script || window.__autoModuloPoll) return;
    window.__autoModuloPoll = true;

    const pagina = script.dataset.pagina || '';
    const endpoint = script.dataset.endpoint || '';
    const modo = script.dataset.modo || 'vigilar';
    if (!pagina || !endpoint) return;

    let solicitudEnCurso = false;
    async function revisarEstado() {
        if (solicitudEnCurso) return;
        solicitudEnCurso = true;
        try {
            const datos = new URLSearchParams({ url: pagina });
            const respuesta = await fetch(endpoint, {
                method: 'POST',
                cache: 'no-store',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: datos.toString()
            });
            if (!respuesta.ok) return;

            const modulo = await respuesta.json();
            const estado = modulo.estado === null ? null : Number(modulo.estado);
            if (modo === 'reactivar') {
                if (estado === 1) window.location.replace(window.location.href);
                return;
            }

            let aviso = document.getElementById('modulo-desactivado-en-vivo');
            if (estado === 0 && !aviso) {
                aviso = document.createElement('main');
                aviso.id = 'modulo-desactivado-en-vivo';
                aviso.setAttribute('role', 'alert');
                aviso.innerHTML = '<div><h1>Módulo desactivado temporalmente</h1><p>El acceso a este módulo fue desactivado por la administración.</p></div>';
                document.body.appendChild(aviso);
            } else if (estado === 1 && aviso) {
                aviso.remove();
            }
        } catch (error) {
            // Si no se puede consultar el estado temporalmente, conservar la página.
        } finally {
            solicitudEnCurso = false;
        }
    }

    revisarEstado();
    window.setInterval(revisarEstado, 2000);
})();
