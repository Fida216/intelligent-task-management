<?php
// Pas de session_start() ici, car la session est démarrée dans le fichier principal (profil.php)

// Initialiser le thème depuis la session ou le cookie
if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], ['light', 'dark']) ? $_COOKIE['theme'] : 'light';
}

// Gérer le basculement de thème via une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $newTheme = $_POST['theme'] === 'light' ? 'light' : 'dark';
    $_SESSION['theme'] = $newTheme;
    setcookie('theme', $newTheme, time() + (365 * 24 * 60 * 60), '/');
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Fonction pour obtenir le thème actuel
function getTheme() {
    return isset($_SESSION['theme']) && in_array($_SESSION['theme'], ['light', 'dark']) ? $_SESSION['theme'] : 'light';
}
?>