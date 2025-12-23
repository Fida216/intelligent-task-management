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

    // Récupérer les données du formulaire
    $user_id = $_SESSION['user_id'];
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Vérifier que tous les champs sont remplis
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        header("Location: settings.php?error=Erreur : Tous les champs de mot de passe sont requis");
        exit;
    }

    // Vérifier si les nouveaux mots de passe correspondent
    if ($new_password !== $confirm_password) {
        header("Location: settings.php?error=Erreur : Les nouveaux mots de passe ne correspondent pas");
        exit;
    }

    // Vérifier la longueur du nouveau mot de passe
    if (strlen($new_password) < 8) {
        header("Location: settings.php?error=Erreur : Le nouveau mot de passe doit avoir au moins 8 caractères");
        exit;
    }

    try {
        // Vérifier le mot de passe actuel
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $current_password !== $user['password']) {
            header("Location: settings.php?error=Erreur : Mot de passe actuel incorrect");
            exit;
        }

        // Mettre à jour le mot de passe
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_password, $user_id]);

        // Créer un nouveau token CSRF
        $_SESSION['csrf_token'] = time();
        header("Location: settings.php?success=Mot de passe mis à jour avec succès");
        exit;
    } catch (Exception $e) {
        header("Location: settings.php?error=Erreur : Impossible de mettre à jour le mot de passe");
        exit;
    }
} else {
    header("Location: settings.php");
    exit;
}
?>