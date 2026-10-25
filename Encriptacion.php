<?php
function cabecera($titulo) {
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . htmlspecialchars($titulo) . '</title>'
       . '<link rel="stylesheet" href="estilos.css"></head><body><div class="contenedor">';
}
function pie() { echo '</div></body></html>'; }

cabecera('Encriptación simple con OpenSSL');

$metodo = 'AES-128-CBC';

// ---------- FORMULARIO ----------
$texto = trim($_POST['texto'] ?? '');
$clave = $_POST['clave'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($texto === '') {
        $error = 'Escribe el texto a cifrar.';
    } elseif (strlen($clave) !== 16) {
        $error = 'La clave debe tener exactamente 16 caracteres para AES-128 (tiene ' . strlen($clave) . ').';
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $error !== '') {
    ?>
    <h2>Encriptación simple con OpenSSL (AES-128-CBC)</h2>
    <?php if ($error !== '') echo '<div class="error">' . htmlspecialchars($error) . '</div>'; ?>
    <form method="post">
        <div class="campo">
            <label for="texto">Texto a cifrar</label>
            <textarea name="texto" id="texto" rows="3" required><?= htmlspecialchars($texto) ?></textarea>
        </div>
        <div class="campo">
            <label for="clave">Clave (exactamente 16 caracteres)</label>
            <input type="text" name="clave" id="clave" maxlength="16" value="<?= htmlspecialchars($clave) ?>" required>
        </div>
        <button type="submit">Cifrar y descifrar</button>
    </form>
    <?php
    pie();
    exit;
}

// ---------- CIFRAR ----------
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($metodo));
$cifrado = openssl_encrypt($texto, $metodo, $clave, 0, $iv);
if ($cifrado === false) {
    echo '<div class="error">Error al cifrar: ' . htmlspecialchars((string)openssl_error_string()) . '</div>';
    pie();
    exit;
}

// ---------- DESCIFRAR ----------
$descifrado = openssl_decrypt($cifrado, $metodo, $clave, 0, $iv);

// ---------- RESULTADOS ----------
echo '<h2>Resultado</h2>';
echo '<ul class="datos">';
echo '<li><b>Algoritmo:</b> ' . $metodo . '</li>';
echo '<li><b>Texto original:</b> ' . htmlspecialchars($texto) . '</li>';
echo '<li><b>Vector de inicialización (IV, base64):</b> ' . base64_encode($iv) . '</li>';
echo '</ul>';
echo '<h3>Resultado del cifrado (base64)</h3>';
echo '<textarea class="codigo" rows="4" readonly>' . htmlspecialchars($cifrado) . '</textarea>';
echo '<h3>Resultado del descifrado</h3>';
echo '<div class="ok">✅ ' . htmlspecialchars((string)$descifrado) . '</div>';
echo '<p class="nota">El IV es aleatorio en cada ejecución, por eso el texto cifrado cambia aunque el mensaje y la clave sean iguales.</p>';
echo '<a class="volver" href="Encriptacion.php">← Cifrar otro texto</a>';
pie();