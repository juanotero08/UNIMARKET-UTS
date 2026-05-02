<?php
if (!session_id()) {
    session_start();
}
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /chat_list.php');
    exit;
}

$emisor_id = $_SESSION['user_id'];
$receptor_id = $_POST['receptor_id'] ?? null;
$producto_id = $_POST['producto_id'] ?? null;
$mensaje = trim($_POST['mensaje'] ?? '');

if (!$receptor_id || !$mensaje) {
    header('Location: /chat_list.php');
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO mensajes (emisor_id, receptor_id, producto_id, mensaje, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$emisor_id, $receptor_id, $producto_id ?: null, $mensaje]);
    
    // Redirigir de vuelta al chat
    $product_param = ($producto_id && $producto_id != 0) ? $producto_id : '0';
    header("Location: /chat.php?producto_id=$product_param&receptor_id=$receptor_id");
    exit;
} catch (PDOException $e) {
    header('Location: /chat_list.php');
    exit;
}
?>