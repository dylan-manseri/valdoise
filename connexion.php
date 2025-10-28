<?php
session_start();
$dbFilePath = './mdp.json';


  if ($_SERVER['REQUEST_METHOD'] === 'POST') { 

  $email = $_POST['email'] ?? null;
  $password = $_POST['password'] ?? null;

  if (empty($email) || empty($password)) { exit("Email and password are required"); }

  $users = file_exists($dbFilePath) ? json_decode(file_get_contents($dbFilePath), true) : [];
  $foundUser = null;
  foreach ($users as $user) {
      if ($user['email'] === $email) {
          $foundUser = $user;
          break;
      }
  }
  
  if ($foundUser && password_verify($password, $foundUser['hashedPassword'])) {
      $_SESSION['userId'] = $foundUser['id'];
      $_SESSION['name'] = $foundUser['name'];
      header('Location: /caMarche.html');
  } else {
      http_response_code(401);
      exit('<h1>Email ou mot de passe incorrect.</h1>');
  }
  exit; 
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
</head>
<body>
  <div class="login-container">
    <h2>
      Login
    </h2>
    
    <form action="" method="POST">
      <div class="suggestions-container">
        <input type="text" id="username" name="name" placeholder="Nom d'utilisateur" required autocomplete="off">
        
        <ul id="suggestions"></ul>
      </div>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
      <p style="font-size: 0.9em;">Vous n'avez pas de compte? <a href="creationCompte.php">inscrivez-vous </a></p>
    </form>

  </div>
<script>
        const usernameInput = document.getElementById('username');
        const suggestionsBox = document.getElementById('suggestions');

        usernameInput.addEventListener('input', () => {
            const query = usernameInput.value.trim();
            suggestionsBox.innerHTML = '';

            if (query.length > 0) {
                fetch('/find-users', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `query=${encodeURIComponent(query)}`
                })
                .then(response => response.json())
                .then(matchingUsers => {
                    matchingUsers.forEach(name => {
                        // CHANGE 2: Create an <li> element for each suggestion
                        const listItem = document.createElement('li');
                        listItem.textContent = name;
                        
                        // When a user clicks a list item, fill the input
                        listItem.addEventListener('click', () => {
                            usernameInput.value = name;
                            suggestionsBox.innerHTML = '';
                        });

                        // Add the list item to the <ul>
                        suggestionsBox.appendChild(listItem);
                    });
                });
            }
        });
    </script>
    </body>
</html>