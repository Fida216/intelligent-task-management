<?php
// Inclure le fichier de configuration
include 'config.php';

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gérer le thème
    $theme = isset($_POST['theme']) && $_POST['theme'] === 'dark' ? 'dark' : 'light';
    
    // Gérer la langue
    $language = isset($_POST['language']) && in_array($_POST['language'], ['en', 'fr', 'ar']) ? $_POST['language'] : 'en';

    // Stocker les préférences dans des cookies (valables 30 jours)
    setcookie('theme', $theme, time() + (86400 * 30), "/");
    setcookie('language', $language, time() + (86400 * 30), "/");

    // Rediriger vers settings.php
    header("Location: settings.php");
    exit;
} else {
    // Si accès direct, rediriger vers settings.php
    header("Location: settings.php");
    exit;
}
?>