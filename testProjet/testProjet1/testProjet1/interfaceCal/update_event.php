<?php
require 'db.php';

try {
    if (!isset($_POST['id']) || !isset($_POST['title']) || !isset($_POST['color'])) {
        header('Location: stat.php?action=error');
        exit;
    }

    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $time = $_POST['time'] ?? null;
    $color = $_POST['color'];

    $requete = $pdo->prepare("UPDATE calendar_events SET title = :title, description = :description, time = :time, color = :color WHERE id = :id");
    $resultat = $requete->execute([
        'title' => $title,
        'description' => $description,
        'time' => $time,
        'color' => $color,
        'id' => $id
    ]);

    if ($resultat) {
        header('Location: stat.php?action=success');
    } else {
        header('Location: stat.php?action=error');
    }
    exit;
} catch (PDOException $e) {
    header('Location: stat.php?action=error');
    exit;
}
?>