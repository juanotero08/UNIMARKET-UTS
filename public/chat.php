<?php
if (!session_id()) {
    session_start();
}
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

$emisor_id = $_SESSION['user_id'];
$receptor_id = $_GET['receptor_id'] ?? $_POST['receptor_id'];
$producto_id = $_GET['producto_id'] ?? $_POST['producto_id'];

if (!$receptor_id) {
    header('Location: /chat_list.php');
    exit;
}

$titulo_chat = "Chat General";

// Obtener nombre del receptor
$stmt_receptor = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt_receptor->execute([$receptor_id]);
$usuario_receptor = $stmt_receptor->fetch(PDO::FETCH_ASSOC);

// Si hay producto_id y es diferente de 0, es un chat de producto específico
if ($producto_id && $producto_id != 0) {
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
    $titulo_chat = "Chat del Producto";
} else {
    // Chat general sin producto específico
    $producto_id = null;
    $stmt = $pdo->prepare("SELECT * FROM mensajes WHERE (producto_id = 0 OR producto_id IS NULL) AND ((emisor_id = ? AND receptor_id = ?) OR (emisor_id = ? AND receptor_id = ?)) ORDER BY created_at ASC");
    $stmt->execute([$emisor_id, $receptor_id, $receptor_id, $emisor_id]);
}

$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($usuario_receptor['name'] ?? 'Chat'); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { 
            height: 100%; 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            background: #fff;
        }
        .chat-container { 
            max-width: 500px; 
            margin: 0 auto; 
            height: 100vh;
            display: flex; 
            flex-direction: column;
            background-color: white;
        }
        .chat-header { 
            background: linear-gradient(135deg, #075e54 0%, #128c7e 100%);
            color: white; 
            padding: 16px;
            display: flex;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }
        .back-btn { 
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            margin-right: 16px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .back-btn:hover { opacity: 0.8; }
        .chat-header-content { flex: 1; }
        .chat-header-content h2 { font-size: 16px; font-weight: 500; margin: 0; }
        .chat-header-content p { font-size: 12px; opacity: 0.9; margin: 2px 0 0 0; }
        .messages { 
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            background: linear-gradient(180deg, #f5f7fa 0%, #e5ddd5 100%);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .message { 
            display: flex;
            margin-bottom: 4px;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message-content {
            padding: 8px 12px;
            border-radius: 18px;
            max-width: 70%;
            word-wrap: break-word;
            line-height: 1.4;
        }
        .message.sent {
            justify-content: flex-end;
        }
        .message.sent .message-content {
            background-color: #dcf8c6;
            color: #000;
        }
        .message.received {
            justify-content: flex-start;
        }
        .message.received .message-content {
            background-color: white;
            color: #000;
            box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
        }
        .message-time {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
            text-align: right;
        }
        .message.sent .message-time { text-align: right; padding-right: 12px; }
        .message.received .message-time { text-align: left; padding-left: 12px; }
        .message-form { 
            padding: 12px 16px;
            border-top: 1px solid #e0e0e0;
            background: white;
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }
        .message-form input[type="text"] { 
            flex: 1; 
            padding: 10px 16px; 
            border: 1px solid #ddd; 
            border-radius: 20px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s;
        }
        .message-form input[type="text"]:focus {
            border-color: #075e54;
        }
        .message-form button { 
            padding: 10px 16px; 
            background-color: #075e54;
            color: white; 
            border: none; 
            border-radius: 50%; 
            width: 36px;
            height: 36px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: background-color 0.2s;
        }
        .message-form button:hover {
            background-color: #054436;
        }
        .empty-state {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <a href="/chat_list.php" class="back-btn">←</a>
            <div class="chat-header-content">
                <h2><?php echo htmlspecialchars($usuario_receptor['name'] ?? 'Chat'); ?></h2>
                <p>En línea</p>
            </div>
        </div>
        <div class="messages" id="messages">
            <?php if (count($mensajes) == 0): ?>
                <div class="empty-state">
                    <div style="text-align: center;">
                        <div style="font-size: 48px; margin-bottom: 16px;">💬</div>
                        <div>Inicia la conversación con un mensaje</div>
                    </div>
                </div>
            <?php endif; ?>
            <?php foreach ($mensajes as $msg): ?>
                <div class="message <?php echo $msg['emisor_id'] == $emisor_id ? 'sent' : 'received'; ?>">
                    <div>
                        <div class="message-content"><?php echo htmlspecialchars($msg['mensaje']); ?></div>
                        <div class="message-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <form class="message-form" action="enviar_mensaje.php" method="post" id="messageForm">
            <input type="hidden" name="receptor_id" value="<?php echo $receptor_id; ?>">
            <?php if ($producto_id): ?>
                <input type="hidden" name="producto_id" value="<?php echo $producto_id; ?>">
            <?php else: ?>
                <input type="hidden" name="producto_id" value="0">
            <?php endif; ?>
            <input type="text" name="mensaje" id="messageInput" placeholder="Escribe un mensaje..." required autofocus>
            <button type="submit">➤</button>
        </form>
    </div>
    <script>
        const messagesDiv = document.getElementById('messages');
        const messageForm = document.getElementById('messageForm');
        const messageInput = document.getElementById('messageInput');
        
        // Auto scroll al final
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
        
        // Hacer scroll al enviar mensaje
        messageForm.addEventListener('submit', function() {
            setTimeout(() => {
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }, 100);
        });
    </script>
</body>
</html>