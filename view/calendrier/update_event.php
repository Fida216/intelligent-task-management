<?php
require '../../auth.php';
require '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: calendrier.php?action=error');
    exit();
}

try {
    // Vérifier les champs obligatoires
    if (!isset($_POST['id']) || !isset($_POST['title']) || !isset($_POST['color'])) {
        header('Location: calendrier.php?action=error');
        exit();
    }

    // Nettoyer et valider les entrées
    $id = is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = trim($_POST['title']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $time = isset($_POST['time']) && !empty($_POST['time']) ? trim($_POST['time']) : null;
    $color = trim($_POST['color']);
    $user_id = (int)$_SESSION['user_id'];

    // Valider l'ID
    if ($id <= 0) {
        header('Location: calendrier.php?action=error');
        exit();
    }

    // Valider le titre
    if (empty($title) || strlen($title) > 255) {
        header('Location: calendrier.php?action=error');
        exit();
    }

    // Valider l'heure
    if ($time !== null && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) {
        header('Location: calendrier.php?action=error');
        exit();
    }

    // Valider la couleur
    $valid_colors = ['#8E44AD', '#9B59B6', '#2ECC71', '#3498DB', '#E74C3C'];
    if (!in_array($color, $valid_colors)) {
        header('Location: calendrier.php?action=error');
        exit();
    }

    // Vérifier que l'événement existe et appartient à l'utilisateur
    $requete = $pdo->prepare("SELECT id FROM calendar_events WHERE id = :id AND user_id = :user_id");
    $requete->execute(['id' => $id, 'user_id' => $user_id]);
    if (!$requete->fetch(PDO::FETCH_ASSOC)) {
        header('Location: calendrier.php?action=error');
        exit();
    }

    // Mettre à jour l'événement
    $requete = $pdo->prepare("UPDATE calendar_events SET title = :title, description = :description, time = :time, color = :color 
                             WHERE id = :id AND user_id = :user_id");
    $resultat = $requete->execute([
        'title' => $title,
        'description' => $description,
        'time' => $time,
        'color' => $color,
        'id' => $id,
        'user_id' => $user_id
    ]);

    if ($resultat) {
        header('Location: calendrier.php?action=success');
    } else {
        header('Location: calendrier.php?action=error');
    }
} catch (PDOException $e) {
    error_log("Erreur lors de la mise à jour de l'événement : " . $e->getMessage());
    header('Location: calendrier.php?action=error');
}
exit();
?>