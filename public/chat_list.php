<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Debes iniciar sesión para acceder a tus mensajes.");
}

$user_id = $_SESSION['user_id'];

// Obtener todas las conversaciones del usuario (emisor o receptor)
$stmt = $pdo->prepare("
    SELECT DISTINCT 
        CASE 
            WHEN emisor_id = ? THEN receptor_id 
            ELSE emisor_id 
        END as otro_usuario_id,
        (SELECT nombre FROM users WHERE id = CASE WHEN emisor_id = ? THEN receptor_id ELSE emisor_id END) as otro_usuario_nombre,
        (SELECT MAX(created_at) FROM mensajes m2 WHERE (m2.emisor_id = ? AND m2.receptor_id = CASE WHEN emisor_id = ? THEN receptor_id ELSE emisor_id END) OR (m2.receptor_id = ? AND m2.emisor_id = CASE WHEN emisor_id = ? THEN receptor_id ELSE emisor_id END)) as ultimo_mensaje_fecha,
        (SELECT mensaje FROM mensajes m3 WHERE (m3.emisor_id = ? AND m3.receptor_id = CASE WHEN emisor_id = ? THEN receptor_id ELSE emisor_id END) OR (m3.receptor_id = ? AND m3.emisor_id = CASE WHEN emisor_id = ? THEN receptor_id ELSE emisor_id END) ORDER BY m3.created_at DESC LIMIT 1) as ultimo_mensaje,
        COUNT(CASE WHEN receptor_id = ? AND emisor_id = CASE WHEN emisor_id = ? THEN receptor_id ELSE emisor_id END THEN 1 END) as no_leidos
    FROM mensajes 
    WHERE emisor_id = ? OR receptor_id = ?
    GROUP BY otro_usuario_id
    ORDER BY ultimo_mensaje_fecha DESC
");
$stmt->execute([
    $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, 
    $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, 
    $user_id, $user_id
]);
$conversaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Mensajes - UNI Market</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f0f0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #075e54 0%, #128c7e 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header p { font-size: 14px; opacity: 0.9; }
        .conversation { 
            background: white; padding: 15px; border-bottom: 1px solid #eee; 
            display: flex; align-items: center; cursor: pointer; transition: background 0.2s;
            text-decoration: none; color: inherit;
        }
        .conversation:hover { background-color: #f9f9f9; }
        .avatar { 
            width: 50px; height: 50px; border-radius: 50%; 
            background: linear-gradient(135deg, #075e54 0%, #128c7e 100%); 
            color: white; display: flex; align-items: center; 
            justify-content: center; font-size: 20px; margin-right: 15px; 
            flex-shrink: 0;
        }
        .conv-info { flex: 1; min-width: 0; }
        .conv-header { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .conv-name { font-weight: 600; color: #000; }
        .conv-time { font-size: 12px; color: #999; }
        .conv-message { font-size: 13px; color: #666; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .empty { text-align: center; padding: 40px 20px; color: #999; }
        .empty-icon { font-size: 48px; margin-bottom: 15px; }
        .back-btn { display: inline-block; margin-bottom: 20px; padding: 10px 15px; background: #075e54; color: white; border-radius: 5px; text-decoration: none; }
        .back-btn:hover { background: #054436; }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-btn">← Volver</a>
        
        <div class="header">
            <h1>💬 Mis Mensajes</h1>
            <p><?php echo count($conversaciones); ?> conversación(es)</p>
        </div>

        <?php if (count($conversaciones) > 0): ?>
            <?php foreach ($conversaciones as $conv): ?>
                <a href="/chat.php?receptor_id=<?php echo $conv['otro_usuario_id']; ?>&producto_id=0" class="conversation">
                    <div class="avatar">👤</div>
                    <div class="conv-info">
                        <div class="conv-header">
                            <span class="conv-name"><?php echo htmlspecialchars($conv['otro_usuario_nombre']); ?></span>
                            <span class="conv-time"><?php echo date('H:i', strtotime($conv['ultimo_mensaje_fecha'])); ?></span>
                        </div>
                        <div class="conv-message"><?php echo htmlspecialchars(substr($conv['ultimo_mensaje'], 0, 50)); ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="header" style="border-radius: 10px;">
                <div class="empty-icon">💭</div>
                <div class="empty">No tienes conversaciones aún</div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>