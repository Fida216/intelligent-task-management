<?php
// auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if the current page is login-signup.php
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page !== 'login-signup.php' && !isset($_SESSION['user_id'])) {
    header('Location: login-signup.php');
    exit;
}
?>