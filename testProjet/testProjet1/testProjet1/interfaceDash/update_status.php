<?php
require 'db.php';

try {
    if (!isset($_POST['id']) || !isset($_POST['status']) || !is_numeric($_POST['id'])) {
        header('Location: f1.php?action=error');
        exit;
    }

    $id = (int)$_POST['id'];
    $status = in_array($_POST['status'], ['en cours', 'terminée']) ? $_POST['status'] : 'en cours';

    $requete = $pdo->prepare("UPDATE tasks2 SET status = :status WHERE id = :id");
    $resultat = $requete->execute([
        'status' => $status,
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