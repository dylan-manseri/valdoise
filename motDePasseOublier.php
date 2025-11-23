<?php
require_once 'conf/bd_conf.php';
require_once 'conf/email_conf.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'] ?? null;

  $genericSuccessMessage = '<h1>Si un compte existe avec cet email, un lien de réinitialisation a été envoyé. Vérifiez votre boîte de réception.</h1>';
  
  $foundUser = null;
  try {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $foundUser = $stmt->fetch(); 

  } catch (PDOException $e) {
    error_log('Request-reset DB read error: ' . $e->getMessage());
    exit($genericSuccessMessage);
  }
  
  if ($foundUser !== null && $foundUser !== false) {
      $token = bin2hex(random_bytes(32)); 
      $expires = time() + 3600; 
      try {
          // We run an UPDATE query on the 'users' table to save the token and expiration time
          $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
          // We use the 'id' from the user we found in step 2
          $updateStmt->execute([$token, $expires, $foundUser['email']]);
      } catch (PDOException $e) {
          // Silently log the error
          error_log('Request-reset DB update error: ' . $e->getMessage());
          exit($genericSuccessMessage);
      }
      
      // 3. Send the email using PHPMailer
      // This is the URL that the user will click to input their new password
      $resetLink = "https://sortievaldoise.alwaysdata.net/changerMdp.php?token=" . $token;
      
      // PHPMailer Configuration (Assuming classes are loaded)
      $mail = new PHPMailer(true); 
      try {
          $mail->isSMTP();
          $mail->Host       = $smtpConfig['host'];
          $mail->SMTPAuth   = true;
          $mail->Username   =$smtpConfig['username'];
          $mail->Password   = $smtpConfig['password'];
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
          $mail->Port       = $smtpConfig['port'];

          $mail->setFrom($smtpConfig['username'], $smtpConfig['sender_name']);
          $mail->addAddress($email); 

          //Content
          $mail->isHTML(true);
          $mail->Subject = 'Reset Your Password';
          // This line sends the URL
          $mail->Body    = 'Click this link to reset your password: <a href="' . $resetLink . '">' . $resetLink . '</a>';
          $mail->AltBody = 'Copy this link into your browser to reset your password: ' . $resetLink;

          $mail->send();
      } catch (\PHPMailer\Exception $e) {
          error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
      }
  }

  exit($genericSuccessMessage);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
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
    .login-remark {
      font-size: 0.8em;      /* Petite écriture */
      color: #6c757d;       /* Couleur discrète (gris) */
      margin-top: -10px;    /* Un peu moins d'espace en haut */
      margin-bottom: 20px;  /* Espace avant le titre "Login" */
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
        <h2>Réinitialiser le mot de passe</h2>
        <p>Entrez votre email et nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>
        
        <form action="" method="POST">
            <input type="email" name="email" placeholder="Votre email" required>
            <button type="submit">Envoyer le lien</button>
        </form>
    </div>
</body>
</html>