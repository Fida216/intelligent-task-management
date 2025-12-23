import mysql.connector
from datetime import datetime, timedelta

# Connexion à la base de données
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="taskenuis"
)

cursor = db.cursor(dictionary=True)

# 1. Lire l'historique des tâches
cursor.execute("SELECT deadline, completed_at, status FROM tasks_history")
history = cursor.fetchall()

# 2. Calculer le score utilisateur
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

# 3. Maintenant pour chaque tâche active, choisir le moment d'envoyer la notification
cursor.execute("SELECT id, name, deadline, importance FROM tasks WHERE status = 'pending'")
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

# 4. Afficher les meilleurs moments d'envoi
for task in pending_tasks:
    best_time = calculate_notification_time(task['deadline'], score)
    print(f"Tâche : {task['name']}")
    print(f"Envoyer notification à : {best_time.strftime('%Y-%m-%d %H:%M:%S')}")
    print("-" * 40)

cursor.close()
db.close()
