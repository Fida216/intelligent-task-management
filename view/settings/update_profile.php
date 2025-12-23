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
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $email = trim($_POST['email']);

    // Vérifier que tous les champs sont remplis
    if (empty($name) || empty($surname) || empty($email)) {
        header("Location: settings.php?error=" . urlencode($translations[$language]['error_message']));
        exit;
    }

    // Vérifier la longueur des champs
    if (strlen($name) > 50 || strlen($surname) > 50 || strlen($email) > 100) {
        header("Location: settings.php?error=" . urlencode($translations[$language]['error_message']));
        exit;
    }

    // Vérifier le format de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: settings.php?error=" . urlencode($translations[$language]['error_message']));
        exit;
    }

    try {
        // Vérifier si l'email est déjà utilisé
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            header("Location: settings.php?error=" . urlencode($translations[$language]['error_message']));
            exit;
        }

        // Mettre à jour les informations dans la base de données
        $stmt = $pdo->prepare("UPDATE users SET name = ?, surname = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $surname, $email, $user_id]);

        // Mettre à jour la session
        $_SESSION['name'] = $name;
        $_SESSION['surname'] = $surname;
        $_SESSION['email'] = $email;

        // Régénérer le token CSRF
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header("Location: settings.php?success=" . urlencode($translations[$language]['success_message']));
        exit;
    } catch (PDOException $e) {
        error_log("Erreur lors de la mise à jour du profil : " . $e->getMessage());
        header("Location: settings.php?error=" . urlencode($translations[$language]['error_message']));
        exit;
    }
} else {
    header("Location: settings.php");
    exit;
}
?>