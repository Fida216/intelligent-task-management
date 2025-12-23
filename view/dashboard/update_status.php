<?php
require '../../auth.php';
require '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: dashboard.php?action=error');
    exit();
}

try {
    if (!isset($_POST['id']) || !isset($_POST['status']) || !is_numeric($_POST['id'])) {
        header('Location: dashboard.php?action=error');
        exit();
    }

    $id = (int)$_POST['id'];
    $status = in_array($_POST['status'], ['en cours', 'terminée']) ? $_POST['status'] : 'en cours';
    $user_id = (int)$_SESSION['user_id'];

    $requete = $pdo->prepare("UPDATE tasks2 SET status = :status WHERE id = :id AND user_id = :user_id");
    $resultat = $requete->execute([
        'status' => $status,
        'id' => $id,
        'user_id' => $user_id
    ]);

    if ($resultat) {
        header('Location: dashboard.php?action=success');
    } else {
        header('Location: dashboard.php?action=error');
    }
} catch (PDOException $e) {
    error_log("Erreur lors de la mise à jour du statut : " . $e->getMessage());
    header('Location: dashboard.php?action=error');
}
exit();
?>