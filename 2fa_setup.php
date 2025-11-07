<?php
session_start();
require_once __DIR__.'/../../db.php';
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../crypto/AES.php';

use OTPHP\TOTP;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

if (empty($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT email, secret_2fa, twofa_enabled FROM usuarios WHERE id=:id");
$stmt->execute([':id'=>$userId]);
$u = $stmt->fetch();

$APP_KEY_2FA = $_ENV['APP_KEY_2FA'] ?? getenv('APP_KEY_2FA') ?? 'clavepredeterminada1234567890123456';
$secretPlain = null;

// Generar un nuevo secreto 2FA
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['enable_2fa'])) {
  $totp = TOTP::create();
  $secretPlain = $totp->getSecret();

  // cifrar con AES
  $enc = aes_encrypt($secretPlain, $APP_KEY_2FA);
  $pdo->prepare("UPDATE usuarios SET secret_2fa=CONCAT(:ct,':',:iv), twofa_enabled=1 WHERE id=:id")
      ->execute([':ct'=>$enc['ct'], ':iv'=>$enc['iv'], ':id'=>$userId]);

  header('Location: 2fa_setup.php');
  exit;
}

// Si ya existe secreto, descifrar
if (!empty($u['secret_2fa'])) {
  [$ct,$iv] = explode(':',$u['secret_2fa'],2);
  $secretPlain = aes_decrypt($ct,$iv,$APP_KEY_2FA);
}

// Generar QR
$qrB64 = null;
if ($secretPlain) {
  $totp = TOTP::create($secretPlain);
  $totp->setLabel($u['email']);
  $totp->setIssuer('Auth2FAFinal');
  $qr = Builder::create()
    ->writer(new PngWriter())
    ->data($totp->getProvisioningUri())
    ->size(280)
    ->margin(8)
    ->build();
  $qrB64 = base64_encode($qr->getString());
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Configurar 2FA</title></head>
<body>
<h2>Configuración de doble autenticación (2FA)</h2>

<?php if ((int)$u['twofa_enabled']===0): ?>
  <form method="post">
    <button name="enable_2fa" value="1">Activar 2FA</button>
  </form>
<?php else: ?>
  <p>Escanea este código QR con Google Authenticator:</p>
  <?php if ($qrB64): ?>
    <img src="data:image/png;base64,<?= $qrB64 ?>" alt="QR Code">
    <p><b>Secreto:</b> <?= htmlspecialchars($secretPlain) ?></p>
    <form method="post" action="2fa_verify.php">
      <input name="code" maxlength="6" pattern="\d{6}" placeholder="Código 6 dígitos" required>
      <button>Verificar</button>
    </form>
  <?php endif; ?>
  <form method="post" action="2fa_toggle.php">
    <button name="disable_2fa" value="1">Desactivar 2FA</button>
  </form>
<?php endif; ?>

<p><a href="../auth/dashboard.php">Volver al dashboard</a></p>
</body>
</html>
