<?php
require '../../auth.php';
require '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: notifications.php?action=error');
    exit();
}

try {
    // Vérifier les champs obligatoires
    if (!isset($_POST['message']) || !isset($_POST['date_notification'])) {
        header('Location: notifications.php?action=error');
        exit();
    }

    $user_id = (int)$_SESSION['user_id'];
    $message = trim($_POST['message']);
    $date_notification = trim($_POST['date_notification']);

    // Valider le message
    if (empty($message) || strlen($message) > 255) {
        header('Location: notifications.php?action=error');
        exit();
    }

    // Valider la date (YYYY-MM-DD HH:MM:SS)
    if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date_notification)) {
        header('Location: notifications.php?action=error');
        exit();
    }

    // Vérifier si les notifications sont activées
    $req = $pdo->prepare("SELECT notification_enabled FROM users WHERE id = ?");
    $req->execute([$user_id]);
    $user = $req->fetch(PDO::FETCH_ASSOC);
    if (!$user || !$user['notification_enabled']) {
        header('Location: notifications.php?action=error');
        exit();
    }

    // Insérer la notification
    $req = $pdo->prepare("INSERT INTO notifications (user_id, message, date_notification, est_vue) VALUES (?, ?, ?, 0)");
    $resultat = $req->execute([$user_id, $message, $date_notification]);

    if ($resultat) {
        header('Location: notifications.php?action=success');
    } else {
        header('Location: notifications.php?action=error');
    }
} catch (PDOException $e) {
    error_log("Erreur lors de l'ajout de la notification : " . $e->getMessage());
    header('Location: notifications.php?action=error');
}
exit();
?>