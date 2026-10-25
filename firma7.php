<?php
// ---------- FUNCIONES DE PRESENTACIÓN ----------
function cabecera($titulo) {
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . htmlspecialchars($titulo) . '</title>'
       . '<link rel="stylesheet" href="estilos.css"></head><body><div class="contenedor">';
}
function pie() { echo '</div></body></html>'; }
function fallo($mensaje) {
    echo '<div class="error">' . htmlspecialchars((string)$mensaje) . '</div>';
    echo '<a class="volver" href="">← Volver</a>';
    pie();
    exit;
}

cabecera('Firma digital RSA');

// ---------- FORMULARIO ----------
if (!isset($_POST['datos']) || trim($_POST['datos']) === '') {
    ?>
    <h2>Firma digital con OpenSSL (RSA + SHA-256)</h2>
    <form method="post">
        <div class="campo">
            <label for="datos">Texto a firmar:</label>
            <textarea name="datos" id="datos" rows="4" required></textarea>
        </div>
        <button type="submit">Firmar</button>
    </form>
    <?php
    pie();
    exit;
}

$datos = $_POST['datos'];

// ---------- CONFIGURACIÓN: busca el openssl.cnf que trae WAMP ----------
$posibles = array_merge(
    glob('C:/wamp64/bin/apache/*/conf/openssl.cnf') ?: [],
    glob('C:/wamp64/bin/php/*/extras/ssl/openssl.cnf') ?: []
);
$configArgs = array(
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA
);
if ($posibles) {
    $configArgs['config'] = $posibles[0];
}

// ---------- GENERAR EL PAR DE CLAVES ----------
$resourceNewKeyPair = openssl_pkey_new($configArgs);
if (!$resourceNewKeyPair) {
    fallo('Error al generar la clave. Revisa la ruta del openssl.cnf. ' . openssl_error_string());
}
$details = openssl_pkey_get_details($resourceNewKeyPair);
$publicKeyPem = $details['key'];

if (!openssl_pkey_export($resourceNewKeyPair, $privateKeyPem, NULL, $configArgs)) {
    fallo('Error al exportar la clave privada: ' . openssl_error_string());
}

// ---------- GUARDAR EN DISCO ----------
if (!is_dir('keys')) {
    mkdir('keys');
}
file_put_contents('keys/private_key.pem', $privateKeyPem);
file_put_contents('keys/public_key.pem', $publicKeyPem);

// Compruebo que el archivo se generó bien leyéndolo de disco
$privateKeyPem = file_get_contents('keys/private_key.pem');
$resourcePrivateKey = openssl_get_privatekey($privateKeyPem);
if (!$resourcePrivateKey) {
    fallo('No se pudo leer la clave privada: ' . openssl_error_string());
}

// ---------- FIRMAR ----------
if (!openssl_sign($datos, $firma, $resourcePrivateKey, OPENSSL_ALGO_SHA256)) {
    fallo('Error al firmar: ' . openssl_error_string());
}
file_put_contents('keys/signature.dat', $firma);

// ---------- RESULTADO Y VERIFICACIÓN ----------
echo '<h2>Resultado de la firma</h2>';
echo '<h3>Texto firmado</h3><p>' . htmlspecialchars($datos) . '</p>';
echo '<h3>Clave pública (keys/public_key.pem)</h3>';
echo '<textarea class="codigo" rows="8" readonly>' . htmlspecialchars($publicKeyPem) . '</textarea>';
echo '<h3>Firma (base64)</h3>';
echo '<textarea class="codigo" rows="6" readonly>' . base64_encode($firma) . '</textarea>';

if (openssl_verify($datos, $firma, $publicKeyPem, 'sha256WithRSAEncryption') === 1) {
    echo '<div class="ok">✅ La firma es válida y los datos son confiables.</div>';
} else {
    echo '<div class="error">❌ La firma es inválida y/o los datos fueron alterados.</div>';
}
echo '<a class="volver" href="firma7.php">← Firmar otro texto</a>';
pie();