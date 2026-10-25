   <?php
   echo "OpenSSL: " . (extension_loaded('openssl') ? 'OK' : 'NO') . "<br>";
   echo "Sodium: " . (extension_loaded('sodium') ? 'OK' : 'NO') . "<br>";
   echo "Versión PHP: " . PHP_VERSION;