import mysql.connector
import json
import sys
import re
import traceback
import os

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
            host=os.getenv('DB_HOST', 'localhost'),
            user=os.getenv('DB_USER', 'root'),
            password=os.getenv('DB_PASSWORD', ''),
            database=os.getenv('DB_NAME', 'taskenuis')
        )
        return connection
    except mysql.connector.Error as err:
        with open('python_debug.log', 'a') as f:
            f.write(f"Database connection failed: {str(err)}\n")
        return {"error": f"Database connection failed: {err}"}

def clean_text(text):
    text = text.lower()
    text = re.sub(r'[^\w\s]', '', text)
    return text

def search_tasks(search_term, category, user_id):
    try:
        connection = connect_to_db()
        if "error" in connection:
            return connection

        cursor = connection.cursor(dictionary=True)
        query = "SELECT id, title, description, deadline, duration, priority, category, status FROM tasks2 WHERE user_id = %s"
        params = [user_id]
        
        if category and category in ['work', 'personal', 'shopping']:
            query += " AND category = %s"
            params.append(category)
            
        cursor.execute(query, params)
        tasks = cursor.fetchall()

        cursor.close()
        connection.close()

        if not tasks:
            return {"tasks": []}

        documents = []
        task_list = []
        for task in tasks:
            text = f"{task['title']} {task['description'] or ''}"
            documents.append(clean_text(text))
            task_list.append(task)

        if search_term:
            search_term = clean_text(search_term)
            vectorizer = TfidfVectorizer()
            tfidf_matrix = vectorizer.fit_transform(documents + [search_term])
            scores = cosine_similarity(tfidf_matrix[-1], tfidf_matrix[:-1]).flatten()

            for i, task in enumerate(task_list):
                task['score'] = scores[i]

            task_list = sorted(task_list, key=lambda x: x['score'], reverse=True)
            task_list = [task for task in task_list if task['score'] > 0]
        else:
            for task in task_list:
                task['score'] = 1.0

        for task in task_list:
            del task['score']

        return {"tasks": task_list}
    except Exception as e:
        with open('python_debug.log', 'a') as f:
            f.write(f"Search failed: {str(e)}\n{traceback.format_exc()}\n")
        return {"error": f"Search failed: {str(e)}"}

if __name__ == "__main__":
    try:
        search_term = sys.argv[1] if len(sys.argv) > 1 else ""
        category = sys.argv[2] if len(sys.argv) > 2 else ""
        user_id = int(sys.argv[3]) if len(sys.argv) > 3 and sys.argv[3].isdigit() else 0
        
        if not user_id:
            print(json.dumps({"error": "Invalid or missing user_id"}))
            sys.exit(1)
            
        result = search_tasks(search_term, category, user_id)
        print(json.dumps(result, ensure_ascii=False))
    except Exception as e:
        with open('python_debug.log', 'a') as f:
            f.write(f"Main error: {str(e)}\n{traceback.format_exc()}\n")
        print(json.dumps({"error": f"Script failed: {str(e)}"}))