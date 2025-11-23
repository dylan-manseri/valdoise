<?php
session_start();
// Assurez-vous que bd_conf.php définit $pdo correctement
require_once 'conf/bd_conf.php';
require_once 'conf/email_conf.php';
require_once 'conf/captcha_conf.php';

// --- CONFIGURATION PHPMailer ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';


// Fonction d'envoi d'e-mail utilisant PHPMailer
function sendVerificationEmail($recipientEmail, $verificationCode, $smtpConfig) {
    $mail = new PHPMailer(true);
    try {
        // Paramètres SMTP
        $mail->isSMTP();
        $mail->Host       = $smtpConfig['host']; 
        $mail->SMTPAuth   = true;

        $mail->Username   = $smtpConfig['username']; 
        $mail->Password   = $smtpConfig['password']; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $smtpConfig['port'];

        // Expéditeur
        $mail->setFrom($smtpConfig['username'], $smtpConfig['sender_name']);
        // Destinataire
        $mail->addAddress($recipientEmail);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Votre code de verification pour Sortie Valdoise';
        $mail->Body    = "<h1>Verification de votre compte</h1><p>Merci de votre inscription. Votre code de vérification est : <strong>{$verificationCode}</strong></p><p>Saisissez ce code sur la page de vérification pour activer votre compte.</p>";
        $mail->AltBody = "Votre code de vérification est : {$verificationCode}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log l'erreur d'envoi de mail, mais ne l'affiche pas directement à l'utilisateur
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}


// VERIFICATION AJAX 
if (isset($_GET['action']) && $_GET['action'] === 'check_username') {
    header('Content-Type: application/json');
    
    $login = $_POST['login'] ?? ''; 

    if (empty($login)) {
        echo json_encode(['available' => false]);
        exit;
    }
    
    $isTaken = false; 
    try {
        // NOTE: On vérifie l'existence même si l'utilisateur est 'pending'
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(login) = LOWER(?)");
        $stmt->execute([$login]);
        
        if ($stmt->fetchColumn() > 0) {
            $isTaken = true;
        }

    } catch (PDOException $e) {
        error_log("DB Error during AJAX check: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['available' => false, 'error' => 'Erreur de base de données.']);
        exit;
    }
    
    echo json_encode(['available' => !$isTaken]);
    exit;
}


// CREATION DE COMPTE 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $recaptchaToken = $_POST['g-recaptcha-response'] ?? null;

    if (!$recaptchaToken) {
        http_response_code(400); 
        exit('<h1>Erreur: Veuillez cocher la case "Je ne suis pas un robot".</h1>');
    }

    // Sanctuarisation des entrées
    $login = trim($_POST['login'] ?? null);
    $nom_user = trim($_POST['nom_user'] ?? null);
    $prenom_user = trim($_POST['prenom_user'] ?? null);
    $email = trim($_POST['email'] ?? null);
    $password = $_POST['password'] ?? null; 


    if (empty($login) || empty($nom_user) || empty($prenom_user) || empty($email) || empty($password)) { 
        http_response_code(400); 
        exit("Erreur: Tous les champs sont requis."); 
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        exit("Erreur: L'adresse e-mail n'est pas valide.");
    }
    
    if (strlen($password) < 8) {
        http_response_code(400);
        exit("Erreur: Le mot de passe doit contenir au moins 8 caractères.");
    }


    // 2.2. VÉRIFICATION RECAPTCHA
    $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';
    $postData = http_build_query([
        'secret'   => $secretKey,
        'response' => $recaptchaToken,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ]);

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => $postData
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($verifyURL, false, $context);
    $result = json_decode($response);

    if (!$result || !$result->success) {
        http_response_code(401);
        exit('<h1>Échec de la vérification CAPTCHA. Vous êtes un robot ?</h1>');
    }


try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(login) = LOWER(?) OR email = ?");
    $stmt->execute([$login, $email]);
    if ($stmt->fetchColumn() > 0) {
        http_response_code(409);
        exit('Un utilisateur avec ce login ou cet e-mail existe déjà. Si votre compte est en attente, vérifiez vos spams.');
    }

    // PRÉ-ENREGISTREMENT ET ENVOI DU CODE
    $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertion du nouvel utilisateur dans la DB avec le code et le statut par default 'pending'
    $stmt = $pdo->prepare("INSERT INTO users (login, nom_user, prenom_user, email, hashedPassword, code_genere) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$login, $nom_user, $prenom_user, $email, $hashedPassword, $verificationCode]);

    // ENVOI DE L'E-MAIL
    if (!sendVerificationEmail($email, $verificationCode, $smtpConfig)) {
         // Si le mail échoue, supprimez l'utilisateur pour qu'il puisse réessayer
         $pdo->prepare("DELETE FROM users WHERE email = ? AND status = 'pending'")->execute([$email]);
         http_response_code(500);
         exit('Erreur lors de l\'envoi de l\'e-mail de vérification. Veuillez réessayer.');
    }
    
    // Préparation de la Session pour la prochaine étape
    // On ne stocke que l'e-mail (identifiant) dans la session
    $_SESSION['email_pending'] = $email;
    
    header('Location: verifCode.php');
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    exit('Erreur de base de données lors de l\'enregistrement: ' . $e->getMessage());
} catch (\Exception $e) {
    http_response_code(500);
    exit('Une erreur inattendue est survenue: ' . $e->getMessage());
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Page insciption</title>
  <style>
    body {
      font-family: 'Permanent Marker', cursive; 
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh; 
      margin: 0;
      background-color: #e7e8bc;
    }

    .register-container {
      background-color: #f4f4d7;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      text-align: center;
      width: 300px;
    }

    .register-container h2 {
      margin-bottom: 20px;
      font-size: 1.8rem;
      color: #333;
    }

    .register-container input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      font-size: 1rem;
      font-family: 'Permanent Marker', cursive; 
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .register-container button {
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
      transition: 0.2s;
    }

    .register-container button:hover {
      background-color: #7789b1;
      opacity: 0.3;
    }
    .register-remark {
      font-size: 0.8em;      /* Petite écriture */
      color: #6c757d;       /* Couleur discrète (gris) */
      margin-top: -10px;    /* Un peu moins d'espace en haut */
      margin-bottom: 20px;  /* Espace avant le titre "Login" */
    }

    .register-container h2 {
      position: relative; 
      display: inline-block;
      margin-bottom: 20px;
      font-size: 2.5rem;
      color: #333;
    }
    .sun-inline {
      position: absolute;
      top: 17px;       
      right: -35px;       
      width: 60px;
    }
    
    .sun-inline img {
      width: 100%;
    }
    /* AJOUTEZ cette nouvelle règle CSS à la fin de votre balise <style> */
  .register-icon {
    position: absolute;  /* Positionne l'image par rapport au .register-container */
    bottom: 15px;        /* 15px du bord inférieur */
    right: 430px;         /* 15px du bord droit */
    width: 150px;         /* Ajustez la taille de l'image comme vous le souhaitez */
  }
  .paper-inline {
    position: absolute;
    top: 120px;       
    left: 490px;       
    width: 70px;
  }
    
    .paper-inline img {
        width: 100%;
    }

    @keyframes float {
      0% {
        transform: translateY(0px);
      }
      50% {
        transform: translateY(-10px);
      }
      100% {
        transform: translateY(0px);
      }
    }
    @keyframes float1 {
      0% {
        transform: translateX(0px);
      }
      50% {
        transform: translateX(-5px);
      }
      100% {
        transform: translateX(0px);
      }
    }

    .paper-inline{
      animation-name: float;
      animation-duration: 7s; 
      animation-iteration-count: infinite;
      animation-timing-function: ease-in-out;
    }
    .register-icon{
      animation-name: float1;
      animation-duration: 10s; 
      animation-iteration-count: infinite;
      animation-timing-function: ease-in-out;
    }

  </style>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
  <div class="register-container">
    <h2>
      Register
    </h2>
    <form action="" method="POST">
      <input type="text" name="login" placeholder="Nom d'utilisateur (Login)" id="username-input" maxlength="12" required>
        <span id="username-error" style="color: red; font-size: 0.9em; height: 1em;"></span>      
      <input type="text" name="nom_user" placeholder="Votre nom" maxlength="30" required>
      <input type="text" name="prenom_user" placeholder="Votre prenom" maxlength="30" required>
      <input type="email" name="email" placeholder="Email" maxlength="50" required>
      <input type="password" name="password" placeholder="Password" required>
      <div class="g-recaptcha" data-sitekey=<?=$data_sitekey?>></div>
      <button type="submit">register</button>
      <p style="font-size: 0.9em;">Si vous avez déjà un compte, <a href="connexion.php">passez au login</a></p>
    </form>
  </div>
<script>
  const usernameInput = document.getElementById('username-input');
  const errorMessageSpan = document.getElementById('username-error');
  let currentAbortController = null; // Pour annuler les requêtes précédentes

  // This function runs every time you type in the username field
  usernameInput.addEventListener('input', function() {
    const login = usernameInput.value.trim(); 

    // Clear any previous message
    errorMessageSpan.textContent = '';
      
      // Annuler la requête précédente pour ne pas avoir de résultats en double
    if (currentAbortController) { 
        currentAbortController.abort(); 
    }
    currentAbortController = new AbortController(); 
    const signal = currentAbortController.signal; 

    // Only start checking after 3 characters
    if (login.length >= 3) {
      
      // CIBLE CORRIGÉE : Pointe vers le même fichier avec l'action AJAX
      fetch('creationCompte.php?action=check_username', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        // Nom de la variable 'login' qui est envoyée au PHP
        body: `login=${encodeURIComponent(login)}`, 
        signal: signal 
      })
      .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.json();
      })
      .then(data => {
        if (signal.aborted) return; // Ignore si la requête a été annulée
                  
        // 'data.available' comes from your PHP script
        if (!data.available) {
          errorMessageSpan.textContent = 'Ce login est déjà utilisé.';
          errorMessageSpan.style.color = 'red';
        } else {
          errorMessageSpan.textContent = 'Login disponible !';
          errorMessageSpan.style.color = 'green';
        }
      })
      .catch(error => {
        if (error.name === 'AbortError') return; // C'est normal
        console.error("Erreur de vérification AJAX:", error);
        errorMessageSpan.textContent = 'Erreur lors de la vérification.';
        errorMessageSpan.style.color = 'orange';
      });
    }
  });
</script>
</body>
</html>
