<?php
session_start();

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gérer le thème
    $theme = isset($_POST['theme']) && $_POST['theme'] === 'dark' ? 'dark' : 'light';
    
    // Gérer la langue
    $language = isset($_POST['language']) && in_array($_POST['language'], ['en', 'fr', 'ar']) ? $_POST['language'] : 'en';

    // Mettre à jour la session
    $_SESSION['theme'] = $theme;
    $_SESSION['language'] = $language;

    // Stocker les préférences dans des cookies (valables 30 jours)
    setcookie('theme', $theme, time() + (86400 * 30), "/");
    setcookie('language', $language, time() + (86400 * 30), "/");

    // Rediriger vers settings.php
    header("Location: settings.php?success=" . urlencode($translations[$language]['success_message']));
    exit;
} else {
    header("Location: settings.php");
    exit;
}
?>