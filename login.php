<?php
// login.php
session_start();
require __DIR__ . '/db.php';
require __DIR__ . '/vendor/autoload.php'; // por si usas utilidades en otras partes

// Si ya está logueado y con 2FA OK, mándalo directo
if(isset($_SESSION['user_id']) && !empty($_SESSION['2fa_ok'])){
  header('Location: dashboard.php'); exit;
}

$error = null;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $username = isset($_POST['username']) ? trim($_POST['username']) : '';
  $password = isset($_POST['password']) ? (string)$_POST['password'] : '';

  if($username === '' || $password === ''){
    $error = 'Usuario y contraseña son obligatorios.';
  } else {
    $stmt = $pdo->prepare("SELECT id, username, password, secret_2fa FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if(!$user){
      $error = 'Usuario o contraseña incorrectos.';
    }else{
      $stored = $user['password'];

      // Soporta bcrypt ($2y$...) y (si existiera) texto plano legacy
      $ok = false;
      if (is_string($stored) && str_starts_with($stored, '$2y$')){
        $ok = password_verify($password, $stored);
      } else {
        // Comparación plana legacy (no recomendada, pero útil para datos viejos)
        $ok = hash_equals((string)$stored, $password);
      }

      if(!$ok){
        $error = 'Usuario o contraseña incorrectos.';
      }else{
        // Credenciales correctas
        $_SESSION['username'] = $user['username'];

        if(!empty($user['secret_2fa'])){
          // Requiere segundo factor
          $_SESSION['user_id_pending_2fa'] = (int)$user['id'];
          unset($_SESSION['user_id'], $_SESSION['2fa_ok']);
          header('Location: verify_2fa.php'); exit;
        }else{
          // Sin 2FA: login completo
          session_regenerate_id(true);
          $_SESSION['user_id'] = (int)$user['id'];
          $_SESSION['2fa_ok']  = true;
          header('Location: dashboard.php'); exit;
        }
      }
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><title>Login</title>
  <style>
    body{font-family:Arial;background:#f5f5f5}
    .card{width:380px;margin:60px auto;padding:24px;background:#fff;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.08)}
    input,button{width:100%;padding:10px;margin-top:10px;border-radius:6px;border:1px solid #ddd}
    button{background:#28a745;color:#fff;border:none;cursor:pointer}
    .error{color:#c0392b;margin-top:8px}
  </style>
</head>
<body>
  <div class="card">
    <h2>Iniciar sesión</h2>
    <?php if($error): ?><div class="error"><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
    <form method="post">
      <input name="username" placeholder="Usuario" required>
      <input name="password" type="password" placeholder="Contraseña" required>
      <button type="submit">Entrar</button>
    </form>
  </div>
</body>
</html>
