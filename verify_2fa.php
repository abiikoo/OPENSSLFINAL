<?php
// verify_2fa.php
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/db.php';

use OTPHP\TOTP;

// ¿Confirmación de setup (enable_2fa) o segundo paso del login?
$fromSetup = isset($_POST['confirm_setup']) && $_POST['confirm_setup'] === '1';

if($fromSetup){
  if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
  $userId = (int)$_SESSION['user_id'];
}else{
  if(!isset($_SESSION['user_id_pending_2fa'])){ header('Location: login.php'); exit; }
  $userId = (int)$_SESSION['user_id_pending_2fa'];
}

$error = null;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])){
  $code = trim($_POST['code']);

  $stmt = $pdo->prepare("SELECT secret_2fa FROM users WHERE id = ?");
  $stmt->execute([$userId]);
  $secret = $stmt->fetchColumn();

  if(!$secret){
    $error = 'No hay 2FA configurado para este usuario.';
  }else{
    $totp = TOTP::create($secret);
    // tolerancia 1 paso (30s) hacia atrás/adelante
    $isValid = $totp->verify($code, null, 1);

    if($isValid){
      session_regenerate_id(true);
      $_SESSION['2fa_ok'] = true;

      if($fromSetup){
        header('Location: dashboard.php'); exit;
      }else{
        $_SESSION['user_id'] = $userId;
        unset($_SESSION['user_id_pending_2fa']);
        header('Location: dashboard.php'); exit;
      }
    }else{
      $error = 'Código inválido o expirado. Inténtalo de nuevo.';
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><title>Verificación 2FA</title>
  <style>
    body{font-family:Arial;background:#f5f5f5}
    .card{width:380px;margin:60px auto;padding:24px;background:#fff;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.08)}
    input,button{width:100%;padding:10px;margin-top:10px;border-radius:6px;border:1px solid #ddd}
    button{background:#007bff;color:#fff;border:none;cursor:pointer}
    .error{color:#c0392b;margin-top:8px}
  </style>
</head>
<body>
  <div class="card">
    <h2>Verificación 2FA</h2>
    <?php if($error): ?><div class="error"><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
    <form method="post">
      <label>Ingresa tu código de 6 dígitos:</label>
      <input type="text" name="code" pattern="\d{6}" maxlength="6" required>
      <button type="submit">Verificar</button>
    </form>
  </div>
</body>
</html>
