<?php
// NO pongas nada antes de este <?php (ni espacios).
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

if (empty($_SESSION['user_id']) || empty($_SESSION['2fa_ok'])) {
  header('Location: login.php');
  exit;
}

// No cierres el tag PHP para evitar espacios/headers enviados.
