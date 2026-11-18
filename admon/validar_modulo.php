<?php

require_once '../complementos/conexion.php';

$pagina = basename($_SERVER['PHP_SELF']);
$raizProyecto = realpath(__DIR__ . '/..');
$archivoActual = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
$rutaModulo = ($raizProyecto && $archivoActual && strpos($archivoActual, $raizProyecto) === 0)
    ? str_replace('\\', '/', ltrim(substr($archivoActual, strlen($raizProyecto)), DIRECTORY_SEPARATOR))
    : $pagina;

$sql = "SELECT estado
        FROM urls_sistema
        WHERE url = :ruta OR url = :pagina
        ORDER BY (url = :rutaOrden) DESC
        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':ruta' => $rutaModulo,
    ':pagina' => $pagina,
    ':rutaOrden' => $rutaModulo
]);

$r = $stmt->fetch();

if($r && $r['estado']==0){

    die("
    <div style='
        display:flex;
        justify-content:center;
        align-items:center;
        height:100vh;
        font-family:Arial;
    '>
        <div>
            <h1>Módulo desactivado temporalmente</h1>
        </div>
    </div>
    ");

}

// Mantiene actualizadas las páginas que ya estaban abiertas cuando se
// desactiva un módulo desde el panel de administración.
if ($r) {
    echo '<style>
        #modulo-desactivado-en-vivo {
            position: fixed; inset: 0; z-index: 2147483647;
            display: flex; align-items: center; justify-content: center;
            padding: 24px; background: #fff; color: #171717;
            font-family: Arial, sans-serif; text-align: center;
        }
        #modulo-desactivado-en-vivo h1 { margin: 0 0 10px; font-size: clamp(1.5rem, 4vw, 2.25rem); }
        #modulo-desactivado-en-vivo p { margin: 0; color: #555; }
    </style>
    <script>
    (() => {
        if (window.__autoModuloPoll) return;
        window.__autoModuloPoll = true;
        const pagina = ' . json_encode($rutaModulo, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ';
        const revisarEstado = async () => {
            try {
                const parametros = new URLSearchParams();
                parametros.set("url", pagina);
                const respuesta = await fetch("../admon/estado_modulo.php", {
                    method: "POST",
                    cache: "no-store",
                    headers: { "Accept": "application/json", "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
                    body: parametros.toString()
                });
                if (!respuesta.ok) return;
                const modulo = await respuesta.json();
                let aviso = document.getElementById("modulo-desactivado-en-vivo");
                // Un módulo sin registro no está desactivado; solo se bloquea
                // cuando existe y el administrador guardó explícitamente estado 0.
                if (modulo.estado !== null && Number(modulo.estado) === 0 && !aviso) {
                    aviso = document.createElement("main");
                    aviso.id = "modulo-desactivado-en-vivo";
                    aviso.setAttribute("role", "alert");
                    aviso.innerHTML = "<div><h1>Módulo desactivado temporalmente</h1><p>El acceso a este módulo fue desactivado por la administración.</p></div>";
                    document.body.appendChild(aviso);
                } else if (modulo.estado !== null && Number(modulo.estado) === 1 && aviso) {
                    aviso.remove();
                }
            } catch (error) {
                console.error("No se pudo verificar el estado del módulo:", error);
            }
        };
        revisarEstado();
        window.setInterval(revisarEstado, 2000);
    })();
    </script>';
}
?>
