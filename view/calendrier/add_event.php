<?php
require '../../auth.php';
require '../../config/db.php';
require '../../controller/translations.php';

$user_id = (int)$_SESSION['user_id'];
$language = isset($_SESSION['language']) && in_array($_SESSION['language'], ['en', 'fr', 'ar']) ? $_SESSION['language'] : 'en';
$theme = isset($_SESSION['theme']) && in_array($_SESSION['theme'], ['light', 'dark']) ? $_SESSION['theme'] : 'light';

$query = $pdo->prepare("SELECT name, surname FROM users WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);
$userName = htmlspecialchars($user['name'] . ' ' . $user['surname']);
$userInitials = strtoupper(substr($user['name'], 0, 1) . substr($user['surname'], 0, 1));

$date = isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date']) ? $_GET['date'] : date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($language); ?>" dir="<?php echo $language === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?> |
        <?php echo htmlspecialchars($translations[$language]['new_event'] ?? 'Add Event'); ?>
    </title>
    <link rel="stylesheet" href="../../public/css/calendrier.css">
</head>
<body data-theme="<?php echo htmlspecialchars($theme); ?>">
<aside class="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-logo"><?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?></span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../dashboard/dashboard.php" class="active"><?php echo htmlspecialchars($translations[$language]['dashboard'] ?? 'Dashboard'); ?></a></li>
            <li><a href="../settings/settings.php"><?php echo htmlspecialchars($translations[$language]['title'] ?? 'Settings'); ?></a></li>
            <li><a href="../profil/profil.php"><?php echo htmlspecialchars($translations[$language]['profile'] ?? 'Profile'); ?></a></li>
            <li><a href="calendrier.php"><?php echo htmlspecialchars($translations[$language]['calendar'] ?? 'Calendar'); ?></a></li>
            <li><a href="../notifications/notifications.php"><?php echo htmlspecialchars($translations[$language]['Notifications'] ?? 'Notifications'); ?></a></li>
            <li><a href="../../view/logout.php"><?php echo htmlspecialchars($translations[$language]['logout'] ?? 'Logout'); ?></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="header">
            <h1><span>📅</span>
                <?php echo htmlspecialchars($translations[$language]['new_event'] ?? 'Add Event'); ?>
            </h1>
            <div class="user-profile">
                <div class="user-avatar">
                    <?php echo htmlspecialchars($userInitials); ?>
                </div>
                <span>
                    <?php echo htmlspecialchars($userName); ?>
                </span>
            </div>
        </div>

        <div class="modal-content">
            <div class="modal-header">
                <h2>
                    <?php echo htmlspecialchars($translations[$language]['new_event'] ?? 'New Event'); ?>
                </h2>
                <a href="calendrier.php" class="close-modal">×</a>
            </div>
            <form method="POST" action="save_event.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                
                <div class="form-group">
                    <label for="event-title">
                        <?php echo htmlspecialchars($translations[$language]['task_title_label'] ?? 'Title'); ?>*
                    </label>
                    <input type="text" id="event-title" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="event-description">
                        <?php echo htmlspecialchars($translations[$language]['description'] ?? 'Description'); ?>
                    </label>
                    <textarea id="event-description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="event-time">
                        <?php echo htmlspecialchars($translations[$language]['time'] ?? 'Time'); ?>
                    </label>
                    <input type="time" id="event-time" name="time" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="event-color">
                        <?php echo htmlspecialchars($translations[$language]['color'] ?? 'Color'); ?>
                    </label>
                    <select id="event-color" name="color" class="form-control">
                        <option value="#8E44AD">
                            <?php echo htmlspecialchars($translations[$language]['purple'] ?? 'Purple'); ?>
                        </option>
                        <option value="#9B59B6">
                            <?php echo htmlspecialchars($translations[$language]['light_purple'] ?? 'Light Purple'); ?>
                        </option>
                        <option value="#2ECC71">
                            <?php echo htmlspecialchars($translations[$language]['green'] ?? 'Green'); ?>
                        </option>
                        <option value="#3498DB">
                            <?php echo htmlspecialchars($translations[$language]['blue'] ?? 'Blue'); ?>
                        </option>
                        <option value="#E74C3C">
                            <?php echo htmlspecialchars($translations[$language]['red'] ?? 'Red'); ?>
                        </option>
                    </select>
                </div>
                
                <div class="modal-footer">
                    <a href="calendrier.php" class="btn btn-secondary">
                        <?php echo htmlspecialchars($translations[$language]['cancel_button'] ?? 'Cancel'); ?>
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <?php echo htmlspecialchars($translations[$language]['save_button'] ?? 'Save'); ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>