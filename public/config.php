<?php
// Preferir variables de entorno (si se ejecuta dentro de Laravel o con env configurado)
$host = getenv('DB_HOST') ?: '127.0.0.1';
$dbname = getenv('DB_DATABASE') ?: 'marketplace';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : 'Smichell11';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>