<?php
function cabecera($titulo) {
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . htmlspecialchars($titulo) . '</title>'
       . '<link rel="stylesheet" href="estilos.css"></head><body><div class="contenedor">';
}
function pie() { echo '</div></body></html>'; }

cabecera('Ejemplo con Libsodium');

if (!extension_loaded('sodium')) {
    echo '<div class="error">La extensión sodium no está habilitada en PHP.</div>';
    pie();
    exit;
}

// ---------- FORMULARIO ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || trim($_POST['texto'] ?? '') === '') {
    ?>
    <h2>Libsodium: cifrado autenticado y hash de contraseñas</h2>
    <form method="post">
        <div class="campo">
            <label for="texto">Texto a cifrar</label>
            <textarea name="texto" id="texto" rows="3" required></textarea>
        </div>
        <div class="campo">
            <label for="clave">Contraseña para generar su hash Argon2</label>
            <input type="password" name="clave" id="clave" required>
        </div>
        <button type="submit">Ejecutar</button>
    </form>
    <?php
    pie();
    exit;
}

$texto = $_POST['texto'];
$contrasena = $_POST['clave'] ?? '';

// ---------- 1. CIFRADO AUTENTICADO (XSalsa20 + Poly1305) ----------
$clave = sodium_crypto_secretbox_keygen();                       // clave aleatoria de 32 bytes
$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);       // número único de 24 bytes
$cifrado = sodium_crypto_secretbox($texto, $nonce, $clave);
$descifrado = sodium_crypto_secretbox_open($cifrado, $nonce, $clave);

// ---------- 2. PRUEBA DE INTEGRIDAD: alterar un byte del cifrado ----------
$alterado = $cifrado;
$alterado[0] = chr(ord($alterado[0]) ^ 1);
$resultadoAlterado = sodium_crypto_secretbox_open($alterado, $nonce, $clave);

// ---------- 3. HASH DE CONTRASEÑA CON ARGON2 ----------
$hash = sodium_crypto_pwhash_str(
    $contrasena,
    SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
    SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
);
$verificaCorrecta = sodium_crypto_pwhash_str_verify($hash, $contrasena);
$verificaIncorrecta = sodium_crypto_pwhash_str_verify($hash, $contrasena . 'x');

// ---------- RESULTADOS ----------
echo '<h2>Resultados con Libsodium</h2>';

echo '<h3>1. Cifrado autenticado (sodium_crypto_secretbox)</h3>';
echo '<ul class="datos">';
echo '<li><b>Texto original:</b> ' . htmlspecialchars($texto) . '</li>';
echo '<li><b>Clave (hex):</b> ' . sodium_bin2hex($clave) . '</li>';
echo '<li><b>Nonce (hex):</b> ' . sodium_bin2hex($nonce) . '</li>';
echo '</ul>';
echo '<h3>Texto cifrado (hex)</h3>';
echo '<textarea class="codigo" rows="4" readonly>' . sodium_bin2hex($cifrado) . '</textarea>';

if ($descifrado !== false) {
    echo '<div class="ok">✅ Descifrado correcto: ' . htmlspecialchars($descifrado) . '</div>';
} else {
    echo '<div class="error">❌ No se pudo descifrar.</div>';
}

echo '<h3>2. Prueba de integridad</h3>';
if ($resultadoAlterado === false) {
    echo '<div class="ok">✅ Se alteró un byte del texto cifrado y Sodium rechazó el mensaje: la integridad está protegida.</div>';
} else {
    echo '<div class="error">❌ El mensaje alterado fue aceptado (no debería pasar).</div>';
}

echo '<h3>3. Hash de contraseña (sodium_crypto_pwhash_str, Argon2)</h3>';
echo '<textarea class="codigo" rows="3" readonly>' . htmlspecialchars($hash) . '</textarea>';
echo '<ul class="datos">';
echo '<li><b>Verificar con la contraseña correcta:</b> ' . ($verificaCorrecta ? '✅ válida' : '❌ inválida') . '</li>';
echo '<li><b>Verificar con una contraseña incorrecta:</b> ' . ($verificaIncorrecta ? '✅ válida' : '❌ inválida (correcto)') . '</li>';
echo '</ul>';
echo '<p class="nota">Solo con fines didácticos: en un sistema real la clave de cifrado nunca se muestra en pantalla.</p>';

echo '<a class="volver" href="ejemploSodium.php">← Probar de nuevo</a>';
pie();