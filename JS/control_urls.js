(() => {
    const abrirFormulario = document.getElementById('mostrar-form-link');
    const formulario = document.getElementById('form-agregar-link');
    const cuerpoTabla = document.getElementById('lista-links');
    const buscador = document.getElementById('buscar-links');
    const mensajeSinResultados = document.getElementById('sin-resultados-links');
    const categorias = {
        preescolar: { nombre: 'Preescolar', icono: 'fa-child-reaching' },
        primaria: { nombre: 'Primaria', icono: 'fa-book-open' },
        secundaria: { nombre: 'Secundaria', icono: 'fa-graduation-cap' }
    };
    const categoriasExpandidas = new Set();

    if (!abrirFormulario || !formulario || !cuerpoTabla) return;

    const normalizarBusqueda = texto => texto
        .toLocaleLowerCase('es')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');

    const filtrarLinks = () => {
        if (!buscador) return;
        const consulta = normalizarBusqueda(buscador.value.trim());
        let coincidencias = 0;
        const categoriasVisibles = new Set();

        cuerpoTabla.querySelectorAll('tr').forEach(fila => {
            if (fila.classList.contains('curls-category-row') || fila.classList.contains('curls-empty-row')) return;
            const nombre = fila.querySelector('.curls-modulo-titulo')?.textContent || '';
            const url = fila.querySelector('.curls-url')?.textContent || '';
            const esModulo = Boolean(fila.querySelector('.curls-modulo-titulo'));
            const categoria = fila.dataset.category || '';
            const nombreCategoria = categorias[categoria]?.nombre || categoria;
            const coincide = esModulo && normalizarBusqueda(`${nombre} ${url} ${nombreCategoria}`).includes(consulta);
            fila.hidden = !coincide || !categoriasExpandidas.has(categoria);
            if (coincide) {
                coincidencias++;
                categoriasVisibles.add(categoria);
            }
        });

        cuerpoTabla.querySelectorAll('.curls-category-row').forEach(fila => {
            const categoria = fila.dataset.categoryHeader;
            fila.hidden = Boolean(consulta) && !categoriasVisibles.has(categoria);
            const boton = fila.querySelector('[data-category-toggle]');
            if (boton) boton.setAttribute('aria-expanded', String(categoriasExpandidas.has(categoria)));
        });
        cuerpoTabla.querySelectorAll('.curls-empty-row').forEach(fila => {
            fila.hidden = Boolean(consulta) || !categoriasExpandidas.has(fila.dataset.category);
        });
        if (mensajeSinResultados) mensajeSinResultados.hidden = !consulta || coincidencias > 0;
    };

    const actualizarContador = () => {
        const contador = document.getElementById('curls-count');
        const total = cuerpoTabla.querySelectorAll('.curls-modulo-titulo').length;
        if (contador) contador.textContent = `${total} ${total === 1 ? 'módulo' : 'módulos'}`;
        cuerpoTabla.querySelectorAll('.curls-category-row').forEach(encabezado => {
            const categoria = encabezado.dataset.categoryHeader;
            const cantidad = cuerpoTabla.querySelectorAll(`tr[data-category="${categoria}"] .curls-modulo-titulo`).length;
            const etiqueta = encabezado.querySelector('.curls-category-count');
            if (etiqueta) etiqueta.textContent = `${cantidad} ${cantidad === 1 ? 'módulo' : 'módulos'}`;
        });
    };

    const insertarEnCategoria = fila => {
        const categoria = fila.dataset.category;
        const encabezado = cuerpoTabla.querySelector(`.curls-category-row[data-category-header="${categoria}"]`);
        if (!encabezado) {
            cuerpoTabla.append(fila);
            return;
        }
        cuerpoTabla.querySelector(`.curls-empty-row[data-category="${categoria}"]`)?.remove();
        let siguiente = encabezado.nextElementSibling;
        while (siguiente && !siguiente.classList.contains('curls-category-row')) {
            siguiente = siguiente.nextElementSibling;
        }
        cuerpoTabla.insertBefore(fila, siguiente);
    };

    buscador?.addEventListener('input', filtrarLinks);

    cuerpoTabla.addEventListener('click', evento => {
        const botonCategoria = evento.target.closest?.('[data-category-toggle]');
        if (!botonCategoria) return;
        const categoria = botonCategoria.dataset.categoryToggle;
        if (categoriasExpandidas.has(categoria)) categoriasExpandidas.delete(categoria);
        else categoriasExpandidas.add(categoria);
        filtrarLinks();
    });

    document.addEventListener('click', async evento => {
        const botonEliminar = evento.target.closest?.('[data-eliminar-id]');
        if (!botonEliminar || botonEliminar.disabled) return;

        const fila = botonEliminar.closest('tr');
        const nombre = fila?.querySelector('.curls-modulo-titulo')?.textContent?.trim() || 'este link';
        const confirmacion = await Swal.fire({
            title: '¿Eliminar link?',
            text: `Se eliminará “${nombre}” del control de URLs.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#a33'
        });
        if (!confirmacion.isConfirmed) return;

        botonEliminar.disabled = true;
        try {
            const datos = new FormData();
            datos.append('id', botonEliminar.dataset.eliminarId);
            const respuesta = await fetch('eliminar_url.php', {
                method: 'POST',
                body: datos,
                cache: 'no-store',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            const texto = await respuesta.text();
            let resultado;
            try {
                resultado = JSON.parse(texto);
            } catch (_) {
                throw new Error(respuesta.redirected
                    ? 'Tu sesión pudo haber vencido. Inicia sesión de nuevo e intenta eliminar el link.'
                    : `El servidor respondió con un formato inesperado (HTTP ${respuesta.status}).`);
            }
            if (!respuesta.ok || !resultado.ok) {
                throw new Error(resultado.mensaje || 'No se pudo eliminar el link.');
            }

            fila.remove();
            const categoria = fila.dataset.category;
            if (cuerpoTabla.querySelectorAll(`tr[data-category="${categoria}"] .curls-modulo-titulo`).length === 0) {
                const filaVacia = document.createElement('tr');
                filaVacia.className = 'curls-empty-row';
                filaVacia.dataset.category = categoria;
                filaVacia.hidden = !categoriasExpandidas.has(categoria);
                const mensaje = document.createElement('td');
                mensaje.className = 'curls-empty';
                mensaje.colSpan = 4;
                mensaje.textContent = 'Sin módulos en esta categoría.';
                filaVacia.append(mensaje);
                const encabezado = cuerpoTabla.querySelector(`.curls-category-row[data-category-header="${categoria}"]`);
                let siguiente = encabezado?.nextElementSibling || null;
                while (siguiente && !siguiente.classList.contains('curls-category-row')) siguiente = siguiente.nextElementSibling;
                cuerpoTabla.insertBefore(filaVacia, siguiente);
            }
            actualizarContador();
            filtrarLinks();
            await Swal.fire('Link eliminado', 'El link se eliminó correctamente.', 'success');
        } catch (error) {
            await Swal.fire('No se pudo eliminar', error.message || 'Error de conexión.', 'error');
        } finally {
            botonEliminar.disabled = false;
        }
    });

    abrirFormulario.addEventListener('click', () => {
        const visible = formulario.hidden;
        formulario.hidden = !visible;
        abrirFormulario.setAttribute('aria-expanded', String(visible));
        if (visible) document.getElementById('nuevo-link-nombre').focus();
    });

    const obtenerIcono = (nombre, ruta) => {
        const texto = `${nombre} ${ruta}`.toLocaleLowerCase();
        const reglas = [
            ['preescolar', 'fa-child-reaching'], ['primaria', 'fa-book-open'],
            ['secundaria', 'fa-graduation-cap'], ['aviso', 'fa-bullhorn'],
            ['inicio', 'fa-house'], ['ficha', 'fa-id-card'],
            ['buscar', 'fa-magnifying-glass'], ['busca', 'fa-magnifying-glass'],
            ['inscrip', 'fa-file-pen'], ['alumno', 'fa-user-graduate'],
            ['contacto', 'fa-address-book'], ['correo', 'fa-envelope'],
            ['pdf', 'fa-file-pdf'], ['reporte', 'fa-chart-column'],
            ['usuario', 'fa-users'], ['admin', 'fa-user-gear']
        ];
        return reglas.find(([palabra]) => texto.includes(palabra))?.[1] || 'fa-puzzle-piece';
    };

    formulario.addEventListener('submit', async evento => {
        evento.preventDefault();
        const boton = formulario.querySelector('[type="submit"]');
        boton.disabled = true;

        try {
            const respuesta = await fetch('agregar_url.php', {
                method: 'POST',
                body: new FormData(formulario),
                cache: 'no-store',
                headers: { 'Accept': 'application/json' }
            });
            const textoRespuesta = await respuesta.text();
            let datos;
            try {
                datos = JSON.parse(textoRespuesta);
            } catch (_) {
                if (respuesta.redirected) {
                    throw new Error('Tu sesión pudo haber vencido. Inicia sesión de nuevo e intenta agregar el link.');
                }
                throw new Error(`El servidor respondió con un formato inesperado (HTTP ${respuesta.status}). Revisa la ruta e intenta de nuevo.`);
            }
            if (!respuesta.ok || !datos.ok) {
                throw new Error(datos.mensaje || 'No se pudo registrar el link.');
            }

            const modulo = datos.modulo;
            const categoria = modulo.categoria;
            if (!categorias[categoria]) {
                throw new Error('Solo se pueden agregar módulos de Preescolar, Primaria o Secundaria.');
            }
            categoriasExpandidas.add(categoria);
            const fila = document.createElement('tr');
            fila.dataset.category = categoria;
            const celdaModulo = document.createElement('td');
            const envoltura = document.createElement('div');
            envoltura.className = 'curls-modulo';
            const cuadroIcono = document.createElement('span');
            cuadroIcono.className = 'curls-icon';
            cuadroIcono.setAttribute('aria-hidden', 'true');
            const icono = document.createElement('i');
            icono.className = `fa-solid ${obtenerIcono(modulo.nombre, modulo.url)}`;
            cuadroIcono.append(icono);
            const textoModulo = document.createElement('span');
            textoModulo.className = 'curls-modulo-titulo';
            textoModulo.textContent = modulo.nombre;
            envoltura.append(cuadroIcono, textoModulo);
            celdaModulo.append(envoltura);

            const celdaUrl = document.createElement('td');
            celdaUrl.className = 'curls-url';
            const iconoUrl = document.createElement('i');
            iconoUrl.className = 'fa-solid fa-link';
            iconoUrl.setAttribute('aria-hidden', 'true');
            celdaUrl.append(iconoUrl, document.createTextNode(modulo.url));

            const celdaEstado = document.createElement('td');
            const botonEstado = document.createElement('button');
            botonEstado.type = 'button';
            botonEstado.className = 'btnCambiar curls-badge btn-success';
            botonEstado.dataset.id = String(modulo.id);
            botonEstado.dataset.estado = '1';
            botonEstado.textContent = 'Activo';
            celdaEstado.append(botonEstado);

            const celdaAcciones = document.createElement('td');
            const botonEliminar = document.createElement('button');
            botonEliminar.type = 'button';
            botonEliminar.className = 'curls-delete-button';
            botonEliminar.dataset.eliminarId = String(modulo.id);
            botonEliminar.setAttribute('aria-label', `Eliminar ${modulo.nombre}`);
            const iconoEliminar = document.createElement('i');
            iconoEliminar.className = 'fa-solid fa-trash';
            iconoEliminar.setAttribute('aria-hidden', 'true');
            botonEliminar.append(iconoEliminar, document.createTextNode(' Eliminar'));
            celdaAcciones.append(botonEliminar);

            fila.append(celdaModulo, celdaUrl, celdaEstado, celdaAcciones);
            insertarEnCategoria(fila);
            filtrarLinks();

            actualizarContador();
            formulario.reset();
            formulario.hidden = true;
            abrirFormulario.setAttribute('aria-expanded', 'false');
            await Swal.fire('Link agregado', 'El módulo quedó activo y ya puedes cambiar su estado desde la tabla.', 'success');
        } catch (error) {
            await Swal.fire('No se pudo agregar', error.message || 'Error de conexión.', 'error');
        } finally {
            boton.disabled = false;
        }
    });
})();
