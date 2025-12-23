<?php
require 'db.php';

try {
    if (!isset($_POST['date']) || !isset($_POST['title']) || !isset($_POST['color'])) {
        header('Location: stat.php?action=error');
        exit;
    }

    $date = $_POST['date'];
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $time = $_POST['time'] ?? null;
    $color = $_POST['color'];

    $requete = $pdo->prepare("INSERT INTO calendar_events (date, title, description, time, color) VALUES (:date, :title, :description, :time, :color)");
    $resultat = $requete->execute([
        'date' => $date,
        'title' => $title,
        'description' => $description,
        'time' => $time,
        'color' => $color
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