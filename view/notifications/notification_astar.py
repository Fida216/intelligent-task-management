import mysql.connector
from datetime import datetime, timedelta
import sys

# Connexion à la base de données
try:
    db = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="taskenuis"
    )
except mysql.connector.Error as err:
    print(f"Erreur de connexion à la base de données : {err}")
    sys.exit(1)

cursor = db.cursor(dictionary=True)

# Récupérer user_id depuis les arguments
if len(sys.argv) < 2:
    print("Erreur : user_id requis")
    cursor.close()
    db.close()
    sys.exit(1)

try:
    user_id = int(sys.argv[1])
except ValueError:
    print("Erreur : user_id doit être un entier")
    cursor.close()
    db.close()
    sys.exit(1)

# Vérifier si les notifications sont activées pour l'utilisateur
cursor.execute("SELECT notification_enabled FROM users WHERE id = %s", (user_id,))
user = cursor.fetchone()
if not user or not user['notification_enabled']:
    print("Notifications désactivées pour cet utilisateur")
    cursor.close()
    db.close()
    sys.exit(0)

# Lire l'historique des tâches pour l'utilisateur
cursor.execute("SELECT deadline, completed_at, status FROM tasks_history WHERE user_id = %s", (user_id,))
history = cursor.fetchall()

# Calculer le score utilisateur
score = 0
for task in history:
    deadline = task['deadline']
    completed_at = task['completed_at']
    status = task['status']

    if status == 'completed':
        if completed_at and completed_at <= deadline:
            score += 1
        else:
            score -= 1
    else:
        score -= 2  # failed ou cancelled

print(f"Score utilisateur = {score}")

# Récupérer les tâches actives
cursor.execute("SELECT id, name, deadline, importance FROM tasks WHERE status = 'pending' AND user_id = %s", (user_id,))
pending_tasks = cursor.fetchall()

def calculate_notification_time(deadline, score):
    """Déterminer combien de temps avant envoyer la notification."""
    if score >= 10:
        advance = timedelta(hours=1)
    elif 5 <= score <= 9:
        advance = timedelta(hours=3)
    elif 0 <= score <= 4:
        advance = timedelta(hours=6)
    else:
        advance = timedelta(hours=12)
    
    return deadline - advance

# Insérer les notifications
current_time = datetime.now()
for task in pending_tasks:
    best_time = calculate_notification_time(task['deadline'], score)
    if best_time > current_time:
        message = f"Rappel : Tâche '{task['name']}' à terminer avant {task['deadline'].strftime('%Y-%m-%d %H:%M:%S')}"
        try:
            cursor.execute(
                "INSERT INTO notifications (user_id, message, date_notification, est_vue) VALUES (%s, %s, %s, 0)",
                (user_id, message, best_time)
            )
            db.commit()
            print(f"Notification ajoutée pour la tâche : {task['name']} à {best_time.strftime('%Y-%m-%d %H:%M:%S')}")
        except mysql.connector.Error as err:
            print(f"Erreur lors de l'insertion de la notification pour {task['name']} : {err}")
    else:
        print(f"Notification ignorée pour la tâche : {task['name']} (heure passée : {best_time.strftime('%Y-%m-%d %H:%M:%S')})")

cursor.close()
db.close()