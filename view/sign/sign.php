<?php
session_start();

// Générer un jeton CSRF si nécessaire
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require '../../config/db.php';

// Afficher les messages d'erreur ou de succès (par exemple, après une inscription échouée)
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error'], $_SESSION['success']); // Nettoyer les messages après affichage
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In / Sign Up</title>
    <link rel="stylesheet" href="../../public/css/sign.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="wrapper animated-signin">
        <!-- Messages d'erreur ou de succès -->
        <?php if ($error): ?>
            <div class="error-message" style="color: red; text-align: center; margin-bottom: 1rem;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message" style="color: green; text-align: center; margin-bottom: 1rem;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Sign In Form -->
        <div class="form-container sign-in">
            <form action="register.php" method="POST">
                <h2>Sign In</h2>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <input type="email" name="email" required>
                    <label>Email</label>
                    <i class="fas fa-at"></i>
                </div>
                <div class="form-group">
                    <input type="password" name="password" required>
                    <label>Password</label>
                    <i class="fas fa-lock"></i>
                </div>
                <div class="forgot-pass">
                    <a href="#">Forgot Password?</a>
                </div>
                <button type="submit" name="login" class="btn">Sign In</button>
                <div class="link">
                    <p>Don't have an account? <a href="#" class="signup-link">Sign Up</a></p>
                </div>
            </form>
        </div>

        <!-- Sign Up Form -->
        <div class="form-container sign-up">
            <form action="register.php" method="POST">
                <h2>Sign Up</h2>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <input type="text" name="name" required>
                    <label>Name</label>
                    <i class="fas fa-user"></i>
                </div>
                <div class="form-group">
                    <input type="text" name="surname" required>
                    <label>Surname</label>
                    <i class="fas fa-user"></i>
                </div>
                <div class="form-group">
                    <input type="email" name="email" required>
                    <label>Email</label>
                    <i class="fas fa-at"></i>
                </div>
                <div class="form-group">
                    <input type="password" name="password" required>
                    <label>Password</label>
                    <i class="fas fa-lock"></i>
                </div>
                <button type="submit" name="signup" class="btn">Sign Up</button>
                <div class="link">
                    <p>Already have an account? <a href="#" class="signin-link">Sign In</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="../../public/js/sign.js"></script>
</body>
</html>