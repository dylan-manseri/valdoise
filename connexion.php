<?php
session_start();
require_once 'conf/bd_conf.php';
require_once 'conf/captcha_conf.php';

$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
  if (isset($_POST['action']) && $_POST['action'] === 'autocomplete') {
        
        // 1. Indiquer au navigateur qu'on envoie du JSON
        header('Content-Type: application/json');
        
        $searchQuery = $_POST['query'] ?? '';
        if (empty($searchQuery)) {
            echo json_encode([]);
            exit;
        }

        // 2. Recherche dans la base de données
        try {
            $searchTerm = $searchQuery . '%';
            // Requête sécurisée pour sélectionner le champ 'login' correspondant
            $stmt = $pdo->prepare("SELECT login FROM users WHERE login LIKE ? LIMIT 10"); 
            $stmt->execute([$searchTerm]); 
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); 
            
            // 3. Conversion du tableau PHP en chaîne JSON et envoi
            echo json_encode($results);

        } catch (PDOException $e) {
            error_log("Autocomplete DB Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([]);
        }
        // CRITIQUE: Arrêter l'exécution pour ne pas renvoyer le code HTML ci-dessous
        exit; 
    }

    $recaptchaToken = $_POST['g-recaptcha-response'] ?? null;

    if (!$recaptchaToken) {
        http_response_code(400); 
        exit('<h1>Veuillez cocher la case "Je ne suis pas un robot".</h1>');
    }
    
    $login = $_POST['login'] ?? null;
    $password = $_POST['password'] ?? null;
    
    if (empty($login) || empty($password)) { exit("Login et mot de passe requis"); }


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
      $stmt = $pdo->prepare("SELECT login, hashedPassword, nom_user, prenom_user FROM users WHERE login = ?");
      $stmt->execute([$login]);
      
      $foundUser = $stmt->fetch(PDO::FETCH_ASSOC);

      } catch (PDOException $e) {
          http_response_code(500);
          exit('Database error: ' . $e->getMessage());
      }
    
    if ($foundUser && password_verify($password, $foundUser['hashedPassword'])) {
        $_SESSION['login'] = $foundUser['login'];
        $_SESSION['name'] = $foundUser['nom_user'];
        $_SESSION['pren'] = $foundUser['prenom_user'];
       header('Location: /profil.php');
        exit;
    } else {
        $errorMessage = 'Login ou mot de passe incorrect.';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Page Login</title>
  <style>
    body {
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
    .sun-inline {
        position: absolute;
        top: 17px;       
        right: -35px;       
        width: 60px;
    }
    
    .sun-inline img {
        width: 100%;
    }
    
    .paper-inline {
        position: absolute;
        top: 150px;       
        right: 510px;       
        width: 70px;
    }
    
    .paper-inline img {
        width: 100%;
    }


    .login-icon {
      position: absolute;  
      bottom: 45px;       
      left: 400px;    
      width: 250px;   
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
    .login-icon{
      animation-name: float1;
      animation-duration: 10s; 
      animation-iteration-count: infinite;
      animation-timing-function: ease-in-out;
    }
    /* Styling for the autocomplete suggestions list */
#suggestions {
    position: relative;
    border: 1px solid #ccc;
    background-color: white;
    width: 100%;
    max-height: 150px;
    overflow-y: auto;
    z-index: 1000;
    /* CHANGE 3: Remove list bullets and default padding */
    list-style-type: none;
    padding: 0;
    margin: 0;
    border-radius: 15px;
}
/* CHANGE 4: Style the <li> instead of the <div> */
#suggestions li {
    padding: 8px;
    cursor: pointer;
}
#suggestions li:hover {
    background-color: #f0f0f0;
}
  </style>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
  <div class="login-container">
    <h2>
      Login
    </h2>
    <?php if (!empty($errorMessage)): ?>
      <div class="error-message">
        <?= htmlspecialchars($errorMessage) ?>
      </div>
    <?php endif; ?>
    
    <form action="" method="POST">
      <div class="suggestions-container">
        <input type="text" id="username" name="login" placeholder="Nom d'utilisateur (Login)" required autocomplete="off">
        
        <ul id="suggestions"></ul>
      </div>
      <input type="password" name="password" placeholder="Mot de passe" required>
      <div class="g-recaptcha" data-sitekey=<?=$data_sitekey?>></div>
      <button type="submit">Login</button>
      <p style="font-size: 0.9em;">Vous n'avez pas de compte? <a href="creationCompte.php">inscrivez-vous </a></p>
      <a href="/motDePasseOublier.php">Mot de passe oublié ?</a>
    </form>

  </div>
<script>
  const usernameInput = document.getElementById('username');
  const suggestionsBox = document.getElementById('suggestions');
  let currentAbortController = null;

  usernameInput.addEventListener('input', () => {
  const query = usernameInput.value.trim();
  suggestionsBox.innerHTML = ''; 

  if (currentAbortController) { 
      currentAbortController.abort(); 
  } 
  currentAbortController = new AbortController(); 
  const signal = currentAbortController.signal; 

  if (query.length > 0) {
    fetch('', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `query=${encodeURIComponent(query)}&action=autocomplete`,
      signal: signal 
  })
  .then(response => {
    const contentType = response.headers.get("content-type");
    if (response.ok && contentType && contentType.includes("application/json")) {
      return response.json();
    } else {
      console.error("Erreur de format de réponse ou statut non OK:", response.status);
      return [];
    }
  })
  .then(matchingUsers => {
    if (signal.aborted) return;

    matchingUsers.forEach(loginSuggestion => {
      const listItem = document.createElement('li');
      listItem.textContent = loginSuggestion;

      listItem.addEventListener('click', () => {
      usernameInput.value = loginSuggestion;
      suggestionsBox.innerHTML = '';
      });

      suggestionsBox.appendChild(listItem);
    });
  })
  .catch(error => {
      if (error.name === 'AbortError') return; 
      console.error("Erreur lors de la récupération des suggestions:", error);
  });
}
});
    </script>
    </body>
</html>