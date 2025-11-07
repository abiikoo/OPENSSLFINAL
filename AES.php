<?php
// Utiliza cifrado AES-256-CBC para proteger datos sensibles (como secret_2fa)

function aes_encrypt(string $plain, string $key, string $cipher='AES-256-CBC'): array {
  $iv = random_bytes(openssl_cipher_iv_length($cipher));
  $ct = openssl_encrypt($plain, $cipher, $key, 0, $iv);
  return ['ct'=>$ct, 'iv'=>base64_encode($iv)];
}

function aes_decrypt(string $ct, string $b64iv, string $key, string $cipher='AES-256-CBC') {
  $iv = base64_decode($b64iv);
  return openssl_decrypt($ct, $cipher, $key, 0, $iv);
}
