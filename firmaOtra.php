<?php
define("OPEN_SSL_CERT_DAYS_VALID", 365);

function cabecera($titulo) {
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . htmlspecialchars($titulo) . '</title>'
       . '<link rel="stylesheet" href="estilos.css"></head><body><div class="contenedor">';
}
function pie() { echo '</div></body></html>'; }
function fallo($mensaje) {
    echo '<div class="error">' . htmlspecialchars((string)$mensaje) . '</div>';
    echo '<a class="volver" href="firmaOtra.php">← Volver</a>';
    pie();
    exit;
}

cabecera('Generar certificado X.509');

// ---------- FORMULARIO ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <h2>Generar certificado digital autofirmado (X.509)</h2>
    <form method="post">
        <div class="campo">
            <label>País (2 letras)</label>
            <input type="text" name="countryName" maxlength="2" value="PA" required>
        </div>
        <div class="campo">
            <label>Localidad</label>
            <input type="text" name="localityName" required>
        </div>
        <div class="campo">
            <label>Organización</label>
            <input type="text" name="organizationName" required>
        </div>
        <div class="campo">
            <label>Nombre común (nombre completo)</label>
            <input type="text" name="commonName" required>
        </div>
        <div class="campo">
            <label>Correo electrónico</label>
            <input type="email" name="emailAddress" required>
        </div>
        <button type="submit">Generar certificado</button>
    </form>
    <?php
    pie();
    exit;
}

// ---------- DATOS DEL CERTIFICADO ----------
$dn = array(
    "countryName"      => strtoupper(substr(trim($_POST['countryName'] ?? 'PA'), 0, 2)),
    "localityName"     => trim($_POST['localityName'] ?? ''),
    "organizationName" => trim($_POST['organizationName'] ?? ''),
    "commonName"       => trim($_POST['commonName'] ?? ''),
    "emailAddress"     => trim($_POST['emailAddress'] ?? '')
);

// ---------- CONFIGURACIÓN: busca el openssl.cnf que trae WAMP ----------
$posibles = array_merge(
    glob('C:/wamp64/bin/apache/*/conf/openssl.cnf') ?: [],
    glob('C:/wamp64/bin/php/*/extras/ssl/openssl.cnf') ?: []
);
$configArgs = array(
    'digest_alg'       => 'sha256',
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA
);
if ($posibles) {
    $configArgs['config'] = $posibles[0];
}

// ---------- 1. PAR DE CLAVES ----------
$resourceNewKeyPair = openssl_pkey_new($configArgs);
if (!$resourceNewKeyPair) {
    fallo('Error al generar la clave: ' . openssl_error_string());
}
// ---------- 2. EXPORTAR CLAVE PRIVADA ----------
if (!openssl_pkey_export($resourceNewKeyPair, $privkey, null, $configArgs)) {
    fallo('Error al exportar la clave privada: ' . openssl_error_string());
}
// ---------- 3. CSR ----------
$csr = openssl_csr_new($dn, $resourceNewKeyPair, $configArgs);
if (!$csr) {
    fallo('Error al crear la CSR: ' . openssl_error_string());
}
// ---------- 4. CERTIFICADO AUTOFIRMADO ----------
$sscert = openssl_csr_sign($csr, null, $resourceNewKeyPair, OPEN_SSL_CERT_DAYS_VALID, $configArgs);
if (!$sscert) {
    fallo('Error al firmar la CSR: ' . openssl_error_string());
}
// ---------- 5. EXPORTAR Y GUARDAR ----------
openssl_x509_export($sscert, $certout);
if (!is_dir('keysCert')) {
    mkdir('keysCert');
}
file_put_contents('keysCert/certout.csr', $certout);
file_put_contents('keysCert/privkey.pem', $privkey);

// ---------- 6. MOSTRAR RESULTADOS ----------
$info = openssl_x509_parse($sscert);

echo '<h2>Certificado generado correctamente</h2>';
echo '<div class="ok">✅ Archivos guardados en keysCert/ (certout.csr y privkey.pem)</div>';

echo '<h3>Datos del certificado</h3><ul class="datos">';
echo '<li><b>País:</b> ' . htmlspecialchars($info['subject']['C'] ?? '') . '</li>';
echo '<li><b>Localidad:</b> ' . htmlspecialchars($info['subject']['L'] ?? '') . '</li>';
echo '<li><b>Organización:</b> ' . htmlspecialchars($info['subject']['O'] ?? '') . '</li>';
echo '<li><b>Nombre común:</b> ' . htmlspecialchars($info['subject']['CN'] ?? '') . '</li>';
echo '<li><b>Correo:</b> ' . htmlspecialchars($info['subject']['emailAddress'] ?? '') . '</li>';
echo '<li><b>Válido desde:</b> ' . date('d/m/Y H:i', $info['validFrom_time_t']) . '</li>';
echo '<li><b>Válido hasta:</b> ' . date('d/m/Y H:i', $info['validTo_time_t']) . '</li>';
echo '<li><b>Algoritmo de firma:</b> ' . htmlspecialchars($info['signatureTypeSN'] ?? '') . '</li>';
echo '</ul>';

echo '<h3>Certificado X.509 (keysCert/certout.csr)</h3>';
echo '<textarea class="codigo" rows="12" readonly>' . htmlspecialchars($certout) . '</textarea>';

echo '<h3>Clave privada (keysCert/privkey.pem)</h3>';
echo '<textarea class="codigo" rows="8" readonly>' . htmlspecialchars($privkey) . '</textarea>';
echo '<p class="nota">Solo con fines didácticos: en un sistema real la clave privada nunca se muestra ni se guarda sin protección.</p>';

echo '<a class="volver" href="firmaOtra.php">← Generar otro certificado</a>';
pie();