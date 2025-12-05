<?php
// enable_2fa.php - Versión con diagnóstico completo
declare(strict_types=1);

// IMPORTANTE: No debe haber NINGÚN espacio o salto antes de este <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/db.php';

use OTPHP\TOTP;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// Verificar GD
if (!extension_loaded('gd')) {
    die('ERROR: La extensión GD no está habilitada en PHP. Edita php.ini y descomenta: extension=gd');
}

// 1) Garantizar identidad del usuario
if (!isset($_SESSION['user_id'])) {
  if (isset($_SESSION['username'])) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $id = $stmt->fetchColumn();
    if ($id) {
      $_SESSION['user_id'] = (int)$id;
    } else {
      header('Location: login.php'); 
      exit;
    }
  } else {
    header('Location: login.php'); 
    exit;
  }
}

$userId = (int)$_SESSION['user_id'];

// 2) Traer usuario y secreto
$stmt = $pdo->prepare("SELECT username, secret_2fa FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) {
  die('Usuario no encontrado.');
}

$secret = $user['secret_2fa'];
if (empty($secret)) {
  $totpNew = TOTP::create();
  $secret  = $totpNew->getSecret();
  $up = $pdo->prepare("UPDATE users SET secret_2fa = ? WHERE id = ?");
  $up->execute([$secret, $userId]);
}

// 3) Construir URI otpauth://
$totp = TOTP::create($secret);
$totp->setIssuer('Login-OpenSSL');
$totp->setLabel($user['username']);
$uri = $totp->getProvisioningUri();

// 4) Generar QR como PNG con manejo robusto de errores
$pngBase64 = '';
$errorMsg = '';

try {
  $options = new QROptions([
    'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel'     => QRCode::ECC_L,
    'scale'        => 5,
    'imageBase64'  => false,
  ]);
  
  $qrcode = new QRCode($options);
  $pngBinary = $qrcode->render($uri);
  
  if (empty($pngBinary)) {
    throw new Exception('El render devolvió vacío');
  }
  
  // Verificar que sea PNG válido
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mimeType = $finfo->buffer($pngBinary);
  
  if ($mimeType !== 'image/png') {
    throw new Exception("El resultado no es PNG válido, es: $mimeType");
  }
  
  $pngBase64 = base64_encode($pngBinary);
  
  if (empty($pngBase64)) {
    throw new Exception('base64_encode devolvió vacío');
  }
  
} catch (Exception $e) {
  $errorMsg = 'Error al generar QR: ' . $e->getMessage();
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Habilitar 2FA</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{--card-w: 640px;}
    *{box-sizing:border-box}
    body{font-family:system-ui, -apple-system, Segoe UI, Roboto, Arial, Helvetica, sans-serif;background:#f5f6f8;margin:0;padding:20px}
    .card{max-width:var(--card-w);margin:20px auto;padding:24px;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
    h1{margin:0 0 12px;font-size:24px}
    p{margin:8px 0 12px;color:#333}
    .qr{display:flex;justify-content:center;align-items:center;margin:12px 0 16px;min-height:240px;background:#fafbfc;border-radius:8px;padding:10px}
    .qr img{width:220px;height:220px;display:block;image-rendering:crisp-edges}
    .label{font-weight:600;margin-top:6px}
    .secret{
      display:block;margin:6px 0 12px;padding:10px;border:1px solid #e3e6ea;border-radius:8px;background:#fafbfc;
      font:14px/1.35 ui-monospace,SFMono-Regular,Consolas,"Liberation Mono",Menlo,monospace;
      overflow-wrap:anywhere;word-break:break-word;white-space:normal;
    }
    .uri{
      display:block;margin:6px 0 12px;padding:10px;border:1px solid #e3e6ea;border-radius:8px;background:#fff8dc;
      font:12px/1.35 ui-monospace,SFMono-Regular,Consolas,"Liberation Mono",Menlo,monospace;
      overflow-wrap:anywhere;word-break:break-all;
    }
    label{display:block;margin-top:10px;margin-bottom:6px;font-weight:500}
    input,button{
      width:100%;padding:12px;border-radius:8px;border:1px solid #d7dbe0;font-size:15px;
    }
    input:focus{outline:none;border-color:#7aa7ff;box-shadow:0 0 0 3px rgba(13,110,253,.15)}
    button{margin-top:10px;background:#0d6efd;color:#fff;border:none;cursor:pointer}
    button:hover{filter:brightness(.95)}
    .note{font-size:13px;color:#666;margin-top:10px}
    a{color:#0d6efd;text-decoration:none}
    a:hover{text-decoration:underline}
    .error{background:#fff3cd;border:1px solid #ffc107;padding:12px;border-radius:8px;margin-bottom:15px;color:#856404;font-size:14px}
    .debug{background:#f0f0f0;border:1px solid #ccc;padding:10px;border-radius:8px;margin:10px 0;font-size:12px;font-family:monospace}
  </style>
</head>
<body>
  <div class="card">
    <h1>Habilitar Autenticación de Dos Factores (2FA)</h1>
    
    <?php if (!empty($errorMsg)): ?>
      <div class="error">
        <strong>⚠️ Error:</strong> <?= htmlspecialchars($errorMsg) ?>
        <div class="debug">
          <strong>Debug info:</strong><br>
          GD habilitado: <?= extension_loaded('gd') ? 'SÍ' : 'NO' ?><br>
          Versión PHP: <?= PHP_VERSION ?><br>
          URI length: <?= strlen($uri) ?> chars
        </div>
      </div>
    <?php endif; ?>

    <p>Escanea este código QR en Google Authenticator (o similar) y luego ingresa el código de 6 dígitos.</p>

    <div class="qr">
      <?php if (!empty($pngBase64)): ?>
        <img src="data:image/png;base64,<?= $pngBase64 ?>" alt="QR 2FA" onerror="this.parentElement.innerHTML='<div class=error>Error al cargar imagen QR. Base64 length: <?= strlen($pngBase64) ?></div>'">
      <?php else: ?>
        <div class="error">No se pudo generar el código QR. Usa el secreto manual abajo.</div>
      <?php endif; ?>
    </div>

    <div class="label">Secreto (respaldo manual):</div>
    <div class="secret"><?= htmlspecialchars($secret, ENT_QUOTES, 'UTF-8') ?></div>

    <details style="margin-top:10px">
      <summary style="cursor:pointer;color:#0d6efd">🔍 Ver URI completa (debug)</summary>
      <div class="uri"><?= htmlspecialchars($uri, ENT_QUOTES, 'UTF-8') ?></div>
    </details>

    <form method="post" action="verify_2fa.php">
      <label for="code">Código 2FA:</label>
      <input id="code" type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6" required>
      <button type="submit" name="confirm_setup" value="1">Confirmar y activar</button>
    </form>

    <p class="note">Si los códigos no coinciden, sincroniza la hora de tu PC y del teléfono.</p>
    <p><a href="dashboard.php">Volver</a></p>
  </div>
</body>
</html>