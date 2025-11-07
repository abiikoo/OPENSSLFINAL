<?php
session_start();
require_once __DIR__.'/../../db.php';

if (empty($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }

if (isset($_POST['disable_2fa'])) {
  $pdo->prepare("UPDATE usuarios SET twofa_enabled=0, secret_2fa=NULL WHERE id=:id")
      ->execute([':id'=>$_SESSION['user_id']]);
  $_SESSION['2fa_passed'] = false;
  header('Location: 2fa_setup.php?off=1'); exit;
}
