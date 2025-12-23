<?php
$pdo = new PDO("mysql:host=localhost;dbname=notification_test", "root", "");
$user_id = 1; // Replace with actual user ID from session or authentication
$req = $pdo->prepare("SELECT notification_enabled FROM users WHERE id = ?");
$req->execute([$user_id]);
$user = $req->fetch();


$req = $pdo->prepare("SELECT * FROM notifications WHERE date_notification <= NOW() AND est_vue = 0 AND user_id = ? ORDER BY date_notification DESC");
$req->execute([$user_id]);
$notifs = $req->fetchAll();

foreach ($notifs as $notif) {
     echo "
        <div class='notif-box' style='background: linear-gradient(135deg, #8a4fff, #5c1de6); color: white; padding: 15px; border-radius: 10px; margin-bottom: 10px;'>
            <div class='notif-message' style='display: flex; align-items: center; gap: 10px;'>
                🔔 " . htmlspecialchars($notif['message']) . "
            </div>
            <div class='notif-ignore' style='cursor: pointer; font-weight: bold;'>×</div>
        </div>";
        $pdo->prepare("UPDATE notifications SET est_vue = 1 WHERE id = ?")->execute([$notif['id']]);
    }

?>
