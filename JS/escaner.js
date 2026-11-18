const mensaje = document.getElementById('mensaje');
const detalles = document.getElementById('detalles');
const avisoGuia = document.getElementById('aviso-guia');

const API_LECTURA = 'http://localhost:5000/lector-raw';
const API_GUARDAR = 'http://localhost:5000/guardar-asistencia';

// Configuración de tiempos
const HORAS_BLOQUEO = 23;
const TIEMPO_BLOQUEO_MS = HORAS_BLOQUEO * 60 * 60 * 1000; 
const TIEMPO_ENFRIAMIENTO_INTERFAZ_MS = 3500; 

let ultimoTimestampLector = 0;
let estaBloqueado = false;

// Registro en memoria local para validación de 23h sin saturar el servidor
let registroAlumnos = JSON.parse(localStorage.getItem('registro_asistencias_kiosco') || '{}');

setInterval(consultarEscaner, 250);

async function consultarEscaner() {
    if (estaBloqueado) return;

    try {
        const res = await fetch(API_LECTURA);
        if (!res.ok) return;

        const data = await res.json();

        if (data.timestamp && data.timestamp > ultimoTimestampLector) {
            ultimoTimestampLector = data.timestamp;
            procesarLecturaDesdeWeb(data.texto_crudo);
        }
    } catch (e) {
        // En espera de conexión con el backend
    }
}

async function procesarLecturaDesdeWeb(textoQr) {
    estaBloqueado = true;

    const datosAlumno = parsearQR(textoQr);

    // Si no contiene los datos mínimos necesarios
    if (!datosAlumno) {
        mostrarInvalido(textoQr);
        return;
    }

    // Identificador único: Matrícula si existe, o respaldo nombre_grado_grupo
    const claveAlumno = datosAlumno.matricula 
        ? datosAlumno.matricula.trim().toLowerCase() 
        : `${datosAlumno.nombre}_${datosAlumno.grado}_${datosAlumno.grupo}`.trim().toLowerCase();
    
    const ahora = Date.now();

    // 1. Verificación de la regla de las 23 horas
    if (registroAlumnos[claveAlumno]) {
        const ultimoRegistroMs = registroAlumnos[claveAlumno].timestamp;
        const tiempoTranscurrido = ahora - ultimoRegistroMs;

        if (tiempoTranscurrido < TIEMPO_BLOQUEO_MS) {
            const tiempoRestanteMs = TIEMPO_BLOQUEO_MS - tiempoTranscurrido;
            mostrarDuplicado23Horas(datosAlumno, registroAlumnos[claveAlumno].horaLegible, tiempoRestanteMs);
            return;
        }
    }

    // 2. Envío a MySQL mediante Python
    try {
        const respuesta = await fetch(API_GUARDAR, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosAlumno)
        });

        const resultadoBD = await respuesta.json();

        if (resultadoBD.status === 'ok') {
            const fechaActual = resultadoBD.fecha;
            const horaActual = resultadoBD.hora;

            registroAlumnos[claveAlumno] = {
                timestamp: ahora,
                horaLegible: `${horaActual} (${fechaActual})`
            };
            localStorage.setItem('registro_asistencias_kiosco', JSON.stringify(registroAlumnos));

            mostrarExito(datosAlumno, horaActual, fechaActual);
        } else {
            mostrarErrorBD(resultadoBD.mensaje || 'Error en BD');
        }
    } catch (err) {
        mostrarErrorBD('No se pudo conectar con el servidor.');
    }
}

// Extractor unificado y flexible del QR
function parsearQR(texto) {
    if (!texto) return null;
    const txt = texto.trim();

    // 1. Si viene en formato JSON
    if (txt.startsWith('{') && txt.endsWith('}')) {
        try {
            const obj = JSON.parse(txt);
            const normalizado = {};
            for (let k in obj) {
                // Normaliza eliminando acentos para leer matrícula o matricula
                const claveSinAcento = k.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
                normalizado[claveSinAcento] = String(obj[k]).trim();
            }

            if (normalizado.nombre && normalizado.grado && normalizado.grupo) {
                return {
                    nombre: normalizado.nombre,
                    matricula: normalizado.matricula || '',
                    grado: normalizado.grado,
                    grupo: normalizado.grupo
                };
            }
        } catch (e) {}
    }

    // 2. Si viene en formato texto separado por comas, barras, punto y coma o tabulación
    const partes = txt.split(/[,|;\t]/).map(p => p.trim()).filter(Boolean);

    // Formato de 4 variables: Nombre, Matrícula, Grado, Grupo
    if (partes.length >= 4 && !partes[0].toLowerCase().startsWith('http')) {
        return {
            nombre: partes[0],
            matricula: partes[1],
            grado: partes[2],
            grupo: partes[3]
        };
    }

    // Compatibilidad en caso de formato antiguo de 3 variables: Nombre, Grado, Grupo
    if (partes.length === 3 && !partes[0].toLowerCase().startsWith('http')) {
        return {
            nombre: partes[0],
            matricula: '',
            grado: partes[1],
            grupo: partes[2]
        };
    }

    return null;
}

function formatearTiempoRestante(ms) {
    const horas = Math.floor(ms / (1000 * 60 * 60));
    const minutos = Math.floor((ms % (1000 * 60 * 60)) / (1000 * 60));
    return `${horas}h ${minutos}m`;
}

function mostrarExito(alumno, hora, fecha) {
    mensaje.className = 'exito';
    mensaje.textContent = '¡Asistencia Registrada!';
    avisoGuia.className = 'aviso-guia exito-texto';
    avisoGuia.textContent = '✓ Guardado exitoso en base de datos';

    detalles.className = 'info info-exito';
    const filaMatricula = alumno.matricula 
        ? `<div class="dato-fila"><span>Matrícula:</span> <strong>${alumno.matricula}</strong></div>` 
        : '';

    detalles.innerHTML = `
        <div class="dato-fila"><span>Alumno:</span> <strong>${alumno.nombre}</strong></div>
        ${filaMatricula}
        <div class="dato-fila"><span>Grado:</span> <strong>${alumno.grado}</strong></div>
        <div class="dato-fila"><span>Grupo:</span> <strong>${alumno.grupo}</strong></div>
        <div class="dato-fila"><span>Hora:</span> <strong>${hora} (${fecha})</strong></div>
    `;

    iniciarEnfriamiento('Registro guardado. Espere un momento...');
}

function mostrarDuplicado23Horas(alumno, horaPrevia, tiempoRestanteMs) {
    mensaje.className = 'duplicado';
    mensaje.textContent = 'Asistencia Ya Registrada';
    avisoGuia.className = 'aviso-guia advertencia-texto';
    avisoGuia.textContent = '⚠️ Debe transcurrir el periodo de 23 horas';

    const tiempoRestanteStr = formatearTiempoRestante(tiempoRestanteMs);

    detalles.className = 'info info-duplicado';
    const filaMatricula = alumno.matricula 
        ? `<div class="dato-fila"><span>Matrícula:</span> <strong>${alumno.matricula}</strong></div>` 
        : '';

    detalles.innerHTML = `
        <div class="dato-fila"><span>Alumno:</span> <strong>${alumno.nombre}</strong></div>
        ${filaMatricula}
        <div class="dato-fila"><span>Grado y Grupo:</span> <strong>${alumno.grado} - ${alumno.grupo}</strong></div>
        <div class="dato-fila"><span>Último registro:</span> <strong>${horaPrevia}</strong></div>
        <div class="dato-fila"><span>Disponible de nuevo en:</span> <strong style="color: #b45309;">${tiempoRestanteStr}</strong></div>
        <div style="margin-top: 8px; font-size: 12px; color: #92400e; text-align: center;">
            Protección activa de 23 horas. No se duplicaron datos en MySQL.
        </div>
    `;

    iniciarEnfriamiento('Pase ya registrado. Espere un momento...');
}

function mostrarInvalido(textoCrudo) {
    mensaje.className = 'error';
    mensaje.textContent = 'QR Inválido / No Escolar';
    avisoGuia.className = 'aviso-guia alerta';
    avisoGuia.textContent = '⚠️ Faltan campos requeridos';

    const textoSeguro = document.createTextNode(textoCrudo || '').textContent;
    detalles.className = 'info info-error';
    detalles.innerHTML = `
        <div class="error-titulo">Contenido rechazado:</div>
        <div class="error-contenido">"${textoSeguro}"</div>
        <div class="error-requisito">Requerido: <strong>Nombre, Matrícula, Grado, Grupo</strong></div>
    `;

    iniciarEnfriamiento('Código no válido. Espere un momento...');
}

function mostrarErrorBD(msg) {
    mensaje.className = 'error';
    mensaje.textContent = 'Error de Base de Datos';
    detalles.className = 'info info-error';
    detalles.innerHTML = `<div>${msg}</div>`;
    iniciarEnfriamiento('Error al guardar. Intente de nuevo...');
}

function iniciarEnfriamiento(msgEspera) {
    setTimeout(() => {
        mensaje.className = 'bloqueado';
        mensaje.textContent = msgEspera;
    }, 1500);

    setTimeout(() => {
        estaBloqueado = false;
        mensaje.className = '';
        mensaje.textContent = 'Esperando escaneo...';
        avisoGuia.className = 'aviso-guia';
        avisoGuia.textContent = 'Mantenga el código quieto frente al lector';
        detalles.className = 'info';
        detalles.innerHTML = '';
    }, TIEMPO_ENFRIAMIENTO_INTERFAZ_MS);
}