<?php
$pdo = new PDO("mysql:host=localhost;dbname=notification_test", "root", "");
$user_id = 1; // Replace with actual user ID from session or authentication

// Run A* algorithm to generate notifications
shell_exec("python3 astar_notification.py " . $user_id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="notification_style.css">
    <title>Notifications Temps Réel</title>
</head>
<body>
    <h2>Notifications</h2>
    <!-- Notifications affichées -->
    <div id="notifications"></div>

<script>
function chargerNotifications() {
    fetch("charger.php")
        .then(res => res.text())
        .then(data => {
            if (data.trim() !== "") {
                let container = document.getElementById("notifications");
                container.innerHTML += data;

                // Après 10 secondes, disparition automatique
                const notifs = container.querySelectorAll(".notif-box:not(.seen)");
                notifs.forEach((notif, i) => {
                    setTimeout(() => {
                        notif.classList.add("fade-out");
                        setTimeout(() => notif.remove(), 1000);
                    }, 10000);
                });
            }
        });
}

// Actualiser toutes les 5 secondes
setInterval(chargerNotifications, 5000);
chargerNotifications();

// Ignorer une notification
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("notif-ignore")) {
        const box = e.target.closest(".notif-box");
        box.classList.add("fade-out");
        setTimeout(() => box.remove(), 1000);
    }

    // Clic sur le message = redirection vers page
    if (e.target.classList.contains("notif-message")) {
        window.location.href = "notifications.php";
    }
});
</script>
</body>
</html>