<?php
require '../auth.php';
include 'connect.php';
include '../interfaceParam/translations.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="SignInStyle.css">
    <title><?php echo htmlspecialchars($translations[$language]['signin_signup_title'] ?? 'Signin & Sign up'); ?></title>
</head>
<body>
    <div class="wrapper">
        <?php if (isset($_GET['signup']) && $_GET['signup'] === 'success'): ?>
            <div class="success-message"><?php echo htmlspecialchars($translations[$language]['signup_success'] ?? 'Compte créé avec succès ! Veuillez vous connecter.'); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="form-container sign-up">
            <form id="signup-form" action="register.php" method="POST">
                <input type="hidden" name="signup-form" value="1">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <h2><?php echo htmlspecialchars($translations[$language]['signup'] ?? 'Sign Up'); ?></h2>
                <div class="form-group">
                    <input type="text" id="signup-nom" name="nom" required>
                    <i class="fas fa-user"></i>
                    <label for="signup-nom"><?php echo htmlspecialchars($translations[$language]['name'] ?? 'Nom'); ?></label>
                </div>
                <div class="form-group">
                    <input type="text" id="signup-prenom" name="prenom" required>
                    <i class="fas fa-user"></i>
                    <label for="signup-prenom"><?php echo htmlspecialchars($translations[$language]['surname'] ?? 'Prénom'); ?></label>
                </div>
                <div class="form-group">
                    <input type="email" id="signup-email" name="email" required>
                    <i class="fas fa-at"></i>
                    <label for="signup-email"><?php echo htmlspecialchars($translations[$language]['email'] ?? 'Email'); ?></label>
                </div>
                <div class="form-group">
                    <input type="password" id="signup-password" name="password" required>
                    <i class="fas fa-lock"></i>
                    <label for="signup-password"><?php echo htmlspecialchars($translations[$language]['password'] ?? 'Mot de passe'); ?></label>
                </div>
                <button type="submit" class="btn"><?php echo htmlspecialchars($translations[$language]['signup'] ?? 'Sign Up'); ?></button>
                <div class="link">
                    <p><?php echo htmlspecialchars($translations[$language]['already_have_account'] ?? 'Tu as déjà un compte ?'); ?> <a href="#" class="signin-link"><?php echo htmlspecialchars($translations[$language]['signin'] ?? 'Sign In'); ?></a></p>
                </div>
            </form>
        </div>
        <div class="form-container sign-in">
            <form id="login-form" action="register.php" method="POST">
                <input type="hidden" name="login-form" value="1">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <h2><?php echo htmlspecialchars($translations[$language]['login'] ?? 'Login'); ?></h2>
                <div class="form-group">
                    <input type="email" id="login-email" name="email" required>
                    <i class="fas fa-user"></i>
                    <label for="login-email"><?php echo htmlspecialchars($translations[$language]['email'] ?? 'Email'); ?></label>
                </div>
                <div class="form-group">
                    <input type="password" id="login-password" name="password" required>
                    <i class="fas fa-lock"></i>
                    <label for="login-password"><?php echo htmlspecialchars($translations[$language]['password'] ?? 'Mot de passe'); ?></label>
                </div>
                <div class="forgot-pass">
                    <a href="mot_de_passe_oublié.html"><?php echo htmlspecialchars($translations[$language]['forgot_password'] ?? 'Mot de passe oublié ?'); ?></a>
                </div>
                <button type="submit" class="btn"><?php echo htmlspecialchars($translations[$language]['login'] ?? 'Login'); ?></button>
                <div class="link">
                    <p><?php echo htmlspecialchars($translations[$language]['new_account'] ?? 'Nouveau compte ?'); ?> <a href="#" class="signup-link"><?php echo htmlspecialchars($translations[$language]['signup'] ?? 'Sign Up'); ?></a></p>
                </div>
            </form>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/9e5ba2e3f5.js" crossorigin="anonymous"></script>
    <script src="main.js"></script>
</body>
</html>