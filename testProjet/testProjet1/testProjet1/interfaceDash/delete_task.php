<?php
require 'db.php';

try {
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        header('Location: f1.php?action=error');
        exit;
    }

    $id = (int)$_POST['id'];
    $requete = $pdo->prepare("DELETE FROM tasks2 WHERE id = :id");
    $resultat = $requete->execute([
        'id' => $id
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