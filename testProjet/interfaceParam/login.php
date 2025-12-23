<?php
   include 'config.php';

   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       $email = trim($_POST['email']);
       $password = trim($_POST['password']);

       try {
           $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
           $stmt->execute([$email]);
           $user = $stmt->fetch(PDO::FETCH_ASSOC);

           if ($user && $password === $user['password']) {
               $_SESSION['user_id'] = $user['id'];
               header("Location: settings.php");
               exit;
           } else {
               $error = "Email ou mot de passe incorrect";
           }
       } catch (Exception $e) {
           $error = "Erreur : Impossible de se connecter";
       }
   }
   ?>
   <!DOCTYPE html>
   <html>
   <head>
       <title>Connexion</title>
   </head>
   <body>
       <h2>Connexion</h2>
       <?php if (isset($error)): ?>
           <p style="color: red;"><?php echo $error; ?></p>
       <?php endif; ?>
       <form method="POST">
           <label>Email:</label>
           <input type="email" name="email" required><br>
           <label>Mot de passe:</label>
           <input type="password" name="password" required><br>
           <button type="submit">Se connecter</button>
       </form>
   </body>
   </html>