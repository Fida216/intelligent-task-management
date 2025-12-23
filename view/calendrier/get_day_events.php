<?php
require '../../auth.php';
require '../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit();
}

try {
    if (!isset($_POST['date']) || !isset($_POST['user_id']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['date']) || !is_numeric($_POST['user_id'])) {
        echo json_encode(['error' => 'Invalid date or user_id']);
        exit();
    }

    $date = trim($_POST['date']);
    $user_id = (int)$_POST['user_id'];

    // Vérifier que l'utilisateur correspond à la session
    if ($user_id !== (int)$_SESSION['user_id']) {
        echo json_encode(['error' => 'Unauthorized user']);
        exit();
    }

    $requete = $pdo->prepare("SELECT id, title, description, time, color FROM calendar_events 
                             WHERE date = :date AND user_id = :user_id");
    $requete->execute([
        'date' => $date,
        'user_id' => $user_id
    ]);
    $events = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Nettoyer les données pour l'affichage
    foreach ($events as &$event) {
        $event['title'] = htmlspecialchars($event['title']);
        $event['description'] = htmlspecialchars($event['description'] ?? '');
        $event['time'] = htmlspecialchars($event['time'] ?? '');
        $event['color'] = htmlspecialchars($event['color']);
    }

    echo json_encode(['events' => $events]);
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des événements : " . $e->getMessage());
    echo json_encode(['error' => 'Error fetching events']);
}
exit();
?>