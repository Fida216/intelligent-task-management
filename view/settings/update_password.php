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

    // Récupérer les données du formulaire
    $user_id = $_SESSION['user_id'];
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Vérifier que tous les champs sont remplis
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        header("Location: settings.php?error=" . urlencode($translations[$language]['error_message']));
        exit;
    }

    // Vérifier si les nouveaux mots de passe correspondent
    if ($new_password !== $confirm_password) {
        header("Location: settings.php?error=" . urlencode($translations[$language]['error_message']));
        exit;
    }

    // Vérifier la longueur du nouveau mot de passe
    if (strlen($new_password) < 8) {
        header("Location: settings.php?error=" . urlencode($translations[$language]['error_message']));
        exit;
    }

    try {
        // Vérifier le mot de passe actuel
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($current_password, $user['password'])) {
            header("Location: settings.php?error=" . urlencode($translations[$language]['error_message']));
            exit;
        }

        // Hacher le nouveau mot de passe
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Mettre à jour le mot de passe
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);

        // Régénérer le token CSRF
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header("Location: settings.php?success=" . urlencode($translations[$language]['success_message']));
        exit;
    } catch (PDOException $e) {
        error_log("Erreur lors de la mise à jour du mot de passe : " . $e->getMessage());
        header("Location: settings.php?error=" . urlencode($translations[$language]['error_message']));
        exit;
    }
} else {
    header("Location: settings.php");
    exit;
}
?>