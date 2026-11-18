import serial
from serial.tools import list_ports
import mysql.connector
from datetime import datetime
import time
import threading
import os
from flask import Flask, jsonify, request
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

def obtener_puerto_lector():
    """Usa SCANNER_PORT si se configuró; si no, detecta el único puerto USB serie."""
    puerto_configurado = os.environ.get('SCANNER_PORT', '').strip()
    if puerto_configurado:
        return puerto_configurado

    puertos_usb = [
        puerto.device
        for puerto in list_ports.comports()
        if 'USB VID:' in (puerto.hwid or '').upper()
        and 'BTHENUM' not in (puerto.hwid or '').upper()
    ]
    if len(puertos_usb) == 1:
        return puertos_usb[0]
    if len(puertos_usb) > 1:
        print(f"Se detectaron varios lectores USB serie: {', '.join(puertos_usb)}. Configure SCANNER_PORT.")
    else:
        print("No se detectó un lector USB serie conectado.")
    return None

# Configuración de base de datos
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',       # Tu contraseña de MySQL
    'database': 'datos'  # Cambiar a escuela_db si aún usas la original
}

# Buffer en memoria de la última lectura del escáner
ultimo_escaneo_crudo = {
    'texto_crudo': '',
    'timestamp': 0
}

def hilo_lector_serial():
    global ultimo_escaneo_crudo
    baudios = 9600

    while True:
        puerto = obtener_puerto_lector()
        if not puerto:
            time.sleep(3)
            continue
        try:
            print(f"Conectando a {puerto}...")
            ser = serial.Serial(puerto, baudios, timeout=0.5)
            print(f"Lector activo en {puerto}. Transmitiendo datos hacia la web.")

            buffer = ""
            while True:
                if ser.in_waiting > 0:
                    caracteres = ser.read(ser.in_waiting).decode('latin-1', errors='ignore')
                    buffer += caracteres

                    if '\n' in buffer or '\r' in buffer:
                        lectura = buffer.strip()
                        buffer = ""
                        if lectura:
                            print(f"[COM3 -> WEB]: {lectura}")
                            ultimo_escaneo_crudo = {
                                'texto_crudo': lectura,
                                'timestamp': time.time()
                            }
                time.sleep(0.05)
        except serial.SerialException as e:
            print(f"Aviso {puerto}: {e}. Reintentando...")
            time.sleep(3)

# 1. Endpoint donde la web consulta qué leyó el escáner
@app.route('/lector-raw', methods=['GET'])
def obtener_lector_raw():
    return jsonify(ultimo_escaneo_crudo)

# 2. Endpoint donde la web pide insertar con la MATRÍCULA agregada
@app.route('/guardar-asistencia', methods=['POST'])
def guardar_asistencia():
    data = request.json or {}
    nombre = data.get('nombre')
    matricula = data.get('matricula', '')
    grado = data.get('grado')
    grupo = data.get('grupo')

    try:
        conexion = mysql.connector.connect(**DB_CONFIG)
        cursor = conexion.cursor()

        ahora = datetime.now()
        fecha_actual = ahora.strftime('%Y-%m-%d')
        hora_actual = ahora.strftime('%H:%M:%S')

        # Inserta matricula después del nombre
        query = """
            INSERT INTO asistencia (nombre, matricula, grado, grupo, fecha, hora)
            VALUES (%s, %s, %s, %s, %s, %s)
        """
        cursor.execute(query, (nombre, matricula, grado, grupo, fecha_actual, hora_actual))
        conexion.commit()
        cursor.close()
        conexion.close()

        print(f"[BD INSERTADO]: {nombre} | Matrícula: {matricula} ({grado}° {grupo}) a las {hora_actual}")
        return jsonify({'status': 'ok', 'fecha': fecha_actual, 'hora': hora_actual})

    except mysql.connector.Error as err:
        print(f"[ERROR BD]: {err}")
        return jsonify({'status': 'error', 'mensaje': str(err)}), 500

if __name__ == '__main__':
    t = threading.Thread(target=hilo_lector_serial, daemon=True)
    t.start()
    app.run(host='0.0.0.0', port=5000, debug=False)
