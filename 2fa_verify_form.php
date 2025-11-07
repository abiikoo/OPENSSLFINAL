<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Verificación 2FA</title></head>
<body>
<h2>Verificación de código 2FA</h2>
<form method="post" action="2fa_verify.php">
  <input name="code" maxlength="6" pattern="\d{6}" placeholder="Código de 6 dígitos" required>
  <button>Verificar</button>
</form>
</body>
</html>
