<?php
session_start();
require '../interfaceDash/db.php'; // Use PDO connection from db.php
require '../interfaceParam/translations.php'; // Include translations
require '../interfaceParam/theme.php'; // Include theme management

if (!isset($_SESSION['user_id'])) {
    header("Location: ../sign up signIn/sign.php");
    exit;
}

// Fetch user data using PDO
try {
    $stmt = $pdo->prepare("SELECT name, surname, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        header("Location: ../interfaceParam/logout.php");
        exit;
    }
    $userInitials = isset($user['name'], $user['surname']) ? strtoupper(substr($user['name'], 0, 1) . substr($user['surname'], 0, 1)) : 'FU';
    $userName = isset($user['name'], $user['surname']) ? htmlspecialchars($user['name'] . ' ' . $user['surname']) : 'Unknown User';
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des données utilisateur : " . $e->getMessage();
    $userInitials = 'FU'; // Fallback for avatar
    $userName = 'Unknown User'; // Fallback for display
    $user = []; // Ensure $user is defined to avoid undefined index errors
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($language); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?> | <?php echo htmlspecialchars($translations[$language]['profile'] ?? 'Profile'); ?></title>
    <link rel="stylesheet" href="profilStyle.css">
</head>
<body data-theme="<?php echo htmlspecialchars(getTheme()); ?>">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-logo"><?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?></span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../interfaceDash/f1.php"><?php echo htmlspecialchars($translations[$language]['dashboard'] ?? 'Dashboard'); ?></a></li>
            <li><a href="../interfaceParam/settings.php"><?php echo htmlspecialchars($translations[$language]['settings'] ?? 'Settings'); ?></a></li>
            <li><a href="profil.php" class="active"><?php echo htmlspecialchars($translations[$language]['profile'] ?? 'Profile'); ?></a></li>
            <li><a href="../interfaceCal/stat.php"><?php echo htmlspecialchars($translations[$language]['calendar'] ?? 'Calendar'); ?></a></li>
            <li><a href="../interfaceNptif/notifications.php"><?php echo htmlspecialchars($translations[$language]['Notifications'] ?? 'Notifications'); ?></a></li>
            <li><a href="../interfaceParam/logout.php"><?php echo htmlspecialchars($translations[$language]['logout'] ?? 'Logout'); ?></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="profile-container">
            <header class="profile-header">
                <h2><?php echo htmlspecialchars($translations[$language]['profile'] ?? 'Profile'); ?></h2>
                <p class="profile-subtitle"><?php echo htmlspecialchars($translations[$language]['personal_info'] ?? 'Personal Information'); ?></p>
            </header>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="profile-content">
                <section class="profile-info">
                    <div class="profile-avatar">
                        <div class="avatar-large"><?php echo htmlspecialchars($userInitials ?? 'FU'); ?></div>
                    </div>

                    <div class="profile-details">
                        <div class="form-group">
                            <label for="last-name"><?php echo htmlspecialchars($translations[$language]['name'] ?? 'Name'); ?></label>
                            <div class="profile-field" id="last-name"><?php echo isset($user['name']) ? htmlspecialchars($user['name']) : ''; ?></div>
                        </div>

                        <div class="form-group">
                            <label for="first-name"><?php echo htmlspecialchars($translations[$language]['surname'] ?? 'Surname'); ?></label>
                            <div class="profile-field" id="first-name"><?php echo isset($user['surname']) ? htmlspecialchars($user['surname']) : ''; ?></div>
                        </div>

                        <div class="form-group">
                            <label for="email"><?php echo htmlspecialchars($translations[$language]['email'] ?? 'Email'); ?></label>
                            <div class="profile-field" id="email"><?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?></div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="user-profile">
                <form method="POST" action="">
                    <button type="submit" name="toggle_theme" class="btn btn-secondary theme-toggle">
                        <?php echo getTheme() === 'light' ? '🌙 Dark Mode' : '☀️ Light Mode'; ?>
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>