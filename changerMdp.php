<?php
require_once '../../.conf.php';
$token = $_GET['token'] ?? $_POST['token'] ?? null;
$error = null;
$success = null;


$foundUser = null;

if (!$token) {
    die('Aucun jeton (token) fourni.');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE reset_token = ?");
    $stmt->execute([$token]);
    $foundUser = $stmt->fetch();
} catch (PDOException $e) {
    die('Erreur de base de données. Veuillez réessayer.');
}

if ($foundUser === false) {
    die('Jeton invalide.');
}


if ($foundUser['reset_expires'] < time()) {
    die('Ce jeton a expiré. Veuillez en demander un nouveau.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (empty($password) || $password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas ou sont vides.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare(
                "UPDATE utilisateurs 
                 SET hashedPassword = ?, reset_token = NULL, reset_expires = NULL 
                 WHERE id = ?"
            );
            $stmt->execute([$hashedPassword, $foundUser['id']]);

        } catch (PDOException $e) {
            die('Erreur lors de la mise à jour du mot de passe.');
        }
        header('Location: /connexion');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Réinitialiser le mot de passe</title>
    <style>
    body {
      font-family: 'Permanent Marker', cursive; 
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh; /* Full viewport height */
      background-color: #e7e8bc;
      margin: 0;
    }

    .login-container {
      background-color: #f4f4d7;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      text-align: center;
      width: 300px;
    }

    .login-container h2 {
      margin-bottom: 20px;
      font-size: 1.8rem;
      color: #333;
    }

    .login-container input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      font-size: 1rem;
      font-family: 'Permanent Marker', cursive; 
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .login-container button {
      position: relative;
      width: 100%;
      padding: 10px;
      margin-top: 15px;
      background-color: #7e9ad7;
      color: white;
      font-size: 1rem;
      font-family: 'Permanent Marker', cursive; 
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: 0.5s;
    }

    .login-container button:hover {
      background-color: #7789b1;
      opacity: 0.3;
    }
    .login-container h2 {
      position: relative; 
      display: inline-block;
      margin-bottom: 20px;
      font-size: 2.5rem;
      color: #333;
    }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Entrez votre nouveau mot de passe</h2>

        <?php if ($success): ?>
            <p style="color: green;"><?php echo $success; ?></p>
        <?php else: ?>
            
            <?php if ($error): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>

            <!-- The HTML form action is correct, it posts to itself -->
            <form action="reset-password.php" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <input type="password" name="password" placeholder="Nouveau mot de passe" required>
                <input type="password" name="password_confirm" placeholder="Confirmez le mot de passe" required>
                <button type="submit">Enregistrer</button>
            </form>

        <?php endif; ?>

    </div>
</body>
</html>