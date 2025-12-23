<?php
// Exemple : on veut notifier pour la tâche "Réviser" avec priorité "high"
$task_name = "Réviser";
$priority = "high";

// Echappe les paramètres pour éviter les erreurs
$task_name = escapeshellarg($task_name);
$priority = escapeshellarg($priority);

// Commande pour exécuter le script Python
$command = "python3 notification_astar.py $task_name $priority";

// Exécute et récupère la réponse
$output = shell_exec($command);

// Affiche ce que Python a répondu
echo "<h3>Notification : </h3>" . nl2br($output);
?>
