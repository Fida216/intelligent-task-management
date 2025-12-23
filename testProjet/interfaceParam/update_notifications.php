<?php
// Inclure le fichier de configuration
include 'config.php';

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']) {
        header("Location: settings.php?error=Erreur : Token invalide");
        exit;
    }

    // Récupérer l'ID de l'utilisateur
    $user_id = $_SESSION['user_id'];
    // Déterminer si les notifications sont activées (1 = oui, 0 = non)
    $notifications_enabled = isset($_POST['notifications']) && $_POST['notifications'] == '1' ? 1 : 0;

    try {
        // Mettre à jour les notifications dans la base de données
        $stmt = $conn->prepare("UPDATE users SET notifications_enabled = ? WHERE id = ?");
        $stmt->execute([$notifications_enabled, $user_id]);

        // Créer un nouveau token CSRF
        $_SESSION['csrf_token'] = time();
        header("Location: settings.php?success=Préférences de notification mises à jour");
        exit;
    } catch (Exception $e) {
        header("Location: settings.php?error=Erreur : Impossible de mettre à jour les notifications");
        exit;
    }
} else {
    header("Location: settings.php");
    exit;
}
?>