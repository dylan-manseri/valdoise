  <?php
session_start();
$dbFilePath = './mdp.json';

// Inclusions de PHPMailer
require 'vendor/autoload.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    $name = $_POST['name'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    if (empty($name) || empty($email) || empty($password)) { exit("All fields are required"); }

    $users = file_exists($dbFilePath) ? json_decode(file_get_contents($dbFilePath), true) : [];
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            http_response_code(409);
            exit('User with this email already exists');
        }
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $newUserId = uniqid('user_');
    $token = bin2hex(random_bytes(32)); 
    
    $newUser = [
        'id' => $newUserId, 
        'name' => $name, 
        'email' => $email, 
        'hashedPassword' => $hashedPassword,
        'is_verified' => false,
        'verification_token' => $token
    ];

    $users[] = $newUser;
    
    try {
        file_put_contents($dbFilePath, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
        
        // Envoi du mail
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.votreservice.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'votre-email@votresite.com';
        $mail->Password   = 'votre_mot_de_passe_email';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
        $mail->Port       = 465; 
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('no-reply@votresite.com', 'Sortie Val d\'Oise');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Confirmez votre inscription';
        
        $verificationLink = "https://votresite.com/verifier.php?token=" . $token; 
        $mail->Body    = "<h2>Bienvenue, $name!</h2><p>Veuillez cliquer sur ce lien pour activer votre compte :</p><p><a href='$verificationLink'>Activer mon compte</a></p>";
        $mail->AltBody = "Veuillez copier/coller ce lien pour activer votre compte : $verificationLink";

        $mail->send();
        
        header('Location: /checkYourEmail.html'); 
        exit;
        
    } catch (Exception $e) {
        // En cas d'échec de l'envoi de mail ou de l'écriture du fichier
        http_response_code(500);
        exit("Erreur : L'inscription a échoué. " . $e->getMessage());
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
      font-size: 0.8em; 
      color: #6c757d; 
      margin-top: -10px; 
      margin-bottom: 20px; 
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
  .register-icon {
    position: absolute; 
    bottom: 15px; 
    right: 430px; 
    width: 150px; 
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
</head>
<body>
  <div class="register-container">
    <h2>
      Register
    </h2>
    <form action="/register" method="POST">
      <input type="text" name="name" placeholder="Nom + prenom" id="username-input" required>
        <span id="username-error" style="color: red; font-size: 0.9em; height: 1em;"></span>     
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">register</button>
      <p style="font-size: 0.9em;">Si vous avez déjà un compte, <a href="connexion.php">passez au login</a></p>
    </form>
  </div>
<script>
        const usernameInput = document.getElementById('username-input');
        const errorMessageSpan = document.getElementById('username-error');

        // This function runs every time you type in the username field
        usernameInput.addEventListener('input', function() {
            const username = usernameInput.value.trim();

            // Clear any previous message
            errorMessageSpan.textContent = '';

            // Only start checking after 3 characters
            if (username.length >= 3) {
                // Send the username to the PHP server to check if it's taken
                fetch('/check-username', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `username=${encodeURIComponent(username)}`
                })
                .then(response => response.json())
                .then(data => {
                    // 'data.available' comes from your PHP script
                    if (!data.available) {
                        errorMessageSpan.textContent = 'Ce nom d\'utilisateur est déjà utilisé.';
                        usernameInput.value = '';
                    }
                })
            }
        });
    </script>
    </body>
</html>