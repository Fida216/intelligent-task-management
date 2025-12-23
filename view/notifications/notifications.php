<?php
require '../../auth.php';
require '../../config/db.php';
require '../../controller/translations.php';

$user_id = (int)$_SESSION['user_id'];
$language = isset($_SESSION['language']) && in_array($_SESSION['language'], ['en', 'fr', 'ar']) ? $_SESSION['language'] : 'en';
$theme = isset($_SESSION['theme']) && in_array($_SESSION['theme'], ['light', 'dark']) ? $_SESSION['theme'] : 'light';

// Récupérer les informations de l'utilisateur
$query = $pdo->prepare("SELECT name, surname FROM users WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

$userName = htmlspecialchars($user['name'] . ' ' . $user['surname']);
$userInitials = strtoupper(substr($user['name'], 0, 1) . substr($user['surname'], 0, 1));

// Traitement de la suppression via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    try {
        $id = is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la suppression de la notification : " . $e->getMessage());
    }
    exit();
}

// Récupération des notifications
try {
    $req = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY date_notification DESC");
    $req->execute([$user_id]);
    $notifs = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des notifications : " . $e->getMessage());
    $error = sprintf(htmlspecialchars($translations[$language]['notifications_fetch_error'] ?? 'Error fetching notifications: %s'), htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($language); ?>" dir="<?php echo $language === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?> |
        <?php echo htmlspecialchars($translations[$language]['Notifications'] ?? 'Notifications'); ?>
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/notifications.css">
</head>
<body data-theme="<?php echo htmlspecialchars($theme); ?>">
<aside class="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-logo"><?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?></span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../dashboard/dashboard.php"><?php echo htmlspecialchars($translations[$language]['dashboard'] ?? 'Dashboard'); ?></a></li>
            <li><a href="../settings/settings.php"><?php echo htmlspecialchars($translations[$language]['title'] ?? 'Settings'); ?></a></li>
            <li><a href="../profil/profil.php"><?php echo htmlspecialchars($translations[$language]['profile'] ?? 'Profile'); ?></a></li>
            <li><a href="../calendrier/calendrier.php"><?php echo htmlspecialchars($translations[$language]['calendar'] ?? 'Calendar'); ?></a></li>
            <li><a href="notifications.php" class="active"><?php echo htmlspecialchars($translations[$language]['Notifications'] ?? 'Notifications'); ?></a></li>
            <li><a href="../sign/logout.php"><?php echo htmlspecialchars($translations[$language]['logout'] ?? 'Logout'); ?></a></li>
        </ul>
    </aside>
    <main class="main-content">
        <div class="header">
            <h1><span>🔔</span>
                <?php echo htmlspecialchars($translations[$language]['Notifications'] ?? 'Notifications'); ?>
            </h1>
            <div class="user-profile">
                <div class="user-avatar">
                    <?php echo htmlspecialchars($userInitials); ?>
                </div>
                <span>
                    <?php echo htmlspecialchars($userName); ?>
                </span>
                <form method="POST" action="../../view/settings/theme.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button type="submit" name="toggle_theme" class="btn btn-secondary theme-toggle">
                        <?php echo $theme === 'light' ? '🌙 Dark Mode' : '☀️ Light Mode'; ?>
                    </button>
                </form>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['action']) && $_GET['action'] === 'success'): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($translations[$language]['success_message'] ?? 'Action completed successfully!'); ?>
            </div>
        <?php elseif (isset($_GET['action']) && $_GET['action'] === 'error'): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($translations[$language]['error_message'] ?? 'Error during action execution.'); ?>
            </div>
        <?php endif; ?>

        <div class="container">
            <h2><?php echo htmlspecialchars($translations[$language]['all_notifications'] ?? 'All Notifications'); ?></h2>
            <?php if (empty($notifs)): ?>
                <div class="empty-state">
                    <p><?php echo htmlspecialchars($translations[$language]['no_notifications'] ?? 'No notifications available'); ?></p>
                </div>
            <?php else: ?>
                <ul class="notification-list">
                    <?php foreach ($notifs as $n): ?>
                        <li class="notification-item" data-id="<?php echo htmlspecialchars($n['id']); ?>">
                            <div class="notification-content">
                                <strong><?php echo htmlspecialchars($n['date_notification']); ?> :</strong>
                                <span><?php echo htmlspecialchars($n['message']); ?></span>
                            </div>
                            <button class="delete-btn" aria-label="<?php echo htmlspecialchars($translations[$language]['delete_button'] ?? 'Delete'); ?>">×</button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Sidebar toggle
        document.querySelector('.sidebar-toggle').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Gestion de la suppression via AJAX
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const notificationItem = this.closest('.notification-item');
                const notificationId = notificationItem.dataset.id;

                fetch('notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${encodeURIComponent(notificationId)}&csrf_token=${encodeURIComponent('<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>')}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }
                    notificationItem.style.transition = 'all 0.3s ease';
                    notificationItem.style.opacity = '0';
                    notificationItem.style.transform = 'translateX(100px)';
                    setTimeout(() => notificationItem.remove(), 300);
                })
                .catch(error => console.error('Erreur:', error));
            });
        });
    </script>
</body>
</html>