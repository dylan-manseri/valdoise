<?php
session_start();
require_once 'conf/bd_conf.php';

if (!isset($_SESSION['login'])) {
    header('Location: /index.php');
    exit;
}

$login = $_SESSION['login'];

$stmt = $pdo->prepare("SELECT nom_user, prenom_user, email FROM users WHERE login = ?");
$stmt->execute([$login]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom_user'];
    $prenom = $_POST['prenom_user'];
    $email = $_POST['email'];

    $stmt = $pdo->prepare("UPDATE users SET nom_user = ?, prenom_user = ?, email = ? WHERE login = ?");
    $stmt->execute([$nom, $prenom, $email, $login]);

    header('Location: profil.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier Profil</title>
<link rel="stylesheet" href="style/<?=$style?>/navbar.css" />
<style>
body{margin:0;background:#e7e8bc}
.container{max-width:800px;margin:50px auto;background:#f4f4d7;padding:30px;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,0.2)}
h1{font-size:2.2rem;margin-bottom:20px}
.logout{display:inline-block;margin-top:20px;padding:10px 20px;background:#7e9ad7;color:#fff;text-decoration:none;border-radius:5px}
.logout:hover{opacity:0.3}
label{display:block;margin-bottom:10px}
input{padding:8px;width:100%;margin-top:5px;border-radius:5px;border:1px solid #ccc}
button.logout{margin-top:15px}
</style>
</head>
<body>
<div class="container">
<h1>Modifier vos informations</h1>
<form action="modifier_profil.php" method="post">
    <label>Nom<input type="text" name="nom_user" value="<?= htmlspecialchars($user['nom_user']) ?>" required></label>
    <label>Prénom<input type="text" name="prenom_user" value="<?= htmlspecialchars($user['prenom_user']) ?>" required></label>
    <label>Email<input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></label>
    <button class="logout" type="submit">Enregistrer</button>
</form>
<a class="logout" href="profil.php">Annuler</a>
</div>
</body>
</html>
