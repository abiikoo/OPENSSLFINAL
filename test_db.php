<?php
require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("SELECT NOW() AS fecha");
    $row = $stmt->fetch();
    echo "Conexión exitosa a la base de datos.<br>";
    echo "Hora del servidor MySQL: " . $row['fecha'];
} catch (Exception $e) {
    echo "Error al conectar a la base de datos: " . $e->getMessage();
}
