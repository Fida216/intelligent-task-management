<?php
require 'auth.php';
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: settings.php');
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Invalid CSRF token";
    header('Location: settings.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name']);
$surname = trim($_POST['surname']);
$email = trim($_POST['email']);

try {
    $stmt = $conn->prepare("UPDATE users SET name = ?, surname = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $surname, $email, $user_id]);

    // Update session data
    $_SESSION['userName'] = $name . ' ' . $surname;
    $_SESSION['userInitials'] = strtoupper(substr($name, 0, 1) . substr($surname, 0, 1));

    // Regenerate CSRF token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header('Location: settings.php?success=Profile updated successfully');
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = "Error updating profile: " . $e->getMessage();
    header('Location: settings.php');
    exit;
}
?>