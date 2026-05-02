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

// Obtener nombre del receptor
$stmt_receptor = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt_receptor->execute([$receptor_id]);
$usuario_receptor = $stmt_receptor->fetch(PDO::FETCH_ASSOC);

// Obtener todas las conversaciones para la lista lateral
$stmt_convs = $pdo->prepare("
    SELECT 
        CASE 
            WHEN emisor_id = ? THEN receptor_id 
            ELSE emisor_id 
        END as otro_usuario_id,
        MAX(created_at) as ultimo_mensaje_fecha
    FROM mensajes 
    WHERE emisor_id = ? OR receptor_id = ?
    GROUP BY otro_usuario_id
    ORDER BY ultimo_mensaje_fecha DESC
");
$stmt_convs->execute([$emisor_id, $emisor_id, $emisor_id]);
$conversaciones_raw = $stmt_convs->fetchAll(PDO::FETCH_ASSOC);

$conversaciones = [];
foreach ($conversaciones_raw as $conv) {
    $stmt_name = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt_name->execute([$conv['otro_usuario_id']]);
    $otro_usuario = $stmt_name->fetch(PDO::FETCH_ASSOC);
    
    $stmt_msg = $pdo->prepare("
        SELECT mensaje FROM mensajes 
        WHERE (emisor_id = ? AND receptor_id = ?) OR (emisor_id = ? AND receptor_id = ?)
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt_msg->execute([$emisor_id, $conv['otro_usuario_id'], $conv['otro_usuario_id'], $emisor_id]);
    $ultimo_msg = $stmt_msg->fetch(PDO::FETCH_ASSOC);
    
    $conversaciones[] = [
        'otro_usuario_id' => $conv['otro_usuario_id'],
        'otro_usuario_nombre' => $otro_usuario['name'] ?? 'Usuario',
        'ultimo_mensaje_fecha' => $conv['ultimo_mensaje_fecha'],
        'ultimo_mensaje' => $ultimo_msg['mensaje'] ?? ''
    ];
}

// Si hay producto_id y es diferente de 0, es un chat de producto específico
$producto = null;
if ($producto_id && $producto_id != 0) {
    $stmt_prod = $pdo->prepare("SELECT p.*, u.name as vendor_name FROM productos p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
    $stmt_prod->execute([$producto_id]);
    $producto = $stmt_prod->fetch(PDO::FETCH_ASSOC);
    
    // Verificar si ya existe conversación
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM mensajes WHERE producto_id = ? AND ((emisor_id = ? AND receptor_id = ?) OR (emisor_id = ? AND receptor_id = ?))");
    $stmt->execute([$producto_id, $emisor_id, $receptor_id, $receptor_id, $emisor_id]);
    $existe_conversacion = $stmt->fetchColumn() > 0;

    if (!$existe_conversacion) {
        $mensaje_auto = "Hola, estoy interesado en tu publicación";
        $stmt = $pdo->prepare("INSERT INTO mensajes (emisor_id, receptor_id, producto_id, mensaje, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$emisor_id, $receptor_id, $producto_id, $mensaje_auto]);
    }

    // Obtener todos los mensajes para este producto
    $stmt = $pdo->prepare("SELECT * FROM mensajes WHERE producto_id = ? AND ((emisor_id = ? AND receptor_id = ?) OR (emisor_id = ? AND receptor_id = ?)) ORDER BY created_at ASC");
    $stmt->execute([$producto_id, $emisor_id, $receptor_id, $receptor_id, $emisor_id]);
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
    <title><?php echo htmlspecialchars($usuario_receptor['name'] ?? 'Chat'); ?> - UNI Market</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { 
            height: 100%; 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            background: #fff;
        }
        .chat-wrapper {
            display: flex;
            height: 100vh;
        }
        /* SIDEBAR CONVERSACIONES */
        .conversations-sidebar {
            width: 320px;
            background: #fff;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .sidebar-header {
            padding: 16px;
            border-bottom: 1px solid #e0e0e0;
        }
        .sidebar-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .sidebar-title h2 {
            font-size: 32px;
            font-weight: 700;
        }
        .conversations-container {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .conversation-item {
            padding: 8px 8px;
            display: flex;
            gap: 12px;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            margin: 0 8px;
            border-radius: 12px;
            transition: background 0.15s;
        }
        .conversation-item:hover {
            background: #f5f5f5;
        }
        .conversation-item.active {
            background: #e8f5e9;
        }
        .avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #075e54 0%, #128c7e 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        .conv-content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .conv-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .conv-name {
            font-weight: 500;
            font-size: 15px;
            color: #000;
        }
        .conv-time {
            font-size: 13px;
            color: #999;
        }
        .conv-message {
            font-size: 13px;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        /* MAIN CHAT AREA */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
        }
        .chat-header {
            background: linear-gradient(135deg, #075e54 0%, #128c7e 100%);
            color: white;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }
        .chat-header-content {
            flex: 1;
        }
        .chat-header-content h2 {
            font-size: 16px;
            font-weight: 500;
            margin: 0;
        }
        .chat-header-content p {
            font-size: 12px;
            opacity: 0.9;
            margin: 2px 0 0 0;
        }
        .header-menu {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }
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
            font-size: 11px;
            color: #999;
            margin-top: 2px;
            text-align: right;
        }
        .message.sent .message-time {
            text-align: right;
            padding-right: 12px;
        }
        .message.received .message-time {
            text-align: left;
            padding-left: 12px;
        }
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
            padding: 8px 12px;
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
        /* PRODUCT DETAILS PANEL */
        .product-panel {
            width: 300px;
            background: #fff;
            border-left: 1px solid #e0e0e0;
            overflow-y: auto;
            padding: 20px;
        }
        .product-image {
            width: 100%;
            height: 200px;
            background: #f0f0f0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .product-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .product-price {
            font-size: 24px;
            font-weight: 700;
            color: #075e54;
            margin-bottom: 16px;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            background: #075e54;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .btn-primary:hover {
            background: #054436;
        }
        .vendor-info {
            border-top: 1px solid #e0e0e0;
            padding-top: 16px;
            margin-top: 16px;
        }
        .vendor-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #075e54 0%, #128c7e 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .vendor-name {
            font-weight: 600;
            font-size: 14px;
        }
        .vendor-link {
            color: #075e54;
            text-decoration: none;
            font-size: 12px;
        }
        .vendor-stats {
            font-size: 13px;
            color: #666;
            margin-top: 8px;
        }
        .stat-row {
            display: flex;
            align-items: center;
            margin-bottom: 6px;
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
    <div class="chat-wrapper">
        <!-- SIDEBAR -->
        <div class="conversations-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">
                    <h2>Mis conversaciones</h2>
                </div>
            </div>
            <div class="conversations-container">
                <?php foreach ($conversaciones as $conv): ?>
                    <a href="/chat.php?receptor_id=<?php echo $conv['otro_usuario_id']; ?>&producto_id=<?php echo $producto_id ?: 0; ?>" 
                       class="conversation-item <?php echo $conv['otro_usuario_id'] == $receptor_id ? 'active' : ''; ?>">
                        <div class="avatar">👤</div>
                        <div class="conv-content">
                            <div class="conv-header">
                                <span class="conv-name"><?php echo htmlspecialchars($conv['otro_usuario_nombre']); ?></span>
                                <span class="conv-time">
                                    <?php 
                                        $fecha = new DateTime($conv['ultimo_mensaje_fecha']);
                                        $ahora = new DateTime();
                                        $diff = $ahora->diff($fecha);
                                        
                                        if ($diff->h < 1 && $diff->d == 0) {
                                            echo $diff->i . 'm';
                                        } elseif ($diff->d == 0) {
                                            echo $fecha->format('H:i');
                                        } elseif ($diff->d == 1) {
                                            echo 'Ayer';
                                        } else {
                                            echo $fecha->format('d/m');
                                        }
                                    ?>
                                </span>
                            </div>
                            <div class="conv-message"><?php echo htmlspecialchars(substr($conv['ultimo_mensaje'], 0, 50)); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- MAIN CHAT -->
        <div class="chat-main">
            <div class="chat-header">
                <div class="chat-header-content">
                    <h2><?php echo htmlspecialchars($usuario_receptor['name'] ?? 'Chat'); ?></h2>
                    <p>● Activo ahora</p>
                </div>
                <button class="header-menu">⋮</button>
            </div>
            <div class="messages" id="messages">
                <?php if (count($mensajes) == 0): ?>
                    <div class="empty-state">
                        <div style="text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 16px;">💬</div>
                            <div>Inicia la conversación</div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($mensajes as $msg): ?>
                        <div class="message <?php echo $msg['emisor_id'] == $emisor_id ? 'sent' : 'received'; ?>">
                            <div>
                                <div class="message-content"><?php echo htmlspecialchars($msg['mensaje']); ?></div>
                                <div class="message-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <form class="message-form" action="enviar_mensaje.php" method="post" id="messageForm">
                <input type="hidden" name="receptor_id" value="<?php echo $receptor_id; ?>">
                <input type="hidden" name="producto_id" value="<?php echo $producto_id ?: 0; ?>">
                <input type="text" name="mensaje" id="messageInput" placeholder="Escribe un mensaje..." required autofocus>
                <button type="submit">➤</button>
            </form>
        </div>

        <!-- PRODUCT PANEL -->
        <?php if ($producto): ?>
        <div class="product-panel">
            <div class="product-image">
                <img src="<?php 
                    if ($producto['imagen']) {
                        echo '/storage/' . htmlspecialchars($producto['imagen']);
                    } else {
                        $color = $producto['tipo'] === 'servicio' ? '4CAF50' : '2E7D32';
                        echo 'https://via.placeholder.com/300x200/' . $color . '/ffffff?text=' . urlencode(substr($producto['nombre'], 0, 20));
                    }
                ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
            </div>
            <h3 class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
            <div class="product-price">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></div>
            <button class="btn-primary">Ver publicación</button>
            
            <div class="vendor-info">
                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 12px;">
                    <div class="vendor-avatar">👤</div>
                    <div>
                        <div class="vendor-name"><?php echo htmlspecialchars($producto['vendor_name']); ?></div>
                        <a href="#" class="vendor-link">Ver perfil</a>
                    </div>
                </div>
                <div class="vendor-stats">
                    <div class="stat-row">
                        <span>📦 Publicaciones: 5</span>
                    </div>
                    <div class="stat-row">
                        <span>⭐ Calificación: 4.8 (24)</span>
                    </div>
                    <div class="stat-row">
                        <span>✓ Usuario verificado</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        const messagesDiv = document.getElementById('messages');
        const messageForm = document.getElementById('messageForm');
        
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
        
        messageForm.addEventListener('submit', function() {
            setTimeout(() => {
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }, 100);
        });
    </script>
</body>
</html>