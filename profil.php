<?php
session_start();
require_once 'conf/bd_conf.php';

$cookieConsent = $_COOKIE['cookieConsent'] ?? null;
$style = "light";

if (isset($_GET["mode"]) && in_array($_GET["mode"], ["light","dark"], true)) {
    $style = $_GET["mode"];
    if ($cookieConsent === 'true') {
        setcookie("style", $style, time() + 60*60*24*30, "/");
    }
} elseif ($cookieConsent === 'true' && isset($_COOKIE['style']) && in_array($_COOKIE['style'], ['light','dark'], true)) {
    $style = $_COOKIE['style'];
}

if ($cookieConsent === 'true' && isset($_COOKIE["date_last_visit"])) {
    setcookie("date_last_visit", time(), time() + 60*60*24*30, "/");
}

$bascule = ($style === "light") ? "dark" : "light";

if (!isset($_SESSION['login'])) {
    header('Location: /index.php');
    exit;
}

$login = $_SESSION['login'];

// Récupérer infos utilisateur
$stmt = $pdo->prepare("SELECT login, nom_user, prenom_user, email FROM users WHERE login = ?");
$stmt->execute([$login]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer favoris avec jointure sur propositions
$fav = $pdo->prepare("
    SELECT p.titre AS titre, p.status AS categorie
    FROM favoris f
    LEFT JOIN propositions p ON f.id_sortie = p.id_prop
    WHERE f.user_login = ?
    ORDER BY f.id_favoris DESC
");
$fav->execute([$login]);
$favoris = $fav->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Profil</title>
<link rel="stylesheet" href="style/<?=$style?>/navbar.css" />
<style>
body{margin:0;background:#e7e8bc}
.container{max-width:800px;margin:50px auto;background:#f4f4d7;padding:30px;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,0.2)}
h1{font-size:2.2rem;margin-bottom:20px}
.gold-gradient{text-align:center;font-size:2.2rem;font-weight:bold;background:linear-gradient(90deg,#b8860b,#ffdf00,#b8860b);-webkit-background-clip:text;-webkit-text-fill-color:transparent;display:block;margin:20px 0}
.section{margin-top:30px;background:#fff;padding:20px;border-radius:8px}
.section h2{font-size:1.7rem;margin-bottom:15px}
table{width:100%;border-collapse:collapse;font-size:1rem}
table td,table th{padding:10px;border-bottom:1px solid #ddd}
.logout{display:inline-block;margin-top:20px;padding:10px 20px;background:#7e9ad7;color:#fff;text-decoration:none;border-radius:5px;border:none;cursor:pointer}
.logout:hover{opacity:0.3}
form{margin-top:15px}
</style>
</head>
<body>

<header>
    <div class="logo">
        <a href="index.php"><img src="images/logo_sv.png" alt="icone du site"/></a>
    </div>
    <nav>
        <ul>
            <li><a class="select-nav" href="carte.php">Carte</a></li>
            <li><a class="select-nav" href="sorties.php">Sorties</a></li>
            <li><a class="select-nav" href="connexion.php">Mes activités</a></li>
        </ul>
    </nav>
    <div class="style-toggle">
        <a class="select-nav-cookie" href="cookies.php">Cookies</a>
        <?php if (!isset($_GET["style"]) || $_GET["style"] == "light"): ?>
            <a href="?style=dark" class="dark-mode">🌙 Mode nuit</a>
        <?php else: ?>
            <a href="?style=light" class="light-mode">☀️ Mode jour</a>
        <?php endif; ?>
    </div>
</header>

<div class="container">
<h1>Profil</h1>
<h2 class="gold-gradient">
    Bienvenue <?= htmlspecialchars($user['prenom_user'] . " " . $user['nom_user']) ?>
</h2>

<div class="section">
<h2>Informations</h2>
<table>
<tr><th>Nom</th><td><?= htmlspecialchars($user['nom_user'] ?? '') ?></td></tr>
<tr><th>Prénom</th><td><?= htmlspecialchars($user['prenom_user'] ?? '') ?></td></tr>
<tr><th>Login</th><td><?= htmlspecialchars($user['login'] ?? '') ?></td></tr>
<tr><th>Email</th><td><?= htmlspecialchars($user['email'] ?? '') ?></td></tr>
</table>
<form action="modifier_profil.php" method="get">
    <button type="submit" class="logout">Modifier les informations</button>
</form>
<form action="supprimer_compte.php" method="post" onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ? Cette action est irréversible.')">
    <button type="submit" class="logout" style="background:#d9534f;">Supprimer le compte</button>
</form>
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
<td><?= htmlspecialchars($f['titre'] ?? 'Non défini') ?></td>
<td><?= htmlspecialchars($f['categorie'] ?? 'Non défini') ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>

<a class="logout" href="logout.php">Déconnexion</a>
</div>
</body>
</html>
