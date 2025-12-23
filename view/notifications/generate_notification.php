<?php
require '../../auth.php';

$user_id = (int)$_SESSION['user_id'];

// Exécuter le script Python avec user_id
$command = "python3 notification_astar.py " . escapeshellarg($user_id);
$exit_code = 0;
$output = [];
exec($command . ' 2>&1', $output, $exit_code);

if ($exit_code !== 0) {
    error_log("Erreur lors de l'exécution de notification_astar.py: " . implode("\n", $output));
    header('Location: notifications.php?action=error');
    exit();
}

header('Location: notifications.php?action=success');
exit();
?>