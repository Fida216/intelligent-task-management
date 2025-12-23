import sys
import mysql.connector

# 📌 Récupérer les arguments passés depuis PHP (nom de la tâche et priorité)
if len(sys.argv) != 3:
    print("Usage: python notification_astar.py <task_name> <priority>")
    sys.exit(1)

task_name = sys.argv[1]
priority = sys.argv[2]

# 📌 Connexion à ta base de données
try:
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",    # Mets ton mot de passe MySQL si besoin
        database="taskenuis"
    )
    cursor = conn.cursor()
except mysql.connector.Error as err:
    print(f"Erreur de connexion à MySQL: {err}")
    sys.exit(1)

# 📌 Lire les tâches existantes depuis la table tasks2
try:
    cursor.execute("SELECT deadline, completed_at, status FROM tasks2")
    tasks = cursor.fetchall()
except mysql.connector.Error as err:
    print(f"Erreur lors de la récupération des tâches: {err}")
    conn.close()
    sys.exit(1)

# 📌 Simuler l'algorithme A* pour suggérer une notification intelligente
def astar(tasks, new_task_name, priority):
    # C'est une version simple. Tu peux améliorer l'heuristique si tu veux !
    best_task = None
    best_score = float('inf')

    for task in tasks:
        deadline, completed_at, status = task

        # Heuristique simple : les tâches non terminées et avec une deadline proche sont prioritaires
        score = 0
        if status != "completed":
            score += 10
        if deadline:
            score += 5

        if score < best_score:
            best_score = score
            best_task = task

    if best_task:
        print(f"[Notification IA] Nouvelle tâche '{new_task_name}' de priorité {priority}. Considérez de la faire rapidement.")
    else:
        print(f"[Notification IA] Aucune tâche existante critique. Vous pouvez commencer '{new_task_name}' tranquillement.")

# 📌 Appeler la fonction
astar(tasks, task_name, priority)

# 📌 Fermer la connexion
conn.close()

