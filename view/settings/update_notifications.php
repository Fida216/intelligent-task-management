<?php
session_start();
require '../../config/db.php';
include '../../controller/translations.php';

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: settings.php?error=" . urlencode($translations[$language]['error_message']));
        exit;
    }

    // Récupérer l'ID de l'utilisateur
    $user_id = $_SESSION['user_id'];
    // Déterminer si les notifications sont activées
    $notifications_enabled = isset($_POST['notifications']) && $_POST['notifications'] == '1' ? 1 : 0;

    try {
        // Mettre à jour les notifications dans la base de données
        $stmt = $pdo->prepare("UPDATE users SET notifications_enabled = ? WHERE id = ?");
        $stmt->execute([$notifications_enabled, $user_id]);

        // Régénérer le token CSRF
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header("Location: settings.php?success=" . urlencode($translations[$language]['success_message']));
        exit;
    } catch (PDOException $e) {
        error_log("Erreur lors de la mise à jour des notifications : " . $e->getMessage());
        header("Location: settings.php?error=" . urlencode($translations[$language]['error_message']));
        exit;
    }
} else {
    header("Location: settings.php");
    exit;
}
?>