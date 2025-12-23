<?php
// Inclure les fichiers de configuration et de traductions
session_start();

// Inclure les fichiers de configuration et de traductions
require '../../config/db.php'; // Utiliser $pdo comme dans register.php
include '../../controller/translations.php';

// Générer ou récupérer le token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Récupérer les données de l'utilisateur
try {
    $query = $pdo->prepare("SELECT name, surname, email, notifications_enabled FROM users WHERE id = ?");
    $query->execute([$user_id]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Rediriger si l'utilisateur n'existe pas (cas improbable après sign-in)
        header('Location: ../../sign/logout.php');
        exit;
    }

    // Initialiser les variables avec les données de l'utilisateur
    $userName = $user['name'];
    $userSurname = $user['surname'];
    $userEmail = $user['email'];
    $notificationsEnabled = $user['notifications_enabled'] ?? 0;
} catch (PDOException $e) {
    error_log("Erreur lors du chargement des données utilisateur : " . $e->getMessage());
    $error = $translations[$language]['error_load_user'];
    $userName = '';
    $userSurname = '';
    $userEmail = '';
    $notificationsEnabled = 0;
}

// Gérer le thème et la langue depuis la session ou les cookies
$theme = $_SESSION['theme'] ?? ($_COOKIE['theme'] ?? 'light');
$language = $_SESSION['language'] ?? ($_COOKIE['language'] ?? 'en');

// S'assurer que le thème et la langue sont valides
$theme = in_array($theme, ['light', 'dark']) ? $theme : 'light';
$language = in_array($language, ['en', 'fr', 'ar']) ? $language : 'en';

// Stocker dans la session pour cohérence
$_SESSION['theme'] = $theme;
$_SESSION['language'] = $language;

// Récupérer les notifications si elles sont activées
$notifications = [];
if ($notificationsEnabled) {
    try {
        $query = $pdo->prepare("
            SELECT title, deadline, status 
            FROM tasks2 
            WHERE user_id = ? 
            AND (
                (deadline < NOW() AND status != 'completed') 
                OR (deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 DAY))
            )
        ");
        $query->execute([$user_id]);
        $tasks = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tasks as $task) {
            $deadline = $task['deadline'];
            $now = date('Y-m-d H:i:s');
            $type = ($deadline < $now && $task['status'] != 'completed') ? 'due-soon' : 'upcoming';
            $message = ($type === 'due-soon')
                ? sprintf($translations[$language]['task_overdue'], $task['title'], $deadline)
                : sprintf($translations[$language]['task_due_soon'], $task['title'], $deadline);
            $notifications[] = [
                'message' => $message,
                'type' => $type
            ];
        }
    } catch (PDOException $e) {
        error_log("Erreur lors du chargement des notifications : " . $e->getMessage());
        $notifications[] = [
            'message' => $translations[$language]['notification_error'],
            'type' => 'error'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($language); ?>" dir="<?php echo $language === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title><?php echo htmlspecialchars($translations[$language]['title']); ?></title>
    <link rel="stylesheet" href="../../public/css/settings.css">
</head>
<body data-theme="<?php echo htmlspecialchars($theme); ?>">
    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
    <aside class="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-logo"><?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?></span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../dashboard/dashboard.php"><?php echo htmlspecialchars($translations[$language]['dashboard'] ?? 'Dashboard'); ?></a></li>
            <li><a href="settings.php"class="active"><?php echo htmlspecialchars($translations[$language]['title'] ?? 'Settings'); ?></a></li>
            <li><a href="../profil/profil.php"><?php echo htmlspecialchars($translations[$language]['profile'] ?? 'Profile'); ?></a></li>
            <li><a href="../calendrier/calendrier.php"><?php echo htmlspecialchars($translations[$language]['calendar'] ?? 'Calendar'); ?></a></li>
            <li><a href="../notifications/notifications.php"><?php echo htmlspecialchars($translations[$language]['Notifications'] ?? 'Notifications'); ?></a></li>
            <li><a href="../sign/logout.php"><?php echo htmlspecialchars($translations[$language]['logout'] ?? 'Logout'); ?></a></li>
        </ul>
    </aside>
    <div class="main-content">
        <div class="settings-container">
            <h2><?php echo htmlspecialchars($translations[$language]['title']); ?></h2>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['success'])): ?>
                <div class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>

            <?php if (!empty($notifications)): ?>
                <div class="notifications">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification <?php echo htmlspecialchars($notification['type']); ?>">
                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="settings-section">
                <h3><?php echo htmlspecialchars($translations[$language]['personal_info']); ?></h3>
                <form action="update_profile.php" method="POST" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="form-group">
                        <label><?php echo htmlspecialchars($translations[$language]['name']); ?>:</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($userName); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo htmlspecialchars($translations[$language]['surname']); ?>:</label>
                        <input type="text" name="surname" value="<?php echo htmlspecialchars($userSurname); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo htmlspecialchars($translations[$language]['email']); ?>:</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                    </div>
                    <button type="submit" class="save-btn"><?php echo htmlspecialchars($translations[$language]['save']); ?></button>
                </form>
            </div>

            <div class="settings-section">
                <h3><?php echo htmlspecialchars($translations[$language]['change_password']); ?></h3>
                <form action="update_password.php" method="POST" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="form-group">
                        <label><?php echo htmlspecialchars($translations[$language]['current_password']); ?>:</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo htmlspecialchars($translations[$language]['new_password']); ?>:</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo htmlspecialchars($translations[$language]['confirm_password']); ?>:</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="save-btn"><?php echo htmlspecialchars($translations[$language]['save']); ?></button>
                </form>
            </div>

            <div class="settings-section">
                <h3><?php echo htmlspecialchars($translations[$language]['theme']); ?></h3>
                <form action="change_settings.php" method="POST" class="settings-form">
                    <input type="hidden" name="language" value="<?php echo htmlspecialchars($language); ?>">
                    <div class="form-group">
                        <label><?php echo htmlspecialchars($translations[$language]['dark_mode']); ?>:</label>
                        <label class="switch">
                            <input type="checkbox" name="theme" value="dark" <?php echo $theme === 'dark' ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </form>
            </div>

            <div class="settings-section">
                <h3><?php echo htmlspecialchars($translations[$language]['language']); ?></h3>
                <form action="change_settings.php" method="POST" class="settings-form">
                    <input type="hidden" name="theme" value="<?php echo htmlspecialchars($theme); ?>">
                    <div class="form-group">
                        <label><?php echo htmlspecialchars($translations[$language]['select_language']); ?>:</label>
                        <select name="language" onchange="this.form.submit()">
                            <option value="en" <?php echo $language === 'en' ? 'selected' : ''; ?>>English</option>
                            <option value="fr" <?php echo $language === 'fr' ? 'selected' : ''; ?>>Français</option>
                            <option value="ar" <?php echo $language === 'ar' ? 'selected' : ''; ?>>العربية</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="settings-section">
                <h3><?php echo htmlspecialchars($translations[$language]['notifications']); ?></h3>
                <form action="update_notifications.php" method="POST" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="form-group">
                        <label><?php echo htmlspecialchars($translations[$language]['enable_notifications']); ?>:</label>
                        <label class="switch">
                            <input type="checkbox" name="notifications" value="1" <?php echo $notificationsEnabled ? 'checked' : ''; ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <button type="submit" class="save-btn"><?php echo htmlspecialchars($translations[$language]['save']); ?></button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const isRTL = document.documentElement.getAttribute('dir') === 'rtl';
            sidebar.style.transform = sidebar.style.transform === 'translateX(0px)' 
                ? (isRTL ? 'translateX(100%)' : 'translateX(-100%)') 
                : 'translateX(0px)';
        }
    </script>
</body>
</html>