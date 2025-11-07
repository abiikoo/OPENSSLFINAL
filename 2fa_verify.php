<?php
session_start();
require_once __DIR__.'/../../db.php';
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../crypto/AES.php';

use OTPHP\TOTP;

$userId = $_SESSION['pending_2fa_user_id'] ?? $_SESSION['user_id'] ?? null;
if (!$userId) { header('Location: ../auth/login.php'); exit; }

$code = $_POST['code'] ?? '';
$APP_KEY_2FA = $_ENV['APP_KEY_2FA'] ?? getenv('APP_KEY_2FA') ?? 'clavepredeterminada1234567890123456';

$stmt = $pdo->prepare("SELECT secret_2fa, twofa_enabled FROM usuarios WHERE id=:id");
$stmt->execute([':id'=>$userId]);
$row = $stmt->fetch();

if (!$row || (int)$row['twofa_enabled']!==1) { header('Location: ../auth/dashboard.php'); exit; }

[$ct,$iv] = explode(':',$row['secret_2fa'],2);
$secret = aes_decrypt($ct,$iv,$APP_KEY_2FA);

$totp = TOTP::create($secret);
if ($totp->verify($code, time(), 1)) {
  $_SESSION['2fa_passed'] = true;
  $pdo->prepare("UPDATE usuarios SET twofa_last_verified_at=NOW() WHERE id=:id")
      ->execute([':id'=>$userId]);
  header('Location: ../auth/dashboard.php'); exit;
} else {
  header('Location: 2fa_verify_form.php?e=code'); exit;
}
