<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Debes iniciar sesión para acceder al chat.");
}

$emisor_id = $_SESSION['user_id'];
$receptor_id = $_GET['receptor_id'] ?? $_POST['receptor_id'];
$producto_id = $_GET['producto_id'] ?? $_POST['producto_id'];

if (!$receptor_id || !$producto_id) {
    die("Parámetros faltantes.");
}

// Verificar si ya existe conversación (al menos un mensaje entre emisor y receptor para este producto)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM mensajes WHERE producto_id = ? AND ((emisor_id = ? AND receptor_id = ?) OR (emisor_id = ? AND receptor_id = ?))");
$stmt->execute([$producto_id, $emisor_id, $receptor_id, $receptor_id, $emisor_id]);
$existe_conversacion = $stmt->fetchColumn() > 0;

if (!$existe_conversacion) {
    // Insertar mensaje automático
    $mensaje_auto = "Hola, estoy interesado en tu publicación";
    $stmt = $pdo->prepare("INSERT INTO mensajes (emisor_id, receptor_id, producto_id, mensaje, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$emisor_id, $receptor_id, $producto_id, $mensaje_auto]);
}

// Obtener todos los mensajes entre emisor y receptor para este producto
$stmt = $pdo->prepare("SELECT * FROM mensajes WHERE producto_id = ? AND ((emisor_id = ? AND receptor_id = ?) OR (emisor_id = ? AND receptor_id = ?)) ORDER BY created_at ASC");
$stmt->execute([$producto_id, $emisor_id, $receptor_id, $receptor_id, $emisor_id]);
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat del Producto</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #e5ddd5; margin: 0; padding: 0; }
        .chat-container { max-width: 600px; margin: 20px auto; background-color: white; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .chat-header { background-color: #075e54; color: white; padding: 10px; border-radius: 10px 10px 0 0; text-align: center; }
        .messages { padding: 10px; height: 400px; overflow-y: auto; }
        .message { margin-bottom: 10px; padding: 8px 12px; border-radius: 18px; max-width: 70%; word-wrap: break-word; }
        .message.sent { background-color: #dcf8c6; margin-left: auto; text-align: right; }
        .message.received { background-color: white; margin-right: auto; }
        .message-form { padding: 10px; border-top: 1px solid #ddd; display: flex; }
        .message-form input[type="text"] { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 20px; }
        .message-form button { padding: 10px 20px; background-color: #25d366; color: white; border: none; border-radius: 20px; margin-left: 10px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h2>Chat del Producto</h2>
        </div>
        <div class="messages" id="messages">
            <?php foreach ($mensajes as $msg): ?>
                <div class="message <?php echo $msg['emisor_id'] == $emisor_id ? 'sent' : 'received'; ?>">
                    <?php echo htmlspecialchars($msg['mensaje']); ?>
                    <br><small><?php echo $msg['created_at']; ?></small>
                </div>
            <?php endforeach; ?>
        </div>
        <form class="message-form" action="enviar_mensaje.php" method="post">
            <input type="hidden" name="producto_id" value="<?php echo $producto_id; ?>">
            <input type="hidden" name="receptor_id" value="<?php echo $receptor_id; ?>">
            <input type="text" name="mensaje" placeholder="Escribe un mensaje..." required>
            <button type="submit">Enviar</button>
        </form>
    </div>
    <script>
        document.getElementById('messages').scrollTop = document.getElementById('messages').scrollHeight;
    </script>
</body>
</html>