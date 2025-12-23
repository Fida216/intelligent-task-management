<?php
session_start();
require '../../config/db.php';

// Créer la table users si elle n'existe pas
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        surname VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        notifications_enabled TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (PDOException $e) {
    die("Error creating users table: " . $e->getMessage());
}

// Traitement de la connexion (Sign In)
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $query = $pdo->prepare("SELECT id, name, surname, email, password, notifications_enabled FROM users WHERE email = ?");
        $query->execute([$email]);
        $user = $query->fetch();

        // Check if user exists and password matches
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['surname'] = $user['surname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['notifications_enabled'] = $user['notifications_enabled'];
            $_SESSION['language'] = 'en';
            $_SESSION['theme'] = 'light';
            header('Location: ../../view/dashboard/dashboard.php');
            exit();
        }
        header('Location: ../../view/dashboard/dashboard.php');
        exit();
    } catch (PDOException $e) {
        error_log("Error during login: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred.";
        header('Location: sign.php');
        exit();
    }
}

// Traitement de l'inscription (Sign Up)
if (isset($_POST['signup'])) {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Check if email already exists
        $query = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $query->execute([$email]);

        if ($query->fetchColumn() > 0) {
            $_SESSION['error'] = "This email is already in use.";
            header('Location: sign.php');
            exit();
        }

        // Hash the password and insert the user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = $pdo->prepare("INSERT INTO users (name, surname, email, password) VALUES (?, ?, ?, ?)");
        $query->execute([$name, $surname, $email, $hashed_password]);

        $_SESSION['success'] = "Registration successful! Please sign in.";
        header('Location: sign.php');
        exit();
    } catch (PDOException $e) {
        error_log("Error during registration: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred.";
        header('Location: sign.php');
        exit();
    }
}

// Si aucune action valide, rediriger
$_SESSION['error'] = "Invalid action.";
header('Location: sign.php');
exit();
?>