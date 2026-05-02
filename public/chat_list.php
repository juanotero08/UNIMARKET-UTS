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
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .container { 
            max-width: 500px; 
            margin: 0 auto; 
            height: 100vh; 
            display: flex; 
            flex-direction: column;
            background: white;
        }
        .header { 
            background: linear-gradient(135deg, #075e54 0%, #128c7e 100%);
            color: white; 
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-content h1 { font-size: 24px; margin-bottom: 5px; }
        .header-content p { font-size: 13px; opacity: 0.9; }
        .back-btn { 
            background: rgba(255,255,255,0.3); 
            border: none; 
            color: white; 
            font-size: 24px; 
            cursor: pointer;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }
        .back-btn:hover { background: rgba(255,255,255,0.5); }
        .conversations-list { 
            flex: 1;
            overflow-y: auto;
        }
        .conversation { 
            padding: 15px 16px;
            border-bottom: 1px solid #e0e0e0;
            display: flex; 
            align-items: center; 
            cursor: pointer; 
            text-decoration: none; 
            color: inherit;
            transition: background 0.15s;
        }
        .conversation:hover { 
            background-color: #f5f5f5;
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
            margin-right: 12px;
            flex-shrink: 0;
        }
        .conv-info { 
            flex: 1; 
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .conv-header { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 4px; 
            align-items: baseline;
        }
        .conv-name { 
            font-weight: 500; 
            color: #000; 
            font-size: 15px;
        }
        .conv-time { 
            font-size: 12px; 
            color: #999;
            margin-left: 8px;
        }
        .conv-message { 
            font-size: 13px; 
            color: #666; 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis;
        }
        .empty { 
            text-align: center; 
            padding: 60px 20px;
            color: #999;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .empty-icon { 
            font-size: 64px; 
            margin-bottom: 20px; 
            opacity: 0.5;
        }
        .empty-text { 
            font-size: 16px;
            margin-bottom: 10px;
        }
        .empty-subtext { 
            font-size: 13px;
            color: #bbb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1>💬 Mensajes</h1>
                <p><?php echo count($conversaciones); ?> conversación(es)</p>
            </div>
            <a href="/" class="back-btn">←</a>
        </div>

        <div class="conversations-list">
            <?php if (count($conversaciones) > 0): ?>
                <?php foreach ($conversaciones as $conv): ?>
                    <a href="/chat.php?receptor_id=<?php echo $conv['otro_usuario_id']; ?>&producto_id=0" class="conversation">
                        <div class="avatar">👤</div>
                        <div class="conv-info">
                            <div class="conv-header">
                                <span class="conv-name"><?php echo htmlspecialchars($conv['otro_usuario_nombre']); ?></span>
                                <span class="conv-time">
                                    <?php 
                                        $fecha = new DateTime($conv['ultimo_mensaje_fecha']);
                                        $ahora = new DateTime();
                                        $diff = $ahora->diff($fecha);
                                        
                                        if ($diff->d == 0) {
                                            echo $fecha->format('H:i');
                                        } elseif ($diff->d == 1) {
                                            echo 'Ayer';
                                        } elseif ($diff->d < 7) {
                                            echo $diff->d . 'd';
                                        } else {
                                            echo $fecha->format('d/m/y');
                                        }
                                    ?>
                                </span>
                            </div>
                            <div class="conv-message"><?php echo htmlspecialchars(substr($conv['ultimo_mensaje'], 0, 50)); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty">
                    <div class="empty-icon">💭</div>
                    <div class="empty-text">No tienes conversaciones aún</div>
                    <div class="empty-subtext">Escribe en un producto para comenzar a chatear</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>