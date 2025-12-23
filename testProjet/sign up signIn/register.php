<?php
require 'auth.php';
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: sign.php');
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Invalid CSRF token";
    header('Location: sign.php');
    exit;
}

// Handle signup
if (isset($_POST['signup-form']) && $_POST['signup-form'] == '1') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required";
        header('Location: sign.php');
        exit;
    }

    try {
        // Check if email already exists
        $query = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $query->execute([$email]);
        if ($query->fetch()) {
            $_SESSION['error'] = "Email already registered";
            header('Location: sign.php');
            exit;
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name, surname, email, password, notifications_enabled) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$nom, $prenom, $email, $hashed_password]);

        // Get the new user's ID
        $user_id = $conn->lastInsertId();

        // Set session data
        $_SESSION['user_id'] = $user_id;
        $_SESSION['userName'] = $nom . ' ' . $prenom;
        $_SESSION['userInitials'] = strtoupper(substr($nom, 0, 1) . substr($prenom, 0, 1));

        // Regenerate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header('Location: ../interfaceDash/f1.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Error creating account: " . $e->getMessage();
        header('Location: sign.php');
        exit;
    }
}

// Handle login
if (isset($_POST['login-form']) && $_POST['login-form'] == '1') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required";
        header('Location: sign.php');
        exit;
    }

    try {
        $query = $conn->prepare("SELECT id, name, surname, password FROM users WHERE email = ?");
        $query->execute([$email]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['userName'] = $user['name'] . ' ' . $user['surname'];
            $_SESSION['userInitials'] = strtoupper(substr($user['name'], 0, 1) . substr($user['surname'], 0, 1));

            // Regenerate CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            header('Location: ../interfaceDash/f1.php');
            exit;
        } else {
            $_SESSION['error'] = "Invalid email or password";
            header('Location: sign.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error logging in: " . $e->getMessage();
        header('Location: sign.php');
        exit;
    }
}

// Invalid form submission
$_SESSION['error'] = "Invalid form submission";
header('Location: sign.php');
exit;
?>