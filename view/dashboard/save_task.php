<?php
require '../../auth.php';
require '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: dashboard.php?action=error');
    exit();
}

try {
    // Récupérer et nettoyer les entrées
    $title = trim($_POST['taskTitle'] ?? '');
    $description = trim($_POST['taskDescription'] ?? '');
    $deadline = $_POST['taskDeadline'] ?? '';
    $duration = trim($_POST['taskDuration'] ?? '');
    $priority = (int)($_POST['taskPriority'] ?? 3);
    $category = $_POST['taskCategory'] ?? 'personal';
    $status = 'en cours';
    $user_id = (int)$_SESSION['user_id'];

    // Vérifier les champs obligatoires
    if (empty($title) || empty($deadline) || empty($duration) || empty($category)) {
        header('Location: dashboard.php?action=error');
        exit();
    }

    // Insérer la tâche
    $requete = $pdo->prepare("INSERT INTO tasks2 (title, description, deadline, duration, priority, category, status, user_id) 
                             VALUES (:title, :description, :deadline, :duration, :priority, :category, :status, :user_id)");
    $requete->execute([
        'title' => $title,
        'description' => $description,
        'deadline' => $deadline,
        'duration' => $duration,
        'priority' => $priority,
        'category' => $category,
        'status' => $status,
        'user_id' => $user_id
    ]);

    // Récupérer l'ID de la tâche insérée
    $task_id = $pdo->lastInsertId();

    // Insérer une notification
    $notification_message = "Nouvelle tâche créée : " . htmlspecialchars($title);
    $notification_requete = $pdo->prepare("INSERT INTO notifications (user_id, task_id, message, type, date_notification) 
                                          VALUES (:user_id, :task_id, :message, 'info', NOW())");
    $notification_requete->execute([
        'user_id' => $user_id,
        'task_id' => $task_id,
        'message' => $notification_message
    ]);

    header('Location: dashboard.php?action=success');
} catch (PDOException $e) {
    error_log("Erreur lors de l'ajout de la tâche ou de la notification : " . $e->getMessage());
    header('Location: dashboard.php?action=error');
}
exit();
?>