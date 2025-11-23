<?php
require_once 'conf/bd_conf.php';
session_start();

$message = "";
$code = trim((string)($_POST['code'] ?? ''));
$email_pending = $_SESSION['email_pending'] ?? null;
$foundUser = null;

if (!$email_pending) {
    header('Location: creationCompte.php');
    exit;
}

try{
  if (isset($_SESSION['email_pending'])) {
    $stmt = $pdo->prepare("SELECT login, nom_user, prenom_user, status, code_genere FROM users WHERE email = ?");
    $stmt->execute([$email_pending]);
    $foundUser = $stmt->fetch(PDO::FETCH_ASSOC);
  }
  if (!$foundUser || $foundUser['status'] !== 'pending') {
    if ($foundUser && $foundUser['status'] === 'active') {
      // Redirection si l'utilisateur est déjà actif
      header('Location: connexion.php');
      exit;
    }
    // Sinon, forcer l'utilisateur à s'inscrire à nouveau
    unset($_SESSION['email_pending']);
    header('Location: creationCompte.php');
    exit;
  }
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && $code) {

    if ($code === $foundUser['code_genere']) {
      // CODE CORRECT : Activer le compte
      
      $updateStmt = $pdo->prepare("UPDATE users SET status = 'active', code_genere = NULL WHERE login = ?");
      $updateStmt->execute([$foundUser['login']]);

      // Nettoyer la session et rediriger vers la connexion
      unset($_SESSION['email_pending']);
        $_SESSION['login'] = $foundUser['login'];
        $_SESSION['name'] = $foundUser['nom_user'];
        $_SESSION['pren'] = $foundUser['prenom_user'];
      $message = "Votre compte a été activé avec succès ! Vous pouvez maintenant vous connecter.";
      header('refresh:5;url=caMarche.html'); // Redirection après 5 secondes
    } else {
      // CODE INCORRECT
      $message = "Le code de vérification est incorrect. Veuillez réessayer.";
    }
  }

} catch (PDOException $e) {
    error_log("DB Error on verifCode: " . $e->getMessage());
    $message = "Une erreur de base de données est survenue. Veuillez réessayer plus tard.";
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
    .error { color: red; font-weight: bold; }
    .success { color: green; font-weight: bold; }
    </style>
</head>
<body>
  <div class="login-container">
    <!-- Affichage du message (Succès/Erreur) -->
    <?php if ($message): ?>
      <p class="<?php echo (strpos($message, 'succès') !== false) ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></p>
      <?php if (strpos($message, 'succès') !== false): ?>
        <p>Redirection automatique en cours...</p>
      <?php endif; ?>
  <?php else: ?>
      <!-- Formulaire de soumission du code -->
      <h1>Activation</h1>
      <p>
        Veuillez entrer le code de vérification pour 
        <strong><?php echo htmlspecialchars($email_pending); ?></strong>
      </p>

      <form action="" method="POST">
        <input type="text" name="code" placeholder="Entrez le code" required maxlength="6" pattern="\d{6}" autofocus>
        <button type="submit">Activer le Compte</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>