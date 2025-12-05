<?php
session_start();
require_once 'conf/bd_conf.php';

if (!isset($_SESSION['login'])) {
    header('Location: /index.php');
    exit;
}

$login = $_SESSION['login'];

/* --- Requête utilisateur --- */
$stmt = $pdo->prepare("SELECT login, nom_user, prenom_user, email FROM users WHERE login = ?");
$stmt->execute([$login]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* --- Requête favoris --- */
$fav = $pdo->prepare("SELECT titre, categorie FROM favoris WHERE user_login = ?");
$fav->execute([$login]);
$favoris = $fav->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Profil</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Permanent+Marker&display=swap" rel="stylesheet">
<style>
body{
    margin:0;
    background:#e7e8bc;
    font-family:'Permanent Marker',cursive;
}
.container{
    max-width:800px;
    margin:50px auto;
    background:#f4f4d7;
    padding:30px;
    border-radius:10px;
    box-shadow:0 4px 10px rgba(0,0,0,0.2);
}
h1{
    font-size:2.2rem;
    margin-bottom:20px;
}
.section{
    margin-top:30px;
    background:#fff;
    padding:20px;
    border-radius:8px;
}
.section h2{
    font-size:1.7rem;
    margin-bottom:15px;
}
table{
    width:100%;
    border-collapse:collapse;
    font-size:1rem;
}
table td,table th{
    padding:10px;
    border-bottom:1px solid #ddd;
}
.logout{
    display:inline-block;
    margin-top:20px;
    padding:10px 20px;
    background:#7e9ad7;
    color:#fff;
    text-decoration:none;
    border-radius:5px;
}
.logout:hover{
    opacity:0.3;
}
</style>
</head>
<body>
<div class="container">
<h1>Profil</h1>

<div class="section">
<h2>Informations</h2>
<table>
<tr><th>Nom</th><td><?= htmlspecialchars($user['nom_user']) ?></td></tr>
<tr><th>Prénom</th><td><?= htmlspecialchars($user['prenom_user']) ?></td></tr>
<tr><th>Login</th><td><?= htmlspecialchars($user['login']) ?></td></tr>
<tr><th>Email</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
</table>
</div>

<div class="section">
<h2>Favoris</h2>
<?php if (empty($favoris)): ?>
<p>Aucun favori enregistré.</p>
<?php else: ?>
<table>
<tr><th>Titre</th><th>Catégorie</th></tr>
<?php foreach ($favoris as $f): ?>
<tr>
<td><?= htmlspecialchars($f['titre']) ?></td>
<td><?= htmlspecialchars($f['categorie']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>

<a class="logout" href="logout.php">Déconnexion</a>
</div>
</body>
</html>
