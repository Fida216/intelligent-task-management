<?php
require '../../auth.php';
require '../../config/db.php';

header('Content-Type: text/html; charset=UTF-8');

$user_id = (int)$_SESSION['user_id'];

// Vérifier si les notifications sont activées
$req = $pdo->prepare("SELECT notification_enabled FROM users WHERE id = ?");
$req->execute([$user_id]);
$user = $req->fetch(PDO::FETCH_ASSOC);

if (!$user || !$user['notification_enabled']) {
    echo '';
    exit();
}

try {
    // Récupérer les notifications non vues
    $req = $pdo->prepare("SELECT * FROM notifications WHERE date_notification <= NOW() AND est_vue = 0 AND user_id = ? ORDER BY date_notification DESC LIMIT 10");
    $req->execute([$user_id]);
    $notifs = $req->fetchAll(PDO::FETCH_ASSOC);

    foreach ($notifs as $notif) {
        echo "
        <div class='notif-box' style='background: linear-gradient(135deg, #8a4fff, #5c1de6); color: white; padding: 15px; border-radius: 10px; margin-bottom: 10px;'>
            <div class='notif-message' style='display: flex; align-items: center; gap: 10px; cursor: pointer;'>
                🔔 " . htmlspecialchars($notif['message']) . "
            </div>
            <div class='notif-ignore' style='cursor: pointer; font-weight: bold;'>×</div>
        </div>";

        // Marquer comme vue
        $pdo->prepare("UPDATE notifications SET est_vue = 1 WHERE id = ? AND user_id = ?")
            ->execute([$notif['id'], $user_id]);
    }
} catch (PDOException $e) {
    error_log("Erreur lors du chargement des notifications : " . $e->getMessage());
    echo '';
}
exit();
?>