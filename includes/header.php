<?php
    // Consentement cookies
    $cookieConsent = isset($_COOKIE['cookieConsent']) ? $_COOKIE['cookieConsent'] : null;

    // Valeur par défaut
    $style = "clair";

    //1. Si l'utilisateur met mode= dans l'URL : on l'utilise
    if (isset($_GET["mode"]) && in_array($_GET["mode"], ["clair", "sombre"], true)) {
        $style = $_GET["mode"];

        // 2. On le stocke en cookie uniquement si consentement accepté
        if ($cookieConsent === 'true') {
            setcookie("style", $style, time() + 60*60*24*30, "/"); // 30 jours
        }
    }
    // 3. Sinon, si consentement accepté : lire le cookie "style" s'il est valide
    elseif ($cookieConsent === 'true' && isset($_COOKIE['style']) && in_array($_COOKIE['style'], ['clair', 'sombre'], true)) {
        $style = $_COOKIE['style'];
    }

    // 4.Cookie date dernière visite
    if ($cookieConsent === 'true' && isset($_COOKIE["date_last_visit"])) {
        $date = $_COOKIE["date_last_visit"];
        setcookie("date_last_visit", time(), time() + 60*60*24*30, "/");
    }
    
    // 5.bouton de bascule
    $bascule = ($style === "clair") ? "sombre" : "clair";

    $title="";

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title><?=$title?></title>
    <link rel="stylesheet" href="style/<?=$style?>.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <script src="script.js"></script>
</head>
<body>
    <script>
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
        <nav class="main-nav" style="display: flex; align-items: center; justify-content: space-between;">
            <ul class="list-nav">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="connexion.php">Connexion</a></li>
            </ul>

		</nav>
        <span style="position: fixed; bottom: 110px; right: 16px;">
            <a href="?mode=<?=$bascule?>" aria-label="Changer le style"><img src="images/<?=$style?>.png" alt="Changer style" /></a>
        </span>

        <span>
       		<a href="#" title="Retour en haut de page" class="back-to-top">↑</a>
    	</span>
    </header>

    <main>
        <section id="cookie-banner" style="display: none; position: fixed; bottom: 0; left: 25px; width: 50%; background: #333; color: #fff; padding: 15px; text-align: center; z-index: 9999;">
            <h2 style="font-size: medium">Ce site utilise des cookies pour améliorer votre expérience.</h2>
            <button onclick="acceptCookies()" style="margin-left: 10px; color: #00D000">Accepter</button>
            <button onclick="refuseCookies()" style="color: red">Refuser</button>
        </section>
