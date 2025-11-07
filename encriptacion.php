<?php
echo "<h1> Encriptación Simétrica con OpenSSL</h1>";

// Configuración
$metodo = "AES-256-CBC";
$clave = "clave_secreta_32_caracteres!!"; // 32 caracteres para AES-256
$texto_original = "Mensaje ultra secreto para encriptar. Fecha: " . date('Y-m-d H:i:s');

echo "<h2> Texto Original:</h2>";
echo "<pre>" . htmlspecialchars($texto_original) . "</pre>";

echo "<h2> Configuración:</h2>";
echo "• Algoritmo: $metodo<br>";
echo "• Longitud clave: " . strlen($clave) . " caracteres<br>";
echo "• IV Length: " . openssl_cipher_iv_length($metodo) . " bytes<br>";

// Generar IV (Initialization Vector)
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($metodo));
echo "• IV generado: " . bin2hex($iv) . "<br>";

// ENCRIPTAR
$texto_cifrado = openssl_encrypt($texto_original, $metodo, $clave, 0, $iv);

echo "<h2> Texto Cifrado:</h2>";
echo "<pre>" . $texto_cifrado . "</pre>";

// DESENCRIPTAR
$texto_descifrado = openssl_decrypt($texto_cifrado, $metodo, $clave, 0, $iv);

echo "<h2> Texto Descifrado:</h2>";
echo "<pre>" . htmlspecialchars($texto_descifrado) . "</pre>";

// Verificar integridad
echo "<h2> Verificación:</h2>";
if ($texto_original === $texto_descifrado) {
    echo " <strong>ENCRIPTACIÓN/DESENCRIPTACIÓN EXITOSA</strong><br>";
    echo "• El texto original y descifrado son idénticos<br>";
    echo "• El proceso de encriptación funciona correctamente<br>";
} else {
    echo " <strong>ERROR EN EL PROCESO</strong><br>";
    echo "• El texto descifrado no coincide con el original<br>";
}

// Demostración con datos incorrectos
echo "<h2> Prueba de Seguridad:</h2>";
$clave_incorrecta = "clave_incorrecta_32_caracter!!";
$texto_descifrado_incorrecto = openssl_decrypt($texto_cifrado, $metodo, $clave_incorrecta, 0, $iv);

if ($texto_descifrado_incorrecto === false || $texto_descifrado_incorrecto !== $texto_original) {
    echo " <strong>SEGURIDAD VERIFICADA:</strong> Clave incorrecta no puede desencriptar<br>";
} else {
    echo " <strong>FALLA DE SEGURIDAD:</strong> Clave incorrecta pudo desencriptar<br>";
}
?>