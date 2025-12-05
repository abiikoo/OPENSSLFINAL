<?php
//Ejemplo::::
// save as create_user.php
$pdo = new PDO('mysql:host=localhost;dbname=company_info', 'root', '');
$username = 'Juan';
$password = password_hash('Juan0723', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->execute([$username, $password]);
echo "Usuario creado.";
?>
