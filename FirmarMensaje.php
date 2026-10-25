<?php
function cabecera($titulo) {
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . htmlspecialchars($titulo) . '</title>'
       . '<link rel="stylesheet" href="estilos.css"></head><body><div class="contenedor">';
}
function pie() { echo '</div></body></html>'; }
function fallo($mensaje) {
    echo '<div class="error">' . htmlspecialchars((string)$mensaje) . '</div>';
    echo '<a class="volver" href="FirmarMensaje.php">← Volver</a>';
    pie();
    exit;
}

cabecera('Firmar y verificar un mensaje');

// ---------- FORMULARIO ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || trim($_POST['mensaje'] ?? '') === '') {
    ?>
    <h2>Firmar y verificar un mensaje (RSA + SHA-256)</h2>
    <form method="post">
        <div class="campo">
            <label for="mensaje">1. Mensaje a firmar</label>
            <textarea name="mensaje" id="mensaje" rows="3" required></textarea>
        </div>
        <div class="campo">
            <label for="mensaje_verificar">2. Mensaje a verificar (opcional)</label>
            <textarea name="mensaje_verificar" id="mensaje_verificar" rows="3"></textarea>
            <p class="nota">Si lo dejas vacío se verifica el mismo mensaje. Escribe uno distinto para probar una firma inválida.</p>
        </div>
        <button type="submit">Firmar y verificar</button>
    </form>
    <?php
    pie();
    exit;
}

$mensaje = $_POST['mensaje'];
$mensajeVerificar = trim($_POST['mensaje_verificar'] ?? '') === '' ? $mensaje : $_POST['mensaje_verificar'];

// ---------- COMPROBAR LOS ARCHIVOS DE firmaOtra.php ----------
if (!file_exists('keysCert/privkey.pem') || !file_exists('keysCert/certout.csr')) {
    echo '<div class="aviso">⚠ No se encontraron keysCert/privkey.pem y keysCert/certout.csr. '
       . 'Ejecuta primero <a href="firmaOtra.php">firmaOtra.php</a>.</div>';
    pie();
    exit;
}

// ---------- PASO 1: FIRMAR CON LA CLAVE PRIVADA ----------
$privateKey = file_get_contents('keysCert/privkey.pem');
$privKeyResource = openssl_pkey_get_private($privateKey);
if (!$privKeyResource) {
    fallo('Error al cargar la clave privada: ' . openssl_error_string());
}
if (!openssl_sign($mensaje, $firmaBinaria, $privKeyResource, OPENSSL_ALGO_SHA256)) {
    fallo('Error al firmar: ' . openssl_error_string());
}
$firmaBase64 = base64_encode($firmaBinaria);

// ---------- PASO 2: VERIFICAR CON EL CERTIFICADO PÚBLICO ----------
$cert = file_get_contents('keysCert/certout.csr');
$pubKeyResource = openssl_pkey_get_public($cert);
if (!$pubKeyResource) {
    fallo('Error al cargar el certificado: ' . openssl_error_string());
}
$ok = openssl_verify($mensajeVerificar, base64_decode($firmaBase64), $pubKeyResource, OPENSSL_ALGO_SHA256);

// ---------- RESULTADOS ----------
echo '<h2>Resultado</h2>';
echo '<h3>Mensaje firmado</h3><p>' . htmlspecialchars($mensaje) . '</p>';
echo '<h3>Firma (base64)</h3>';
echo '<textarea class="codigo" rows="6" readonly>' . $firmaBase64 . '</textarea>';
echo '<h3>Mensaje verificado</h3><p>' . htmlspecialchars($mensajeVerificar) . '</p>';

if ($ok === 1) {
    echo '<div class="ok">✅ Firma válida. El mensaje no fue alterado.</div>';
} elseif ($ok === 0) {
    echo '<div class="error">❌ Firma inválida. El mensaje pudo haber sido modificado.</div>';
} else {
    echo '<div class="aviso">⚠ Error al verificar la firma: ' . htmlspecialchars((string)openssl_error_string()) . '</div>';
}
echo '<a class="volver" href="FirmarMensaje.php">← Probar con otro mensaje</a>';
pie();