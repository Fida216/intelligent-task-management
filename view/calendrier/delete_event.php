<?php
require '../../auth.php';
require '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: calendrier.php?action=error');
    exit();
}

try {
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        header('Location: calendrier.php?action=error');
        exit();
    }

    $id = (int)$_POST['id'];
    $user_id = (int)$_SESSION['user_id'];

    $requete = $pdo->prepare("DELETE FROM calendar_events WHERE id = :id AND user_id = :user_id");
    $resultat = $requete->execute([
        'id' => $id,
        'user_id' => $user_id
    ]);

    if ($resultat) {
        header('Location: calendrier.php?action=success');
    } else {
        header('Location: calendrier.php?action=error');
    }
} catch (PDOException $e) {
    error_log("Erreur lors de la suppression de l'événement : " . $e->getMessage());
    header('Location: calendrier.php?action=error');
}
exit();
?>