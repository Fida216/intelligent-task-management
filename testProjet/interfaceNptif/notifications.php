<?php
$pdo = new PDO("mysql:host=localhost;dbname=notification_test", "root", "");

// Traitement de la suppression si une requête AJAX est reçue
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    exit; // On arrête l'exécution après une requête AJAX
}
include '../interfaceParam/translations.php';
// Récupération des notifications
$req = $pdo->query("SELECT * FROM notifications ORDER BY date_notification DESC");
$notifs = $req->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../finalProject/finalProject/public/css/tableau_de_bordStyle.css">
    <title>Liste des Notifications</title>
</head>  
<body>
    <button class="sidebar-toggle">☰</button>
    <aside class="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-logo"><?php echo htmlspecialchars($translations[$language]['app_name']); ?></span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../interfaceDash/f1.php" ><?php echo htmlspecialchars($translations[$language]['dashboard']); ?></a></li>
            <li><a href="../interfaceParam/settings.php"><?php echo htmlspecialchars($translations[$language]['title']); ?></a></li>
            <li><a href="../profil/profil.php"><?php echo htmlspecialchars($translations[$language]['profile']); ?></a></li>
            <li><a href="../interfaceCal/stat.php"><?php echo htmlspecialchars($translations[$language]['calendar']); ?></a></li>
            <li><a href="notifications.php" class="active"><?php echo htmlspecialchars($translations[$language]['Notifications']); ?></a></li>
            <li><a href="../interfaceParam/logout.php"><?php echo htmlspecialchars($translations[$language]['logout']); ?></a></li>
        </ul>
    </aside>
    <div class="container">
        <h1>Toutes les notifications</h1>
        <?php if (empty($notifs)): ?>
            <div class="empty-state">
                <p><?php echo $translations[$language]['no_notifications'] ?? 'Aucune notification disponible'; ?></p>
            </div>
        <?php else: ?>
            <ul class="notification-list">
                <?php foreach ($notifs as $n): ?>
                    <li class="notification-item" data-id="<?= $n['id'] ?>">
                        <div class="notification-content">
                            <strong><?= htmlspecialchars($n['date_notification']) ?> :</strong>
                            <span><?= htmlspecialchars($n['message']) ?></span>
                        </div>
                        <button class="delete-btn" aria-label="Supprimer">×</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

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

                // Envoyer la requête de suppression
                fetch('notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + encodeURIComponent(notificationId)
                })
                .then(response => {
                    if (response.ok) {
                        // Animation de disparition
                        notificationItem.style.transition = 'all 0.3s ease';
                        notificationItem.style.opacity = '0';
                        notificationItem.style.transform = 'translateX(100px)';
                        
                        // Suppression après l'animation
                        setTimeout(() => {
                            notificationItem.remove();
                        }, 300);
                    }
                })
                .catch(error => console.error('Erreur:', error));
            });
        });
    </script>
</body>
</html>