<?php
declare(strict_types=1);

/**
 * Datos de la institucion educational.
 *
 * Fuente unica de verdad para el nombre, el campus, el lema, el contacto
 * y las claves de registro. Ninguna pantalla debe escribir estos datos
 * a mano: se leen de aqui.
 *
 * Los documentos oficiales se imprimen en mayusculas, pero aqui los
 * valores van en su forma natural: cada pantalla decide si los
 * convierte con mb_strtoupper() al mostrarlos.
 */

/**
 * @return array<string, mixed>
 */
function pims_institucion(): array
{
    return [
        'nombre' => 'Centro Escolar Aparicio',
        'campus' => 'Campus Calpulalpan',
        'lema' => 'Camino, Verdad y Vida',
        'lema_corto' => '#SomosFranciscanos.',
        'sistema' => 'Sistema Integral de Gestión Escolar',
        'descripcion' => 'Educación y formación académica',
        'incorporacion' => 'Incorporados a la Secretaría de Educación Pública',
        'director' => 'Fray Ricardo Luciano Maldonado Castillo',
        'cargo_director' => 'Dirección escolar',
        'siglas' => 'CEA',

        'niveles' => ['Preescolar', 'Primaria', 'Secundaria'],

        // Rutas públicas controladas; nunca se construyen con datos del formulario.
        'membretes' => [
            'Preescolar' => 'assets/img/membretes/preescolar.png',
            'Primaria' => 'assets/img/membretes/primaria.jpg',
            'Secundaria' => 'assets/img/membretes/secundaria.png',
        ],

        'claves_sep' => [
            'Preescolar' => '29PJN0014T',
            'Primaria' => '29PPR0010N',
            'Secundaria' => '29PES0020N',
        ],

        'contacto' => [
            'direccion' => '5 de Mayo No. 18, Col. Centro',
            'cp' => '90200',
            'ciudad' => 'Calpulalpan',
            'estado' => 'Tlaxcala',
            'pais' => 'México',
            'telefonos' => ['749 918 0494', '749 110 5918'],
            'correo' => 'comunicacion.aparicio@gmail.com',
        ],

        'redes' => [
            'instagram' => 'https://www.instagram.com/aparicio_calpulalpan?igsh=bDUzYzdmOG12aXd0',
            'facebook' => 'https://www.facebook.com/share/1CVeFMm4bu/',
            'youtube' => 'https://youtube.com/@apariciocalpulalpan7260',
        ],

        'copyright' => '© 2026 Centro Escolar Aparicio',
    ];
}

/**
 * Direccion en una sola linea.
 */
function pims_direccion_linea(): string
{
    $contacto = pims_institucion()['contacto'];

    return implode(', ', [
        $contacto['direccion'],
        $contacto['cp'],
        $contacto['ciudad'],
        $contacto['estado'],
        $contacto['pais'],
    ]);
}

/**
 * Escapar para imprimir en HTML.
 */
function e(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Texto recibido del formulario: sin espacios sobrantes.
 */
function pims_texto(string $valor): string
{
    return trim($valor);
}
