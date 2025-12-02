<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
require_once 'conf/bd_conf.php';
$stmt = $pdo->prepare("SELECT nom_user, prenom_user, login FROM users WHERE login = ?");
$stmt->execute([$_SESSION['login']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Mon Profil</title>
<style>
body {
  font-family: 'Permanent Marker', cursive; 
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #e7e8bc;
  margin: 0;
}
.profil-container {
  background-color: #f4f4d7;
  padding: 30px 40px;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  text-align: center;
  width: 350px;
}
.profil-container h2 {
  margin-bottom: 20px;
  font-size: 2rem;
  color: #333;
}
.profil-container p {
  font-size: 1rem;
  margin: 10px 0;
}
.profil-container button {
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
.profil-container button:hover {
  background-color: #7789b1;
  opacity: 0.3;
}
</style>
</head>
<body>
<div class="profil-container">
<h2>Mon Profil</h2>

<p><strong>Nom :</strong> <?= htmlspecialchars($user['nom_user']) ?></p>
<p><strong>Prénom :</strong> <?= htmlspecialchars($user['prenom_user']) ?></p>
<p><strong>Login :</strong> <?= htmlspecialchars($user['login']) ?></p>

<form action="logout.php" method="POST">
<button type="submit">Déconnexion</button>
</form>
</div>
</body>
</html>
