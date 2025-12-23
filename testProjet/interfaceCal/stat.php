<?php
require 'db.php';
require '../interfaceParam/translations.php'; // Include translations
require '../interfaceParam/theme.php'; // Include theme management
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch user data
try {
    $query = $pdo->prepare("SELECT name, surname FROM users WHERE id = ?");
    $query->execute([$_SESSION['user_id']]);
    $user = $query->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        header("Location: logout.php");
        exit;
    }
    $userName = $user['name'] . ' ' . $user['surname'];
    $userInitials = strtoupper(substr($user['name'], 0, 1) . substr($user['surname'], 0, 1));
} catch(PDOException $e) {
    $error = "Erreur lors de la récupération des données utilisateur : " . $e->getMessage();
}

// Determine the month and year to display
$currentDate = new DateTime();
if (isset($_GET['month']) && isset($_GET['year'])) {
    $currentDate->setDate($_GET['year'], $_GET['month'], 1);
}

// Fetch events for the current month and user
$firstDayOfMonth = clone $currentDate;
$firstDayOfMonth->setDate($currentDate->format('Y'), $currentDate->format('m'), 1);
$lastDayOfMonth = clone $currentDate;
$lastDayOfMonth->setDate($currentDate->format('Y'), $currentDate->format('m'), $lastDayOfMonth->format('t'));

try {
    $requete = $pdo->prepare("SELECT * FROM calendar_events WHERE user_id = :user_id AND date BETWEEN :start_date AND :end_date");
    $requete->execute([
        'user_id' => $_SESSION['user_id'],
        'start_date' => $firstDayOfMonth->format('Y-m-d'),
        'end_date' => $lastDayOfMonth->format('Y-m-d')
    ]);
    $events = $requete->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Erreur lors de la récupération des événements : " . $e->getMessage();
}

// Generate navigation links for months
$prevMonth = clone $currentDate;
$prevMonth->modify('-1 month');
$nextMonth = clone $currentDate;
$nextMonth->modify('+1 month');

$today = new DateTime();
$isCurrentMonth = $today->format('Y-m') === $currentDate->format('Y-m');

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($language); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?> | <?php echo htmlspecialchars($translations[$language]['calendar'] ?? 'Calendar'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style1.css">
</head>
<body data-theme="<?php echo htmlspecialchars(getTheme()); ?>">
    <aside class="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-logo"><?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?></span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../interfaceDash/f1.php"><?php echo htmlspecialchars($translations[$language]['dashboard'] ?? 'Dashboard'); ?></a></li>
            <li><a href="../interfaceParam/settings.php"><?php echo htmlspecialchars($translations[$language]['settings'] ?? 'Settings'); ?></a></li>
            <li><a href="../profil/profil.php"><?php echo htmlspecialchars($translations[$language]['profile'] ?? 'Profile'); ?></a></li>
            <li><a href="stat.php" class="active"><?php echo htmlspecialchars($translations[$language]['calendar'] ?? 'Calendar'); ?></a></li>
            <li><a href="../interfaceNptif/notifications.php"><?php echo htmlspecialchars($translations[$language]['Notifications'] ?? 'Notifications'); ?></a></li>
            <li><a href="../interfaceParam/logout.php"><?php echo htmlspecialchars($translations[$language]['logout'] ?? 'Logout'); ?></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="header">
            <h1><span>📅</span> <?php echo htmlspecialchars($translations[$language]['calendar'] ?? 'Calendar'); ?></h1>
            <div class="user-profile">
                <div class="user-avatar"><?php echo htmlspecialchars($userInitials); ?></div>
                <span><?php echo htmlspecialchars($userName); ?></span>
                <form method="POST" action="">
                    <button type="submit" name="toggle_theme" class="btn btn-secondary theme-toggle">
                        <?php echo getTheme() === 'light' ? '🌙 Dark Mode' : '☀️ Light Mode'; ?>
                    </button>
                </form>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['action']) && $_GET['action'] === 'success'): ?>
            <div class="success-message"><?php echo htmlspecialchars($translations[$language]['success_message'] ?? 'Action completed successfully!'); ?></div>
        <?php elseif (isset($_GET['action']) && $_GET['action'] === 'error'): ?>
            <div class="error-message"><?php echo htmlspecialchars($translations[$language]['error_message'] ?? 'Error during action execution.'); ?></div>
        <?php endif; ?>

        <div class="calendar-container">
            <div class="calendar-header">
                <div class="calendar-nav">
                    <a href="stat.php?month=<?php echo $prevMonth->format('m'); ?>&year=<?php echo $prevMonth->format('Y'); ?>" class="btn btn-primary">◀</a>
                    <a href="stat.php?month=<?php echo $nextMonth->format('m'); ?>&year=<?php echo $nextMonth->format('Y'); ?>" class="btn btn-primary">▶</a>
                </div>
                <h2 class="calendar-month"><?php echo $currentDate->format('F Y'); ?></h2>
                <a href="stat.php" class="btn btn-primary"><?php echo htmlspecialchars($translations[$language]['today'] ?? 'Today'); ?></a>
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
                        $eventTime = $event['time'] ? $event['time'] : '';
                        $eventText = $eventTime ? "$eventTime - {$event['title']}" : $event['title'];
                        echo '<a href="edit_event.php?id=' . htmlspecialchars($event['id']) . '" class="day-event" style="background-color: ' . htmlspecialchars($event['color']) . ';">';
                        echo htmlspecialchars($eventText);
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

        <div id="add-event-modal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><?php echo htmlspecialchars($translations[$language]['new_event'] ?? 'New Event'); ?></h2>
                    <a href="#" class="close-modal" id="close-add-event-modal">×</a>
                </div>
                <form method="POST" action="save_event.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="date" id="event-date" value="<?php echo $currentDate->format('Y-m-d'); ?>">
                    
                    <div class="form-group">
                        <label for="event-title"><?php echo htmlspecialchars($translations[$language]['task_title_label'] ?? 'Title'); ?>*</label>
                        <input type="text" id="event-title" name="title" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event-description"><?php echo htmlspecialchars($translations[$language]['description'] ?? 'Description'); ?></label>
                        <textarea id="event-description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="event-time"><?php echo htmlspecialchars($translations[$language]['time'] ?? 'Time'); ?></label>
                        <input type="time" id="event-time" name="time" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="event-color"><?php echo htmlspecialchars($translations[$language]['color'] ?? 'Color'); ?></label>
                        <select id="event-color" name="color" class="form-control">
                            <option value="#8E44AD"><?php echo htmlspecialchars($translations[$language]['purple'] ?? 'Purple'); ?></option>
                            <option value="#9B59B6"><?php echo htmlspecialchars($translations[$language]['light_purple'] ?? 'Light Purple'); ?></option>
                            <option value="#2ECC71"><?php echo htmlspecialchars($translations[$language]['green'] ?? 'Green'); ?></option>
                            <option value="#3498DB"><?php echo htmlspecialchars($translations[$language]['blue'] ?? 'Blue'); ?></option>
                            <option value="#E74C3C"><?php echo htmlspecialchars($translations[$language]['red'] ?? 'Red'); ?></option>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <a href="#" class="btn btn-secondary" id="cancel-add-event"><?php echo htmlspecialchars($translations[$language]['cancel_button'] ?? 'Cancel'); ?></a>
                        <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($translations[$language]['save_button'] ?? 'Save'); ?></button>
                    </div>
                </form>
            </div>
        </div>

        <div id="day-events-modal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="day-events-title"><?php echo htmlspecialchars($translations[$language]['events'] ?? 'Events'); ?></h2>
                    <a href="#" class="close-modal" id="close-day-events-modal">×</a>
                </div>
                <div id="day-events-list" class="events-list"></div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-secondary" id="close-day-events"><?php echo htmlspecialchars($translations[$language]['cancel_button'] ?? 'Cancel'); ?></a>
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

        openAddEventModal.addEventListener('click', (e) => {
            e.preventDefault();
            addEventModal.style.display = 'flex';
            dayEventsModal.style.display = 'none';
        });

        closeAddEventModal.addEventListener('click', (e) => {
            e.preventDefault();
            addEventModal.style.display = 'none';
        });

        cancelAddEvent.addEventListener('click', (e) => {
            e.preventDefault();
            addEventModal.style.display = 'none';
        });

        closeDayEventsModal.addEventListener('click', (e) => {
            e.preventDefault();
            dayEventsModal.style.display = 'none';
        });

        closeDayEvents.addEventListener('click', (e) => {
            e.preventDefault();
            dayEventsModal.style.display = 'none';
        });

        const events = <?php echo json_encode($events); ?>;
        const translations = <?php echo json_encode($translations[$language]); ?>;

        document.querySelectorAll('.calendar-day:not(.day-other-month)').forEach(day => {
            day.addEventListener('click', (e) => {
                if (e.target.classList.contains('day-event')) return;

                const date = day.getAttribute('data-date');
                const dayEvents = events.filter(event => event.date === date);

                const dateObj = new Date(date);
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                dayEventsTitle.textContent = `${translations.events} ${dateObj.toLocaleDateString('<?php echo $language; ?>', options)}`;

                if (dayEvents.length === 0) {
                    dayEventsList.innerHTML = `<p>${translations.no_events}</p>`;
                } else {
                    dayEventsList.innerHTML = dayEvents.map(event => {
                        const time = event.time ? `${event.time} - ` : '';
                        return `
                            <div style="background-color: ${event.color}; color: white; padding: 0.5rem; margin-bottom: 0.5rem; border-radius: 4px;">
                                <strong>${time}${event.title}</strong>
                                ${event.description ? `<p>${event.description}</p>` : ''}
                                <a href="edit_event.php?id=${event.id}" class="btn btn-primary" style="display: inline-block; margin-top: 0.5rem;">${translations.edit}</a>
                            </div>
                        `;
                    }).join('');
                }

                dayEventsModal.style.display = 'flex';
                addEventModal.style.display = 'none';
                eventDateInput.value = date;
            });
        });
    </script>
</body>
</html>