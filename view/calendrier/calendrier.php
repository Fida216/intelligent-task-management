<?php
require '../../config/db.php';
require '../../auth.php';
require '../../controller/translations.php';

// Récupérer les informations de l'utilisateur
$user_id = (int)$_SESSION['user_id'];
$language = isset($_SESSION['language']) && in_array($_SESSION['language'], ['en', 'fr', 'ar']) ? $_SESSION['language'] : 'en';
$theme = isset($_SESSION['theme']) && in_array($_SESSION['theme'], ['light', 'dark']) ? $_SESSION['theme'] : 'light';

$query = $pdo->prepare("SELECT name, surname FROM users WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

$userName = htmlspecialchars($user['name'] . ' ' . $user['surname']);
$userInitials = strtoupper(substr($user['name'], 0, 1) . substr($user['surname'], 0, 1));

// Déterminer le mois et l'année à afficher
$currentDate = new DateTime();
if (isset($_GET['month']) && isset($_GET['year']) && is_numeric($_GET['month']) && is_numeric($_GET['year'])) {
    $month = (int)$_GET['month'];
    $year = (int)$_GET['year'];
    if ($month >= 1 && $month <= 12 && $year >= 1970 && $year <= 9999) {
        $currentDate->setDate($year, $month, 1);
    }
}

// Récupérer les événements pour le mois actuel
$firstDayOfMonth = clone $currentDate;
$firstDayOfMonth->setDate($currentDate->format('Y'), $currentDate->format('m'), 1);
$lastDayOfMonth = clone $currentDate;
$lastDayOfMonth->setDate($currentDate->format('Y'), $currentDate->format('m'), $lastDayOfMonth->format('t'));

try {
    $requete = $pdo->prepare("SELECT * FROM calendar_events WHERE user_id = :user_id AND date BETWEEN :start_date AND :end_date");
    $requete->execute([
        'user_id' => $user_id,
        'start_date' => $firstDayOfMonth->format('Y-m-d'),
        'end_date' => $lastDayOfMonth->format('Y-m-d')
    ]);
    $events = $requete->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des événements : " . $e->getMessage());
    $error = sprintf(htmlspecialchars($translations[$language]['events_fetch_error'] ?? 'Error fetching events: %s'), htmlspecialchars($e->getMessage()));
}

// Générer les liens de navigation
$prevMonth = clone $currentDate;
$prevMonth->modify('-1 month');
$nextMonth = clone $currentDate;
$nextMonth->modify('+1 month');

$today = new DateTime();
$isCurrentMonth = $today->format('Y-m') === $currentDate->format('Y-m');
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($language); ?>" dir="<?php echo $language === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?> |
        <?php echo htmlspecialchars($translations[$language]['calendar'] ?? 'Calendar'); ?>
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/calendrier.css">
</head>
<body data-theme="<?php echo htmlspecialchars($theme); ?>">
    <aside class="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-logo"><?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?></span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../dashboard/dashboard.php"><?php echo htmlspecialchars($translations[$language]['dashboard'] ?? 'Dashboard'); ?></a></li>
            <li><a href="../settings/settings.php"><?php echo htmlspecialchars($translations[$language]['title'] ?? 'Settings'); ?></a></li>
            <li><a href="../profil/profil.php"><?php echo htmlspecialchars($translations[$language]['profile'] ?? 'Profile'); ?></a></li>
            <li><a href="calendrier.php"class="active"><?php echo htmlspecialchars($translations[$language]['calendar'] ?? 'Calendar'); ?></a></li>
            <li><a href="../notifications/notifications.php"><?php echo htmlspecialchars($translations[$language]['Notifications'] ?? 'Notifications'); ?></a></li>
            <li><a href="../sign/logout.php"><?php echo htmlspecialchars($translations[$language]['logout'] ?? 'Logout'); ?></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="header">
            <h1><span>📅</span>
                <?php echo htmlspecialchars($translations[$language]['calendar'] ?? 'Calendar'); ?>
            </h1>
            <div class="user-profile">
                <div class="user-avatar">
                    <?php echo htmlspecialchars($userInitials); ?>
                </div>
                <span>
                    <?php echo htmlspecialchars($userName); ?>
                </span>
                <form method="POST" action="../settings/theme.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button type="submit" name="toggle_theme" class="btn btn-secondary theme-toggle">
                        <?php echo $theme === 'light' ? '🌙 Dark Mode' : '☀️ Light Mode'; ?>
                    </button>
                </form>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['action']) && $_GET['action'] === 'success'): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($translations[$language]['success_message'] ?? 'Action completed successfully!'); ?>
            </div>
        <?php elseif (isset($_GET['action']) && $_GET['action'] === 'error'): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($translations[$language]['error_message'] ?? 'Error during action execution.'); ?>
            </div>
        <?php endif; ?>

        <div class="calendar-container">
            <div class="calendar-header">
                <div class="calendar-nav">
                    <a href="calendrier.php?month=<?php echo $prevMonth->format('m'); ?>&year=<?php echo $prevMonth->format('Y'); ?>"
                       class="btn btn-primary">◀</a>
                    <a href="calendrier.php?month=<?php echo $nextMonth->format('m'); ?>&year=<?php echo $nextMonth->format('Y'); ?>"
                       class="btn btn-primary">▶</a>
                </div>
                <h2 class="calendar-month">
                    <?php echo htmlspecialchars($currentDate->format('F Y')); ?>
                </h2>
                <a href="calendrier.php" class="btn btn-primary">
                    <?php echo htmlspecialchars($translations[$language]['today'] ?? 'Today'); ?>
                </a>
            </div>

            <div class="calendar-weekdays">
                <div><?php echo $language === 'fr' ? 'Lun' : ($language === 'ar' ? 'الإثنين' : 'Mon'); ?></div>
                <div><?php echo $language === 'fr' ? 'Mar' : ($language === 'ar' ? 'الثلاثاء' : 'Tue'); ?></div>
                <div><?php echo $language === 'fr' ? 'Mer' : ($language === 'ar' ? 'الأربعاء' : 'Wed'); ?></div>
                <div><?php echo $language === 'fr' ? 'Jeu' : ($language === 'ar' ? 'الخميس' : 'Thu'); ?></div>
                <div><?php echo $language === 'fr' ? 'Ven' : ($language === 'ar' ? 'الجمعة' : 'Fri'); ?></div>
                <div><?php echo $language === 'fr' ? 'Sam' : ($language === 'ar' ? 'السبت' : 'Sat'); ?></div>
                <div><?php echo $language === 'fr' ? 'Dim' : ($language === 'ar' ? 'الأحد' : 'Sun'); ?></div>
            </div>

            <div class="calendar-grid">
                <?php
                $firstDay = clone $currentDate;
                $firstDay->setDate($currentDate->format('Y'), $currentDate->format('m'), 1);
                $lastDay = clone $currentDate;
                $lastDay->setDate($currentDate->format('Y'), $currentDate->format('m'), $lastDay->format('t'));

                $startDay = $firstDay->format('N') - 1;
                $prevMonthLastDay = (clone $firstDay)->modify('-1 day')->format('d');

                for ($i = 0; $i < $startDay; $i++) {
                    $dayNumber = $prevMonthLastDay - $startDay + $i + 1;
                    echo '<div class="calendar-day day-other-month">';
                    echo '<div class="day-number">' . htmlspecialchars($dayNumber) . '</div>';
                    echo '</div>';
                }

                $totalDays = $lastDay->format('d');
                for ($i = 1; $i <= $totalDays; $i++) {
                    $dayDate = clone $currentDate;
                    $dayDate->setDate($currentDate->format('Y'), $currentDate->format('m'), $i);
                    $isToday = $isCurrentMonth && $i === (int)$today->format('d');
                    $dateStr = $dayDate->format('Y-m-d');

                    echo '<div class="calendar-day' . ($isToday ? ' day-today' : '') . '" data-date="' . htmlspecialchars($dateStr) . '">';
                    echo '<div class="day-number">' . htmlspecialchars($i) . '</div>';

                    $dayEvents = array_filter($events, function($event) use ($i, $currentDate) {
                        $eventDate = new DateTime($event['date']);
                        return $eventDate->format('d') == $i &&
                               $eventDate->format('m') == $currentDate->format('m') &&
                               $eventDate->format('Y') == $currentDate->format('Y');
                    });

                    foreach ($dayEvents as $event) {
                        $eventTime = $event['time'] ? htmlspecialchars($event['time']) : '';
                        $eventText = $eventTime ? "$eventTime - " . htmlspecialchars($event['title']) : htmlspecialchars($event['title']);
                        echo '<a href="edit_event.php?id=' . htmlspecialchars($event['id']) . '" class="day-event" style="background-color: ' . htmlspecialchars($event['color']) . ';">';
                        echo $eventText;
                        echo '</a>';
                    }

                    echo '</div>';
                }

                $remainingDays = (7 - (($startDay + $totalDays) % 7)) % 7;
                for ($i = 1; $i <= $remainingDays; $i++) {
                    echo '<div class="calendar-day day-other-month">';
                    echo '<div class="day-number">' . htmlspecialchars($i) . '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <a href="#" class="add-event" id="open-add-event-modal">+</a>

        <div id="add-event-modal" class="modal-overlay" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>
                        <?php echo htmlspecialchars($translations[$language]['new_event'] ?? 'New Event'); ?>
                    </h2>
                    <a href="#" class="close-modal" id="close-add-event-modal">×</a>
                </div>
                <form method="POST" action="save_event.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="date" id="event-date" value="<?php echo htmlspecialchars($currentDate->format('Y-m-d')); ?>">

                    <div class="form-group">
                        <label for="event-title">
                            <?php echo htmlspecialchars($translations[$language]['task_title_label'] ?? 'Title'); ?>*
                        </label>
                        <input type="text" id="event-title" name="title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="event-description">
                            <?php echo htmlspecialchars($translations[$language]['description'] ?? 'Description'); ?>
                        </label>
                        <textarea id="event-description" name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="event-time">
                            <?php echo htmlspecialchars($translations[$language]['time'] ?? 'Time'); ?>
                        </label>
                        <input type="time" id="event-time" name="time" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="event-color">
                            <?php echo htmlspecialchars($translations[$language]['color'] ?? 'Color'); ?>
                        </label>
                        <select id="event-color" name="color" class="form-control">
                            <option value="#8E44AD">
                                <?php echo htmlspecialchars($translations[$language]['purple'] ?? 'Purple'); ?>
                            </option>
                            <option value="#9B59B6">
                                <?php echo htmlspecialchars($translations[$language]['light_purple'] ?? 'Light Purple'); ?>
                            </option>
                            <option value="#2ECC71">
                                <?php echo htmlspecialchars($translations[$language]['green'] ?? 'Green'); ?>
                            </option>
                            <option value="#3498DB">
                                <?php echo htmlspecialchars($translations[$language]['blue'] ?? 'Blue'); ?>
                            </option>
                            <option value="#E74C3C">
                                <?php echo htmlspecialchars($translations[$language]['red'] ?? 'Red'); ?>
                            </option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <a href="#" class="btn btn-secondary" id="cancel-add-event">
                            <?php echo htmlspecialchars($translations[$language]['cancel_button'] ?? 'Cancel'); ?>
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <?php echo htmlspecialchars($translations[$language]['save_button'] ?? 'Save'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="day-events-modal" class="modal-overlay" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="day-events-title">
                        <?php echo htmlspecialchars($translations[$language]['events'] ?? 'Events'); ?>
                    </h2>
                    <a href="#" class="close-modal" id="close-day-events-modal">×</a>
                </div>
                <div id="day-events-list" class="events-list" style="padding: 1rem; max-height: 400px; overflow-y: auto;"></div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-secondary" id="close-day-events">
                        <?php echo htmlspecialchars($translations[$language]['cancel_button'] ?? 'Cancel'); ?>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        const openAddEventModal = document.getElementById('open-add-event-modal');
        const addEventModal = document.getElementById('add-event-modal');
        const closeAddEventModal = document.getElementById('close-add-event-modal');
        const cancelAddEvent = document.getElementById('cancel-add-event');
        const eventDateInput = document.getElementById('event-date');

        const dayEventsModal = document.getElementById('day-events-modal');
        const closeDayEventsModal = document.getElementById('close-day-events-modal');
        const closeDayEvents = document.getElementById('close-day-events');
        const dayEventsList = document.getElementById('day-events-list');
        const dayEventsTitle = document.getElementById('day-events-title');

        const translations = <?php echo json_encode($translations[$language]); ?>;
        const csrfToken = '<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>';
        const userId = '<?php echo htmlspecialchars($user_id); ?>';

        // Ouvre le modal pour ajouter un événement
        openAddEventModal.addEventListener('click', (e) => {
            e.preventDefault();
            addEventModal.style.display = 'flex';
            dayEventsModal.style.display = 'none';
        });

        // Ferme le modal d'ajout
        closeAddEventModal.addEventListener('click', (e) => {
            e.preventDefault();
            addEventModal.style.display = 'none';
        });

        cancelAddEvent.addEventListener('click', (e) => {
            e.preventDefault();
            addEventModal.style.display = 'none';
        });

        // Ferme le modal des événements du jour
        closeDayEventsModal.addEventListener('click', (e) => {
            e.preventDefault();
            dayEventsModal.style.display = 'none';
        });

        closeDayEvents.addEventListener('click', (e) => {
            e.preventDefault();
            dayEventsModal.style.display = 'none';
        });

        // Gère le clic sur les jours du calendrier
        document.querySelectorAll('.calendar-day:not(.day-other-month)').forEach(day => {
            day.addEventListener('click', (e) => {
                if (e.target.classList.contains('day-event')) return;

                const date = day.getAttribute('data-date');

                // Met à jour le titre du modal
                const dateObj = new Date(date);
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                dayEventsTitle.textContent = `${translations.events || 'Events'} - ${dateObj.toLocaleDateString('<?php echo $language; ?>', options)}`;

                // Affiche un message de chargement
                dayEventsList.innerHTML = `<p style="color: #666;">${translations.loading || 'Loading...'}</p>`;

                // Requête AJAX pour récupérer les événements du jour
                fetch('get_day_events.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `date=${encodeURIComponent(date)}&user_id=${encodeURIComponent(userId)}&csrf_token=${encodeURIComponent(csrfToken)}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        dayEventsList.innerHTML = `<p style="color: #e74c3c;">${data.error}</p>`;
                        return;
                    }

                    const dayEvents = data.events;

                    if (dayEvents.length === 0) {
                        dayEventsList.innerHTML = `<p style="color: #666;">${translations.no_events || 'No events for this day'}</p>`;
                    } else {
                        dayEventsList.innerHTML = dayEvents.map(event => {
                            const time = event.time ? `${event.time} - ` : '';
                            const description = event.description ? `<p style="margin: 0.5rem 0; font-size: 0.9rem;">${event.description}</p>` : '';
                            return `
                                <div style="background-color: ${event.color}; color: white; padding: 1rem; margin-bottom: 0.5rem; border-radius: 4px;">
                                    <strong>${time}${event.title}</strong>
                                    ${description}
                                    <a href="edit_event.php?id=${event.id}" class="btn btn-primary" style="display: inline-block; margin-top: 0.5rem; color: white; text-decoration: none; padding: 0.3rem 0.6rem; border-radius: 4px;">
                                        ${translations.edit || 'Edit'}
                                    </a>
                                </div>
                            `;
                        }).join('');
                    }
                })
                .catch(error => {
                    dayEventsList.innerHTML = `<p style="color: #e74c3c;">${translations.error_loading || 'Error loading events'}</p>`;
                    console.error('Error:', error);
                });

                // Affiche le modal des événements et cache le modal d'ajout
                dayEventsModal.style.display = 'flex';
                addEventModal.style.display = 'none';
                eventDateInput.value = date;
            });
        });
    </script>
</body>
</html>