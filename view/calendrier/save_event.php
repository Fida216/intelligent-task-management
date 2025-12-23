<?php
require '../../auth.php';
require '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: calendrier.php?action=error');
    exit();
}

try {
    // Vérifier les champs obligatoires
    if (!isset($_POST['date']) || !isset($_POST['title']) || !isset($_POST['color'])) {
        header('Location: calendrier.php?action=error');
        exit();
    }

    // Nettoyer et valider les entrées
    $date = trim($_POST['date']);
    $title = trim($_POST['title']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $time = isset($_POST['time']) && !empty($_POST['time']) ? trim($_POST['time']) : null;
    $color = trim($_POST['color']);
    $user_id = (int)$_SESSION['user_id'];

    // Valider la date (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        header('Location: calendrier.php?action=error');
        exit();
    }

    // Valider le titre
    if (empty($title) || strlen($title) > 255) {
        header('Location: calendrier.php?action=error');
        exit();
    }

    // Valider l'heure (HH:MM ou null)
    if ($time !== null && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) {
        header('Location: calendrier.php?action=error');
        exit();
    }

    // Valider la couleur (liste blanche)
    $valid_colors = ['#8E44AD', '#9B59B6', '#2ECC71', '#3498DB', '#E74C3C'];
    if (!in_array($color, $valid_colors)) {
        header('Location: calendrier.php?action=error');
        exit();
    }

    // Insérer l'événement
    $requete = $pdo->prepare("INSERT INTO calendar_events (date, title, description, time, color, user_id) 
                             VALUES (:date, :title, :description, :time, :color, :user_id)");
    $resultat = $requete->execute([
        'date' => $date,
        'title' => $title,
        'description' => $description,
        'time' => $time,
        'color' => $color,
        'user_id' => $user_id
    ]);

    if ($resultat) {
        header('Location: calendrier.php?action=success');
    } else {
        header('Location: calendrier.php?action=error');
    }
} catch (PDOException $e) {
    error_log("Erreur lors de l'ajout de l'événement : " . $e->getMessage());
    header('Location: calendrier.php?action=error');
}
exit();
?>