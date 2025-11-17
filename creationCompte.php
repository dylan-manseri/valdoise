<?php
session_start();
$dbFilePath = './mdp.json';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {  

  $secretKey = '6LevZwwsAAAAAEW-nvjqE6s-f7dswt8OzcPIM1_V'; 
  $recaptchaToken = $_POST['g-recaptcha-response'] ?? null;

  if (!$recaptchaToken) {
      http_response_code(400); 
      exit('<h1>Veuillez cocher la case "Je ne suis pas un robot".</h1>');
  }


  $name = $_POST['name'] ?? null;
  $email = $_POST['email'] ?? null;
  $password = $_POST['password'] ?? null;

  if (empty($name) || empty($email) || empty($password)) { exit("All fields are required"); }

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


  $users = file_exists($dbFilePath) ? json_decode(file_get_contents($dbFilePath), true) : [];
  foreach ($users as $user) {
      if ($user['email'] === $email) {
          http_response_code(409);
          exit('User with this email already exists');
      }
  }
  
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
  $newUserId = uniqid('user_');
  $newUser = ['id' => $newUserId, 'name' => $name, 'email' => $email, 'hashedPassword' => $hashedPassword];
  $users[] = $newUser;
  file_put_contents($dbFilePath, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
  $_SESSION['userId'] = $newUserId;
  $_SESSION['name'] = $newUser['name'];

  header('Location: /caMarche.html');
  exit;
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
    <form action="/register" method="POST">
      <input type="text" name="name" placeholder="Nom + prenom" id="username-input" required>
        <span id="username-error" style="color: red; font-size: 0.9em; height: 1em;"></span>      
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <div class="g-recaptcha" data-sitekey="6LevZwwsAAAAAHJ6UbjViJZvzWHdhkgQqB4v2zHz"></div>
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
