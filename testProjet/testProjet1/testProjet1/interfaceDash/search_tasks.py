import mysql.connector
import json
import sys
import re
import traceback

try:
    from sklearn.feature_extraction.text import TfidfVectorizer
    from sklearn.metrics.pairwise import cosine_similarity
except ImportError as e:
    with open('python_debug.log', 'a') as f:
        f.write(f"Missing required library: {str(e)}\n")
    print(json.dumps({"error": f"Missing required library: {str(e)}. Please install scikit-learn."}))
    sys.exit(1)

def connect_to_db():
    try:
        connection = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="taskenuis"
        )
        return connection
    except mysql.connector.Error as err:
        with open('python_debug.log', 'a') as f:
            f.write(f"Database connection failed: {str(err)}\n")
        return {"error": f"Database connection failed: {err}"}

def clean_text(text):
    # Nettoyer le texte : convertir en minuscules et supprimer la ponctuation
    text = text.lower()
    text = re.sub(r'[^\w\s]', '', text)
    return text

def search_tasks(search_term, category):
    try:
        connection = connect_to_db()
        if "error" in connection:
            return connection

        cursor = connection.cursor(dictionary=True)
        query = "SELECT id, title, description, deadline, duration, priority, category, status FROM tasks2"
        cursor.execute(query)
        tasks = cursor.fetchall()

        # Fermer la connexion
        cursor.close()
        connection.close()

        if not tasks:
            return {"tasks": []}

        # Préparer les documents pour TF-IDF
        documents = []
        task_list = []
        for task in tasks:
            # Combiner titre et description pour le document
            text = f"{task['title']} {task['description'] or ''}"
            documents.append(clean_text(text))
            task_list.append(task)

        # Filtrer par catégorie si spécifié
        if category and category in ['work', 'personal', 'shopping']:
            task_list = [task for task in task_list if task['category'] == category]
            documents = [clean_text(f"{task['title']} {task['description'] or ''}") for task in task_list]

        if not task_list:
            return {"tasks": []}

        # Appliquer TF-IDF
        if search_term:
            search_term = clean_text(search_term)
            vectorizer = TfidfVectorizer()
            tfidf_matrix = vectorizer.fit_transform(documents + [search_term])
            scores = cosine_similarity(tfidf_matrix[-1], tfidf_matrix[:-1]).flatten()

            # Associer les scores aux tâches
            for i, task in enumerate(task_list):
                task['score'] = scores[i]

            # Trier les tâches par score décroissant
            task_list = sorted(task_list, key=lambda x: x['score'], reverse=True)
            # Filtrer les tâches avec un score > 0
            task_list = [task for task in task_list if task['score'] > 0]
        else:
            # Si aucun terme de recherche, retourner toutes les tâches de la catégorie
            for task in task_list:
                task['score'] = 1.0

        # Supprimer le champ score avant de retourner les résultats
        for task in task_list:
            del task['score']

        return {"tasks": task_list}
    except Exception as e:
        with open('python_debug.log', 'a') as f:
            f.write(f"Search failed: {str(e)}\n{traceback.format_exc()}\n")
        return {"error": f"Search failed: {str(e)}"}

if __name__ == "__main__":
    try:
        # Lire les arguments de la ligne de commande
        search_term = sys.argv[1] if len(sys.argv) > 1 else ""
        category = sys.argv[2] if len(sys.argv) > 2 else ""
        
        # Effectuer la recherche
        result = search_tasks(search_term, category)
        
        # Retourner le résultat au format JSON
        print(json.dumps(result, ensure_ascii=False))
    except Exception as e:
        with open('python_debug.log', 'a') as f:
            f.write(f"Main error: {str(e)}\n{traceback.format_exc()}\n")
        print(json.dumps({"error": f"Script failed: {str(e)}"}))