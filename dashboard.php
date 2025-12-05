<?php
// dashboard.php
require __DIR__ . '/bloque_seguridad.php';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'usuario';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><title>Panel</title>
  <style>
    body{font-family:Arial;background:#f5f5f5}
    .card{width:420px;margin:60px auto;padding:24px;background:#fff;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.08);text-align:center}
    a.btn, button.btn{display:inline-block;padding:10px 16px;border-radius:6px;text-decoration:none;margin-top:12px}
    .primary{background:#007bff;color:#fff}
    .success{background:#28a745;color:#fff}
    .danger{background:#dc3545;color:#fff}
  </style>
</head>
<body>
  <div class="card">
    <h2>Bienvenido, <?= htmlspecialchars($username,ENT_QUOTES,'UTF-8') ?>!</h2>
    <p>¡Has iniciado sesión correctamente!</p>

    <p><a href="enable_2fa.php" class="btn primary">Habilitar 2FA</a></p>
    <p><a href="logout.php" class="btn success">Cerrar sesión</a></p>
  </div>
</body>
</html>
