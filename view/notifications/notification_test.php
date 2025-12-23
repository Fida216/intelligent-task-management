<?php
require '../../auth.php';
require '../../config/db.php';
require '../../controller/translations.php';

$user_id = (int)$_SESSION['user_id'];
$language = isset($_SESSION['language']) && in_array($_SESSION['language'], ['en', 'fr', 'ar']) ? $_SESSION['language'] : 'en';
$theme = isset($_SESSION['theme']) && in_array($_SESSION['theme'], ['light', 'dark']) ? $_SESSION['theme'] : 'light';

// Vérifier si les notifications sont activées
$req = $pdo->prepare("SELECT notification_enabled FROM users WHERE id = ?");
$req->execute([$user_id]);
$user = $req->fetch(PDO::FETCH_ASSOC);
if (!$user || !$user['notification_enabled']) {
    header('Location: notifications.php?action=error');
    exit();
}

// Exécuter le script Python
$command = "python3 notification_astar.py " . escapeshellarg($user_id);
$exit_code = 0;
$output = [];
exec($command . ' 2>&1', $output, $exit_code);
if ($exit_code !== 0) {
    error_log("Erreur lors de l'exécution de notification_astar.py: " . implode("\n", $output));
}
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($language); ?>" dir="<?php echo $language === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?> |
        <?php echo htmlspecialchars($translations[$language]['real_time_notifications'] ?? 'Real-Time Notifications'); ?>
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/.css">
</head>
<body data-theme="<?php echo htmlspecialchars($theme); ?>">
    <h2><?php echo htmlspecialchars($translations[$language]['real_time_notifications'] ?? 'Real-Time Notifications'); ?></h2>
    <div id="notifications"></div>

    <script>
        const translations = <?php echo json_encode($translations[$language]); ?>;
        
        function chargerNotifications() {
            fetch("charger.php", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `csrf_token=${encodeURIComponent('<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>')}`
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Erreur réseau');
                }
                return res.text();
            })
            .then(data => {
                if (data.trim() !== "") {
                    let container = document.getElementById("notifications");
                    container.innerHTML += data;

                    const notifs = container.querySelectorAll(".notif-box:not(.seen)");
                    notifs.forEach((notif, i) => {
                        setTimeout(() => {
                            notif.classList.add("fade-out");
                            setTimeout(() => notif.remove(), 1000);
                        }, 10000);
                    });
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        }

        setInterval(chargerNotifications, 5000);
        chargerNotifications();

        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("notif-ignore")) {
                const box = e.target.closest(".notif-box");
                box.classList.add("fade-out");
                setTimeout(() => box.remove(), 1000);
            }

            if (e.target.classList.contains("notif-message")) {
                window.location.href = "notifications.php";
            }
        });
    </script>
</body>
</html>