<?php
// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../../config/db.php';
require '../../controller/translations.php';
require '../../view/settings/theme.php';

try {
    $stmt = $pdo->prepare("SELECT name, surname, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        header("Location: ../sign/logout.php?error=" . urlencode($translations[$language]['error_load_user']));
        exit;
    }
    $userInitials = isset($user['name'], $user['surname']) ? strtoupper(substr($user['name'], 0, 1) . substr($user['surname'], 0, 1)) : 'FU';
    $userName = isset($user['name'], $user['surname']) ? htmlspecialchars($user['name'] . ' ' . $user['surname']) : 'Unknown User';
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des données utilisateur : " . $e->getMessage());
    $error = $translations[$language]['error_load_user'];
    $userInitials = 'FU'; // Fallback for avatar
    $userName = 'Unknown User'; // Fallback for display
    $user = []; // Ensure $user is defined to avoid undefined index errors
}

// Définir la langue si elle n'est pas définie
$language = isset($_SESSION['language']) && in_array($_SESSION['language'], ['en', 'fr', 'ar']) ? $_SESSION['language'] : 'en';
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($language); ?>" dir="<?php echo $language === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($translations[$language]['app_name']); ?> | <?php echo htmlspecialchars($translations[$language]['profile']); ?></title>
    <link rel="stylesheet" href="../../public/css/profil.css">
</head>
<body data-theme="<?php echo htmlspecialchars(getTheme()); ?>">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-logo"><?php echo htmlspecialchars($translations[$language]['app_name']); ?></span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../dashboard/dashboard.php"><?php echo htmlspecialchars($translations[$language]['dashboard']); ?></a></li>
            <li><a href="../settings/settings.php"><?php echo htmlspecialchars($translations[$language]['settings']); ?></a></li>
            <li><a href="profil.php" class="active"><?php echo htmlspecialchars($translations[$language]['profile']); ?></a></li>
            <li><a href="../calendrier/calendrier.php"><?php echo htmlspecialchars($translations[$language]['calendar']); ?></a></li>
            <li><a href="../notifications/notifications.php"><?php echo htmlspecialchars($translations[$language]['Notifications']); ?></a></li>
            <li><a href="../sign/logout.php"><?php echo htmlspecialchars($translations[$language]['logout']); ?></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="profile-container">
            <header class="profile-header">
                <h2><?php echo htmlspecialchars($translations[$language]['profile']); ?></h2>
                <p class="profile-subtitle"><?php echo htmlspecialchars($translations[$language]['personal_info']); ?></p>
            </header>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="profile-content">
                <section class="profile-info">
                    <div class="profile-avatar">
                        <div class="avatar-large"><?php echo htmlspecialchars($userInitials); ?></div>
                    </div>

                    <div class="profile-details">
                        <div class="form-group">
                            <label for="last-name"><?php echo htmlspecialchars($translations[$language]['name']); ?></label>
                            <div class="profile-field" id="last-name"><?php echo isset($user['name']) ? htmlspecialchars($user['name']) : ''; ?></div>
                        </div>

                        <div class="form-group">
                            <label for="first-name"><?php echo htmlspecialchars($translations[$language]['surname']); ?></label>
                            <div class="profile-field" id="first-name"><?php echo isset($user['surname']) ? htmlspecialchars($user['surname']) : ''; ?></div>
                        </div>

                        <div class="form-group">
                            <label for="email"><?php echo htmlspecialchars($translations[$language]['email']); ?></label>
                            <div class="profile-field" id="email"><?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?></div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="user-profile">
                <form method="POST" action="../../view/settings/theme.php">
                    <input type="hidden" name="theme" value="<?php echo getTheme() === 'light' ? 'dark' : 'light'; ?>">
                    <button type="submit" class="btn btn-secondary theme-toggle">
                        <?php echo getTheme() === 'light' ? htmlspecialchars($translations[$language]['dark_mode']) : htmlspecialchars($translations[$language]['light_mode']); ?>
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>