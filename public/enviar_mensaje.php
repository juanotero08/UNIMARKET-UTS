<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Debes iniciar sesión.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido.");
}

$emisor_id = $_SESSION['user_id'];
$receptor_id = $_POST['receptor_id'];
$producto_id = $_POST['producto_id'];
$mensaje = trim($_POST['mensaje']);

if (!$receptor_id || !$producto_id || !$mensaje) {
    die("Datos faltantes.");
}

try {
    $stmt = $pdo->prepare("INSERT INTO mensajes (emisor_id, receptor_id, producto_id, mensaje, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$emisor_id, $receptor_id, $producto_id, $mensaje]);
    
    // Redirigir de vuelta al chat
    header("Location: chat.php?producto_id=$producto_id&receptor_id=$receptor_id");
    exit;
} catch (PDOException $e) {
    die("Error al enviar mensaje: " . $e->getMessage());
}
?>