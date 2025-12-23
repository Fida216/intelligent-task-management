<?php
// Initialize or retrieve theme from cookie
$theme = $_COOKIE['theme'] ?? 'light';

// Function to get the current theme
function getTheme() {
    global $theme;
    return in_array($theme, ['light', 'dark']) ? $theme : 'light';
}

// Handle theme toggle via POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_theme'])) {
    $newTheme = getTheme() === 'light' ? 'dark' : 'light';
    setcookie('theme', $newTheme, time() + (365 * 24 * 60 * 60), '/'); // Persist for 1 year, apply to all paths
    $theme = $newTheme;
    // Redirect to the same page to avoid form resubmission
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}
?>