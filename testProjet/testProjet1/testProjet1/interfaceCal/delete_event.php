<?php
require 'db.php';

try {
    if (!isset($_POST['id'])) {
        header('Location: stat.php?action=error');
        exit;
    }

    $id = $_POST['id'];
    $requete = $pdo->prepare("DELETE FROM calendar_events WHERE id = :id");
    $resultat = $requete->execute(['id' => $id]);

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