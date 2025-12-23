<?php
require '../../auth.php';
require '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: dashboard.php?action=error');
    exit();
}

try {
    // Vérifier les champs obligatoires
    if (!isset($_POST['id']) || !isset($_POST['taskTitle']) || !isset($_POST['taskDeadline']) || !isset($_POST['taskDuration']) || !isset($_POST['taskCategory'])) {
        header('Location: dashboard.php?action=error');
        exit();
    }

    // Nettoyer et valider les entrées
    $id = is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = trim($_POST['taskTitle']);
    $description = isset($_POST['taskDescription']) ? trim($_POST['taskDescription']) : '';
    $deadline = $_POST['taskDeadline'];
    $duration = trim($_POST['taskDuration']);
    $priority = isset($_POST['taskPriority']) && in_array($_POST['taskPriority'], ['1', '2', '3']) ? (int)$_POST['taskPriority'] : 3;
    $category = in_array($_POST['taskCategory'], ['work', 'personal', 'shopping']) ? $_POST['taskCategory'] : 'personal';
    $user_id = (int)$_SESSION['user_id'];

    // Vérifier que le titre, la durée et l'ID sont valides
    if ($id <= 0 || empty($title) || empty($duration)) {
        header('Location: dashboard.php?action=error');
        exit();
    }

    // Valider la date
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) {
        header('Location: dashboard.php?action=error');
        exit();
    }

    // Vérifier que la tâche existe et appartient à l'utilisateur
    $requete = $pdo->prepare("SELECT status FROM tasks2 WHERE id = :id AND user_id = :user_id");
    $requete->execute(['id' => $id, 'user_id' => $user_id]);
    $current_task = $requete->fetch(PDO::FETCH_ASSOC);
    if (!$current_task) {
        header('Location: dashboard.php?action=error');
        exit();
    }

    // Mettre à jour la tâche
    $requete = $pdo->prepare("UPDATE tasks2 SET title = :title, description = :description, deadline = :deadline, 
                             duration = :duration, priority = :priority, category = :category, 
                             status = :status WHERE id = :id AND user_id = :user_id");
    $resultat = $requete->execute([
        'title' => $title,
        'description' => $description,
        'deadline' => $deadline,
        'duration' => $duration,
        'priority' => $priority,
        'category' => $category,
        'status' => $current_task['status'],
        'id' => $id,
        'user_id' => $user_id
    ]);

    if ($resultat) {
        header('Location: dashboard.php?action=success');
    } else {
        header('Location: dashboard.php?action=error');
    }
} catch (PDOException $e) {
    error_log("Erreur lors de la mise à jour de la tâche : " . $e->getMessage());
    header('Location: dashboard.php?action=error');
}
exit();
?>