<?php
require 'db.php';

if (!isset($_GET['id'])) {
    header('Location: stat.php');
    exit;
}

try {
    $requete = $pdo->prepare("SELECT * FROM calendar_events WHERE id = :id");
    $requete->execute(['id' => $_GET['id']]);
    $event = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        header('Location: stat.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: stat.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taskenuis | Modifier un événement</title>
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-logo">Taskenuis</span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="f1.php">📋 Dashboard</a></li>
            <li><a href="statistiques.html">📊 Statistiques</a></li>
            <li><a href="paramètres.html">⚙️ Paramètres</a></li>
            <li><a href="profil.html">👤 Profil</a></li>
            <li><a href="stat.php" class="active">📅 Calendrier</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <h1><span>📅</span> Modifier un événement</h1>
            <div class="user-profile">
                <div class="user-avatar">FF</div>
                <span>Foulen Fouleni</span>
            </div>
        </div>

        <div class="modal-content">
            <div class="modal-header">
                <h2>Modifier Événement</h2>
                <a href="stat.php" class="close-modal">×</a>
            </div>
            <form method="POST" action="update_event.php">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($event['id']); ?>">
                
                <div class="form-group">
                    <label for="event-title">Titre*</label>
                    <input type="text" id="event-title" name="title" class="form-control" value="<?php echo htmlspecialchars($event['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="event-description">Description</label>
                    <textarea id="event-description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($event['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="event-time">Heure</label>
                    <input type="time" id="event-time" name="time" class="form-control" value="<?php echo htmlspecialchars($event['time'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="event-color">Couleur</label>
                    <select id="event-color" name="color" class="form-control">
                        <option value="#8E44AD" <?php if ($event['color'] === '#8E44AD') echo 'selected'; ?>>Violet</option>
                        <option value="#9B59B6" <?php if ($event['color'] === '#9B59B6') echo 'selected'; ?>>Violet clair</option>
                        <option value="#2ECC71" <?php if ($event['color'] === '#2ECC71') echo 'selected'; ?>>Vert</option>
                        <option value="#3498DB" <?php if ($event['color'] === '#3498DB') echo 'selected'; ?>>Bleu</option>
                        <option value="#E74C3C" <?php if ($event['color'] === '#E74C3C') echo 'selected'; ?>>Rouge</option>
                    </select>
                </div>
                
                <div class="modal-footer">
                    <a href="stat.php" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
            <form method="POST" action="delete_event.php">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($event['id']); ?>">
                <button type="submit" class="btn btn-block" style="background: #E74C3C; color: white;">Supprimer</button>
            </form>
        </div>
    </main>
</body>
</html>