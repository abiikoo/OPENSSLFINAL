<?php
// db.php: Configuración de conexión a la base de datos

try {
    // Crear la conexión PDO con MySQL
    $pdo = new PDO('mysql:host=localhost;dbname=company_info', 'root', '');
    
    // Configuración para manejo de errores
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Si hay un error de conexión, muestra un mensaje
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>
