<?php
require 'db.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taskenuis | Ajouter un événement</title>
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
            <h1><span>📅</span> Ajouter un événement</h1>
            <div class="user-profile">
                <div class="user-avatar">FF</div>
                <span>Foulen Fouleni</span>
            </div>
        </div>

        <div class="modal-content">
            <div class="modal-header">
                <h2>Nouvel Événement</h2>
                <a href="stat.php" class="close-modal">×</a>
            </div>
            <form method="POST" action="save_event.php">
                <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                
                <div class="form-group">
                    <label for="event-title">Titre*</label>
                    <input type="text" id="event-title" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="event-description">Description</label>
                    <textarea id="event-description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="event-time">Heure</label>
                    <input type="time" id="event-time" name="time" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="event-color">Couleur</label>
                    <select id="event-color" name="color" class="form-control">
                        <option value="#8E44AD">Violet</option>
                        <option value="#9B59B6">Violet clair</option>
                        <option value="#2ECC71">Vert</option>
                        <option value="#3498DB">Bleu</option>
                        <option value="#E74C3C">Rouge</option>
                    </select>
                </div>
                
                <div class="modal-footer">
                    <a href="stat.php" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>