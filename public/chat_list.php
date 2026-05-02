<?php
if (!session_id()) {
    session_start();
}
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

$user_id = $_SESSION['user_id'];

// Obtener el nombre del usuario actual
$stmt_user = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user_actual = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Obtener todas las conversaciones del usuario de forma simple
$stmt = $pdo->prepare("
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
$stmt->execute([$user_id, $user_id, $user_id]);
$conversaciones_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Enriquecer los datos con nombres y últimos mensajes
$conversaciones = [];
foreach ($conversaciones_raw as $conv) {
    // Obtener nombre del otro usuario
    $stmt_name = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt_name->execute([$conv['otro_usuario_id']]);
    $otro_usuario = $stmt_name->fetch(PDO::FETCH_ASSOC);
    
    // Obtener último mensaje
    $stmt_msg = $pdo->prepare("
        SELECT mensaje FROM mensajes 
        WHERE (emisor_id = ? AND receptor_id = ?) OR (emisor_id = ? AND receptor_id = ?)
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt_msg->execute([$user_id, $conv['otro_usuario_id'], $conv['otro_usuario_id'], $user_id]);
    $ultimo_msg = $stmt_msg->fetch(PDO::FETCH_ASSOC);
    
    $conversaciones[] = [
        'otro_usuario_id' => $conv['otro_usuario_id'],
        'otro_usuario_nombre' => $otro_usuario['name'] ?? 'Usuario',
        'ultimo_mensaje_fecha' => $conv['ultimo_mensaje_fecha'],
        'ultimo_mensaje' => $ultimo_msg['mensaje'] ?? ''
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - UNI Market</title>
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
            background: #fff;
        }
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
        .add-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f0f0f0;
            border: none;
            cursor: pointer;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .add-btn:hover { background: #e0e0e0; }
        .search-box {
            position: relative;
        }
        .search-box input {
            width: 100%;
            padding: 10px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            font-size: 14px;
            outline: none;
        }
        .search-box input:focus {
            border-color: #075e54;
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
            transition: background 0.15s;
            text-decoration: none;
            color: inherit;
            margin: 0 8px;
            border-radius: 12px;
        }
        .conversation-item:hover {
            background: #f5f5f5;
        }
        .conversation-item.active {
            background: #e8f5e9;
        }
        .avatar-wrapper {
            position: relative;
            flex-shrink: 0;
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
        }
        .online-indicator {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 14px;
            height: 14px;
            background: #31a24c;
            border: 3px solid white;
            border-radius: 50%;
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
        .conv-type {
            font-size: 12px;
            color: #999;
            margin-bottom: 4px;
        }
        .conv-message {
            font-size: 13px;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .chat-empty {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="chat-wrapper">
        <div class="conversations-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">
                    <h2>Mis conversaciones</h2>
                    <button class="add-btn">+</button>
                </div>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Buscar conversaciones..." />
                </div>
            </div>
            <div class="conversations-container" id="conversationsContainer">
                <?php if (count($conversaciones) > 0): ?>
                    <?php foreach ($conversaciones as $index => $conv): ?>
                        <a href="/chat.php?receptor_id=<?php echo $conv['otro_usuario_id']; ?>&producto_id=0" class="conversation-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <div class="avatar-wrapper">
                                <div class="avatar">👤</div>
                                <div class="online-indicator"></div>
                            </div>
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
                                            } elseif ($diff->d < 7) {
                                                echo $diff->d . 'd';
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
                <?php else: ?>
                    <div class="chat-empty">
                        <div style="text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 16px;">💭</div>
                            <div>No tienes conversaciones</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="chat-empty" style="flex: 1;">
            <div style="text-align: center; color: #ccc;">
                <div style="font-size: 64px; margin-bottom: 16px;">💬</div>
                <div style="font-size: 18px;">Selecciona una conversación</div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const conversations = document.querySelectorAll('.conversation-item');
            conversations.forEach(conv => {
                const name = conv.querySelector('.conv-name').textContent.toLowerCase();
                if (name.includes(searchTerm)) {
                    conv.style.display = '';
                } else {
                    conv.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>