<?php
// Consentement cookies
$cookieConsent = isset($_COOKIE['cookieConsent']) ? $_COOKIE['cookieConsent'] : null;

// Valeur par défaut
$style = "light";

//1. Si l'utilisateur met mode= dans l'URL : on l'utilise
if (isset($_GET["mode"]) && in_array($_GET["mode"], ["light", "dark"], true)) {
    $style = $_GET["mode"];

    // 2. On le stocke en cookie uniquement si consentement accepté
    if ($cookieConsent === 'true') {
        setcookie("style", $style, time() + 60*60*24*30, "/"); // 30 jours
    }
}
// 3. Sinon, si consentement accepté : lire le cookie "style" s'il est valide
elseif ($cookieConsent === 'true' && isset($_COOKIE['style']) && in_array($_COOKIE['style'], ['light', 'dark'], true)) {
    $style = $_COOKIE['style'];
}

// 4.Cookie date dernière visite
if ($cookieConsent === 'true' && isset($_COOKIE["date_last_visit"])) {
    $date = $_COOKIE["date_last_visit"];
    setcookie("date_last_visit", time(), time() + 60*60*24*30, "/");
}

// 5.bouton de bascule
$bascule = ($style === "light") ? "dark" : "light";

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title><?=$title?></title>
    <link rel="stylesheet" href="style/<?=$style?>/<?=$style?>.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="style/<?=$style?>/navbar.css" />
    <link rel="stylesheet" href="style/<?=$style?>/<?=$css?>.css" />
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=search" />
    <meta name="robots" content="index, follow">
    <meta name="msvalidate.01" content="3EAE8332F257463B9D8DE1208375E37B" />
    <meta name="google-site-verification" content="q-MMb7F1RGkafbyRqtY7RWspQVzYXJ4aCmvuIfNOxgs" />
    <meta name="description" content="<?=$description?>" />
</head>
<body>
<script>
    // Skip cookie banner for search engine crawlers
    if (navigator.userAgent.match(/bot|crawl|spider|bing|google/i)) {
        document.getElementById('cookie-banner').style.display = 'none';
    }
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i=0; i < ca.length; i++) {
            let c = ca[i].trim();
            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length, c.length);
            }
        }
        return null;
    }

    function acceptCookies() {
        // Consentement valable 30 jours
        setCookie('cookieConsent', 'true', 30);
        document.getElementById('cookie-banner').style.display = 'none';
        location.reload();
    }

    function refuseCookies() {
        // Consentement refusé valable 1 jour seulement
        setCookie('cookieConsent', 'false', 1);

        // Supprimer le cookie "style" s'il existe
        document.cookie = "style=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";

        document.getElementById('cookie-banner').style.display = 'none';
    }

    function showCookieBanner() {
        document.getElementById('cookie-banner').style.display = 'block';
    }

    // Script cookie
    window.addEventListener('load', function() {
        const consent = getCookie('cookieConsent');
        if (consent === null) {
            showCookieBanner();
        }

        const changeConsent = document.getElementById('change-consent');
        if (changeConsent) {
            changeConsent.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('cookie-banner').style.display = 'block';
            });
        }
    });

</script>

<header>
    <div class="logo">
        <a href="index.php">
            <img src="images/logo_sv.png" alt="icone du site"/>
        </a>
    </div>
    <nav>
        <ul>
            <li class="menu-deroulant">
                <a href="index.php#accueil">Explorer ▾</a>
                <div class="choice-list">
                    <a href="search.php">
                        <img src="images/header/<?=$style?>/search-text.webp" alt="icone de carte"/>
                    </a>
                    <a href="meteo.php">
                        <img src="images/header/<?=$style?>/search-map.webp" alt="icone de carte"/>
                    </a>
                </div>
            </li>
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
<?php if ($h1!=""):?>
<h1> <?=$h1?> </h1>
<?php endif;?>
<main>
    <section id="cookie-banner" style="display: none; position: fixed; bottom: 0; left: 25px; width: 50%; background: #333; color: #fff; padding: 15px; text-align: center; z-index: 9999;">
        <h2 style="font-size: medium">Ce site utilise des cookies pour améliorer votre expérience.</h2>
        <button onclick="acceptCookies()" style="margin-left: 10px; color: #00D000">Accepter</button>
        <button onclick="refuseCookies()" style="color: red">Refuser</button>
    </section>