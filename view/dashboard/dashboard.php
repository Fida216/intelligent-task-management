<?php
require '../../auth.php'; 
require '../../config/db.php';
require '../../controller/translations.php';

// Définir le fuseau horaire
date_default_timezone_set('America/New_York'); 

// Utiliser les données de la session
$user_id = (int)$_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['name'] . ' ' . $_SESSION['surname']);
$userInitials = strtoupper(substr($_SESSION['name'], 0, 1) . substr($_SESSION['surname'], 0, 1));
$language = isset($_SESSION['language']) && in_array($_SESSION['language'], ['en', 'fr', 'ar']) ? $_SESSION['language'] : 'en';
$theme = htmlspecialchars($_SESSION['theme']);

// Chemin vers Python (configurable via une variable d'environnement)
$python_path = getenv('PYTHON_PATH') ?: 'C:\wamp64\www\finalement\view\dashboard\search_tasks.py';

// Messages de succès ou d'erreur
$success_message = isset($_GET['action']) && $_GET['action'] === 'success' ? htmlspecialchars($translations[$language]['success_message']) : '';
$error_message = isset($_GET['action']) && $_GET['action'] === 'error' ? htmlspecialchars($translations[$language]['error_message']) : '';

// Initialiser les tâches
$tasks = [];
$error = null;

// Gérer la recherche
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) && in_array($_GET['category'], ['work', 'personal', 'shopping', '']) ? $_GET['category'] : '';
if ($search_term || $category) {
    // Échapper les entrées pour la commande shell
    $safe_search = escapeshellarg($search_term);
    $safe_category = escapeshellarg($category);
    $safe_user_id = escapeshellarg($user_id);
    
    // Construire la commande
    $command = "\"$python_path\" search_tasks.py $safe_search $safe_category $safe_user_id 2>&1";
    $output = shell_exec($command);
    
    if ($output === null) {
        $error = sprintf(htmlspecialchars($translations[$language]['python_execution_error']), htmlspecialchars($python_path));
    } else {
        $result = json_decode($output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            file_put_contents('python_output.log', $output);
            $error = htmlspecialchars($translations[$language]['python_json_error']);
        } elseif (isset($result['error'])) {
            $error = sprintf(htmlspecialchars($translations[$language]['search_error']), htmlspecialchars($result['error']));
        } elseif (isset($result['tasks'])) {
            $tasks = $result['tasks'];
        } else {
            $error = htmlspecialchars($translations[$language]['search_unexpected_error']);
        }
    }
} else {
    // Récupérer les tâches de l'utilisateur
    try {
        $requete = $pdo->prepare("SELECT * FROM tasks2 WHERE user_id = ?");
        $requete->execute([$user_id]);
        $tasks = $requete->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des tâches : " . $e->getMessage());
        $error = sprintf(htmlspecialchars($translations[$language]['tasks_fetch_error']), htmlspecialchars($e->getMessage()));
    }
}

// Appliquer le tri
$sort = isset($_GET['sort']) && in_array($_GET['sort'], ['priority', 'deadline']) ? $_GET['sort'] : 'none';
if ($sort === 'priority') {
    usort($tasks, function($a, $b) {
        return $a['priority'] <=> $b['priority'];
    });
} elseif ($sort === 'deadline') {
    usort($tasks, function($a, $b) {
        return strtotime($a['deadline']) <=> strtotime($b['deadline']);
    });
}

// Appliquer le filtrage
$filter = isset($_GET['filter']) && in_array($_GET['filter'], ['late', 'on-time', 'completed', 'not-completed', 'work', 'personal', 'shopping']) ? $_GET['filter'] : '';
if ($filter && !($category && in_array($filter, ['work', 'personal', 'shopping']))) {
    $filtered_tasks = [];
    foreach ($tasks as $task) {
        $is_late = new DateTime($task['deadline']) < new DateTime() && $task['status'] !== 'terminée';
        switch ($filter) {
            case 'late':
                if ($is_late) $filtered_tasks[] = $task;
                break;
            case 'on-time':
                if (!$is_late || $task['status'] === 'terminée') $filtered_tasks[] = $task;
                break;
            case 'completed':
                if ($task['status'] === 'terminée') $filtered_tasks[] = $task;
                break;
            case 'not-completed':
                if ($task['status'] !== 'terminée') $filtered_tasks[] = $task;
                break;
            case 'work':
            case 'personal':
            case 'shopping':
                if ($task['category'] === $filter) $filtered_tasks[] = $task;
                break;
        }
    }
    $tasks = $filtered_tasks;
}

// Récupérer une tâche pour modification
$task_to_edit = null;
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    try {
        $requete = $pdo->prepare("SELECT * FROM tasks2 WHERE id = :id AND user_id = :user_id");
        $requete->execute(['id' => (int)$_GET['edit_id'], 'user_id' => $user_id]);
        $task_to_edit = $requete->fetch(PDO::FETCH_ASSOC);
        if (!$task_to_edit) {
            $error_message = htmlspecialchars($translations[$language]['task_not_found']);
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération de la tâche : " . $e->getMessage());
        $error_message = sprintf(htmlspecialchars($translations[$language]['task_fetch_error']), htmlspecialchars($e->getMessage()));
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($language); ?>" dir="<?php echo $language === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?> | <?php echo htmlspecialchars($translations[$language]['dashboard'] ?? 'Dashboard'); ?></title>
    <link rel="stylesheet" href="../../public/css/dashboard.css">
</head>
<body data-theme="<?php echo htmlspecialchars($theme); ?>">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-logo"><?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?></span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><?php echo htmlspecialchars($translations[$language]['dashboard'] ?? 'Dashboard'); ?></a></li>
            <li><a href="../settings/settings.php"><?php echo htmlspecialchars($translations[$language]['title'] ?? 'Settings'); ?></a></li>
            <li><a href="../profil/profil.php"><?php echo htmlspecialchars($translations[$language]['profile'] ?? 'Profile'); ?></a></li>
            <li><a href="../calendrier/calendrier.php"><?php echo htmlspecialchars($translations[$language]['calendar'] ?? 'Calendar'); ?></a></li>
            <li><a href="../notifications/notifications.php"><?php echo htmlspecialchars($translations[$language]['Notifications'] ?? 'Notifications'); ?></a></li>
            <li><a href="../sign/logout.php"><?php echo htmlspecialchars($translations[$language]['logout'] ?? 'Logout'); ?></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div id="dashboard-view">
            <div class="header">
                <div class="header-title">
                    <h1><?php echo htmlspecialchars($translations[$language]['app_name'] ?? 'Taskenuis'); ?></h1>
                    <div class="current-date"><?php echo date('Y-m-d'); ?></div>
                </div>
                <div class="user-profile">
                    <div class="user-avatar"><?php echo htmlspecialchars($userInitials); ?></div>
                    <span><?php echo htmlspecialchars($userName); ?></span>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($success_message): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Recherche, tri et boutons -->
            <div class="task-input-container">
                <div class="search-container">
                    <form method="GET" action="dashboard.php" class="task-input-form">
                        <input type="text" name="search" class="task-input" placeholder="<?php echo htmlspecialchars($translations[$language]['search_placeholder'] ?? 'Search tasks...'); ?>" value="<?php echo htmlspecialchars($search_term); ?>">
                        <select name="category" class="task-category-select">
                            <option value="" <?php echo $category === '' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['all_categories'] ?? 'All Categories'); ?></option>
                            <option value="work" <?php echo $category === 'work' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['category_work'] ?? 'Work'); ?></option>
                            <option value="personal" <?php echo $category === 'personal' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['category_personal'] ?? 'Personal'); ?></option>
                            <option value="shopping" <?php echo $category === 'shopping' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['category_shopping'] ?? 'Shopping'); ?></option>
                        </select>
                        <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($translations[$language]['search_button'] ?? 'Search'); ?></button>
                    </form>
                </div>
                <div class="sort-filter-container">
                    <form method="GET" action="dashboard.php" class="sort-form">
                        <select name="sort">
                            <option value="none" <?php echo $sort === 'none' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['sort_none'] ?? 'No Sorting'); ?></option>
                            <option value="priority" <?php echo $sort === 'priority' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['sort_priority'] ?? 'Sort by Priority'); ?></option>
                            <option value="deadline" <?php echo $sort === 'deadline' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['sort_deadline'] ?? 'Sort by Deadline'); ?></option>
                        </select>
                        <select name="filter">
                            <option value="" <?php echo $filter === '' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['filter_all'] ?? 'All Tasks'); ?></option>
                            <option value="late" <?php echo $filter === 'late' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['filter_late'] ?? 'Late'); ?></option>
                            <option value="on-time" <?php echo $filter === 'on-time' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['filter_on_time'] ?? 'On Time'); ?></option>
                            <option value="completed" <?php echo $filter === 'completed' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['filter_completed'] ?? 'Completed'); ?></option>
                            <option value="not-completed" <?php echo $filter === 'not-completed' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['filter_not_completed'] ?? 'Not Completed'); ?></option>
                            <option value="work" <?php echo $filter === 'work' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['category_work'] ?? 'Work'); ?></option>
                            <option value="personal" <?php echo $filter === 'personal' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['category_personal'] ?? 'Personal'); ?></option>
                            <option value="shopping" <?php echo $filter === 'shopping' ? 'selected' : ''; ?>><?php echo htmlspecialchars($translations[$language]['category_shopping'] ?? 'Shopping'); ?></option>
                        </select>
                        <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($translations[$language]['apply_button'] ?? 'Apply'); ?></button>
                    </form>
                </div>
                <div class="add-task-container">
                    <button type="button" class="add-task-btn" onclick="openModal('add')"><?php echo htmlspecialchars($translations[$language]['add_task'] ?? 'Add Task'); ?></button>
                </div>
            </div>

            <!-- Task List -->
            <div class="task-list" id="task-list">
                <?php if (!empty($tasks)): ?>
                    <?php foreach ($tasks as $index => $task): ?>
                        <?php
                        $late = new DateTime($task['deadline']) < new DateTime() && $task['status'] !== 'terminée';
                        $statusBadge = $task['status'] === 'terminée' ? htmlspecialchars($translations[$language]['status_completed']) : htmlspecialchars($translations[$language]['status_in_progress']);
                        $statusClass = $task['status'] === 'terminée' ? 'status-completed' : 'status-in-progress';
                        $lateClass = $late ? 'late' : '';
                        $priorityClass = $task['priority'] == 1 ? 'high' : ($task['priority'] == 2 ? 'medium' : 'low');
                        $categoryDisplay = $task['category'] === 'work' ? htmlspecialchars($translations[$language]['category_work']) :
                                         ($task['category'] === 'personal' ? htmlspecialchars($translations[$language]['category_personal']) :
                                         htmlspecialchars($translations[$language]['category_shopping']));
                        ?>
                        <div class="task-card" id="task-<?php echo htmlspecialchars($task['id']); ?>">
                            <div class="task-id">#<?php echo $index + 1; ?></div>
                            <div class="task-main-info">
                                <h3 class="task-title">
                                    <span class="priority-circle <?php echo $priorityClass; ?>"></span>
                                    <?php echo htmlspecialchars($task['title']); ?>
                                </h3>
                                <p class="task-description"><?php echo htmlspecialchars($task['description'] ?? ''); ?></p>
                                <div class="task-meta">
                                    <span class="task-meta-item">
                                        <span class="task-meta-label"><?php echo htmlspecialchars($translations[$language]['deadline_label'] ?? 'Deadline'); ?></span>
                                        <span class="task-meta-value <?php echo $lateClass; ?>">
                                            <?php echo htmlspecialchars($task['deadline']); ?>
                                            <?php if ($late) echo ' ' . htmlspecialchars($translations[$language]['late_indicator'] ?? 'Late'); ?>
                                        </span>
                                    </span>
                                    <span class="task-meta-item">
                                        <span class="task-meta-label"><?php echo htmlspecialchars($translations[$language]['duration_label'] ?? 'Duration'); ?></span>
                                        <span class="task-meta-value"><?php echo htmlspecialchars($task['duration']); ?></span>
                                    </span>
                                </div>
                                <div class="task-meta">
                                    <span class="task-meta-item">
                                        <span class="task-meta-label"><?php echo htmlspecialchars($translations[$language]['category_label'] ?? 'Category'); ?></span>
                                        <span class="task-category-badge <?php echo htmlspecialchars($task['category']); ?>">
                                            <?php echo htmlspecialchars($categoryDisplay); ?>
                                        </span>
                                    </span>
                                </div>
                            </div>
                            <div class="task-actions">
                                <span class="task-status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusBadge); ?></span>
                                <div class="task-actions-buttons">
                                    <button type="button" onclick="openModal('edit', <?php echo htmlspecialchars($task['id']); ?>)">✏️</button>
                                    <form action="delete_task.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($task['id']); ?>">
                                        <button type="submit">🗑️</button>
                                    </form>
                                    <form action="update_status.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($task['id']); ?>">
                                        <input type="hidden" name="status" value="<?php echo $task['status'] === 'terminée' ? 'en cours' : 'terminée'; ?>">
                                        <button type="submit"><?php echo $task['status'] === 'terminée' ? '❌' : '✅'; ?></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><?php echo htmlspecialchars($translations[$language]['no_tasks'] ?? 'No tasks found'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal pour ajouter une tâche -->
        <div id="add-task-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><?php echo htmlspecialchars($translations[$language]['add_task_title'] ?? 'Add Task'); ?></h2>
                    <span class="close-modal" onclick="closeModal('add')">×</span>
                </div>
                <div class="modal-body">
                    <form action="save_task.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="form-group">
                            <label for="taskTitle"><?php echo htmlspecialchars($translations[$language]['task_title_label'] ?? 'Title'); ?></label>
                            <input type="text" id="taskTitle" name="taskTitle" required placeholder="<?php echo htmlspecialchars($translations[$language]['task_title_placeholder'] ?? 'Enter task title'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="taskDescription"><?php echo htmlspecialchars($translations[$language]['task_description_label'] ?? 'Description'); ?></label>
                            <textarea id="taskDescription" name="taskDescription" rows="3" placeholder="<?php echo htmlspecialchars($translations[$language]['task_description_placeholder'] ?? 'Enter task description'); ?>"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="taskDeadline"><?php echo htmlspecialchars($translations[$language]['task_deadline_label'] ?? 'Deadline'); ?></label>
                                <input type="date" id="taskDeadline" name="taskDeadline" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="taskDuration"><?php echo htmlspecialchars($translations[$language]['task_duration_label'] ?? 'Duration'); ?></label>
                                <input type="text" id="taskDuration" name="taskDuration" required placeholder="<?php echo htmlspecialchars($translations[$language]['task_duration_placeholder'] ?? 'e.g., 2 hours'); ?>">
                            </div>
                            <div class="form-group">
                                <label><?php echo htmlspecialchars($translations[$language]['task_priority_label'] ?? 'Priority'); ?></label>
                                <div class="priority-options">
                                    <label class="priority-option high">
                                        <input type="radio" name="taskPriority" value="1" required>
                                        <span><?php echo htmlspecialchars($translations[$language]['priority_high'] ?? 'High'); ?></span>
                                    </label>
                                    <label class="priority-option medium">
                                        <input type="radio" name="taskPriority" value="2">
                                        <span><?php echo htmlspecialchars($translations[$language]['priority_medium'] ?? 'Medium'); ?></span>
                                    </label>
                                    <label class="priority-option low">
                                        <input type="radio" name="taskPriority" value="3" checked>
                                        <span><?php echo htmlspecialchars($translations[$language]['priority_low'] ?? 'Low'); ?></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label><?php echo htmlspecialchars($translations[$language]['task_category_label'] ?? 'Category'); ?></label>
                                <div class="category-options">
                                    <label class="category-option work">
                                        <input type="radio" name="taskCategory" value="work" required>
                                        <span><?php echo htmlspecialchars($translations[$language]['category_work'] ?? 'Work'); ?></span>
                                    </label>
                                    <label class="category-option personal">
                                        <input type="radio" name="taskCategory" value="personal">
                                        <span><?php echo htmlspecialchars($translations[$language]['category_personal'] ?? 'Personal'); ?></span>
                                    </label>
                                    <label class="category-option shopping">
                                        <input type="radio" name="taskCategory" value="shopping" checked>
                                        <span><?php echo htmlspecialchars($translations[$language]['category_shopping'] ?? 'Shopping'); ?></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('add')"><?php echo htmlspecialchars($translations[$language]['cancel_button'] ?? 'Cancel'); ?></button>
                            <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($translations[$language]['save_button'] ?? 'Save'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal pour modifier une tâche -->
        <div id="edit-task-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><?php echo htmlspecialchars($translations[$language]['edit_task_title'] ?? 'Edit Task'); ?></h2>
                    <span class="close-modal" onclick="closeModal('edit')">×</span>
                </div>
                <div class="modal-body">
                    <?php if ($task_to_edit): ?>
                        <form action="update_task.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($task_to_edit['id']); ?>">
                            <div class="form-group">
                                <label for="taskTitle"><?php echo htmlspecialchars($translations[$language]['task_title_label'] ?? 'Title'); ?></label>
                                <input type="text" id="taskTitle" name="taskTitle" required value="<?php echo htmlspecialchars($task_to_edit['title']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="taskDescription"><?php echo htmlspecialchars($translations[$language]['task_description_label'] ?? 'Description'); ?></label>
                                <textarea id="taskDescription" name="taskDescription" rows="3"><?php echo htmlspecialchars($task_to_edit['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="taskDeadline"><?php echo htmlspecialchars($translations[$language]['task_deadline_label'] ?? 'Deadline'); ?></label>
                                    <input type="date" id="taskDeadline" name="taskDeadline" required value="<?php echo htmlspecialchars($task_to_edit['deadline']); ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="taskDuration"><?php echo htmlspecialchars($translations[$language]['task_duration_label'] ?? 'Duration'); ?></label>
                                    <input type="text" id="taskDuration" name="taskDuration" required value="<?php echo htmlspecialchars($task_to_edit['duration']); ?>">
                                </div>
                                <div class="form-group">
                                    <label><?php echo htmlspecialchars($translations[$language]['task_priority_label'] ?? 'Priority'); ?></label>
                                    <div class="priority-options">
                                        <label class="priority-option high">
                                            <input type="radio" name="taskPriority" value="1" <?php echo $task_to_edit['priority'] == 1 ? 'checked' : ''; ?> required>
                                            <span><?php echo htmlspecialchars($translations[$language]['priority_high'] ?? 'High'); ?></span>
                                        </label>
                                        <label class="priority-option medium">
                                            <input type="radio" name="taskPriority" value="2" <?php echo $task_to_edit['priority'] == 2 ? 'checked' : ''; ?>>
                                            <span><?php echo htmlspecialchars($translations[$language]['priority_medium'] ?? 'Medium'); ?></span>
                                        </label>
                                        <label class="priority-option low">
                                            <input type="radio" name="taskPriority" value="3" <?php echo $task_to_edit['priority'] == 3 ? 'checked' : ''; ?>>
                                            <span><?php echo htmlspecialchars($translations[$language]['priority_low'] ?? 'Low'); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label><?php echo htmlspecialchars($translations[$language]['task_category_label'] ?? 'Category'); ?></label>
                                    <div class="category-options">
                                        <label class="category-option work">
                                            <input type="radio" name="taskCategory" value="work" <?php echo $task_to_edit['category'] === 'work' ? 'checked' : ''; ?> required>
                                            <span><?php echo htmlspecialchars($translations[$language]['category_work'] ?? 'Work'); ?></span>
                                        </label>
                                        <label class="category-option personal">
                                            <input type="radio" name="taskCategory" value="personal" <?php echo $task_to_edit['category'] === 'personal' ? 'checked' : ''; ?>>
                                            <span><?php echo htmlspecialchars($translations[$language]['category_personal'] ?? 'Personal'); ?></span>
                                        </label>
                                        <label class="category-option shopping">
                                            <input type="radio" name="taskCategory" value="shopping" <?php echo $task_to_edit['category'] === 'shopping' ? 'checked' : ''; ?>>
                                            <span><?php echo htmlspecialchars($translations[$language]['category_shopping'] ?? 'Shopping'); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" onclick="closeModal('edit')"><?php echo htmlspecialchars($translations[$language]['cancel_button'] ?? 'Cancel'); ?></button>
                                <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($translations[$language]['save_button'] ?? 'Save'); ?></button>
                            </div>
                        </form>
                    <?php else: ?>
                        <p><?php echo htmlspecialchars($translations[$language]['no_task_selected'] ?? 'No task selected'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function openModal(type, taskId = null) {
            if (type === 'add') {
                document.getElementById('add-task-modal').classList.add('active');
            } else if (type === 'edit' && taskId) {
                window.location.href = 'dashboard.php?edit_id=' + encodeURIComponent(taskId);
            }
        }

        function closeModal(type) {
            if (type === 'add') {
                document.getElementById('add-task-modal').classList.remove('active');
            } else if (type === 'edit') {
                window.location.href = 'dashboard.php';
            }
        }

        // Ouvrir automatiquement le modal de modification si edit_id est présent
        <?php if (isset($_GET['edit_id']) && $task_to_edit): ?>
            document.getElementById('edit-task-modal').classList.add('active');
        <?php endif; ?>
    </script>
</body>
</html>