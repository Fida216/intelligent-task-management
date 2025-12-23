<?php
require 'db.php';

try {
    // Vérifier les champs obligatoires
    if (!isset($_POST['taskTitle']) || !isset($_POST['taskDeadline']) || !isset($_POST['taskDuration']) || !isset($_POST['taskCategory'])) {
        header('Location: f1.php?action=error');
        exit;
    }

    // Valider les entrées
    $title = trim($_POST['taskTitle']);
    $description = isset($_POST['taskDescription']) ? trim($_POST['taskDescription']) : '';
    $deadline = $_POST['taskDeadline'];
    $duration = trim($_POST['taskDuration']);
    $priority = isset($_POST['taskPriority']) && in_array($_POST['taskPriority'], [1, 2, 3]) ? (int)$_POST['taskPriority'] : 3;
    $category = in_array($_POST['taskCategory'], ['work', 'personal', 'shopping']) ? $_POST['taskCategory'] : 'personal';
    $status = 'en cours';

    // Vérifier que le titre et la durée ne sont pas vides
    if (empty($title) || empty($duration)) {
        header('Location: f1.php?action=error');
        exit;
    }

    // Insérer la tâche
    $requete = $pdo->prepare("INSERT INTO tasks2 (title, description, deadline, duration, priority, category, status) 
                             VALUES (:title, :description, :deadline, :duration, :priority, :category, :status)");
    $resultat = $requete->execute([
        'title' => $title,
        'description' => $description,
        'deadline' => $deadline,
        'duration' => $duration,
        'priority' => $priority,
        'category' => $category,
        'status' => $status
    ]);

    if ($resultat) {
        header('Location: f1.php?action=success');
    } else {
        header('Location: f1.php?action=error');
    }
    exit;
} catch (PDOException $e) {
    header('Location: f1.php?action=error');
    exit;
}
?>