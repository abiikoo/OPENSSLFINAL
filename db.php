<?php
$env = [];
if (file_exists(__DIR__.'/.env')) {
  foreach (file(__DIR__.'/.env', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
    if ($line === '' || $line[0]==='#' || strpos($line,'=')===false) continue;
    [$k,$v] = array_map('trim', explode('=', $line, 2));
    $env[$k]=$v;
  }
}

$dsn  = "mysql:host=".($env['DB_HOST']??'localhost').";dbname=".($env['DB_NAME']??'authlab').";charset=utf8mb4";
$user = $env['DB_USER'] ?? 'app_user';
$pass = $env['DB_PASS'] ?? '';

try {
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);
} catch(Throwable $e){
  http_response_code(500);
  die("Error BD: ".$e->getMessage());
}
