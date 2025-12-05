<?php
/**
 * @file footer.php
 * Footer de toute les page du site
 */
?>

</main>
<footer class="main-footer">
    <div class="footer-content">
        <div class="footer-social">
            <a href="https://x.com/EtTemps81032" target="_blank">
                <img src="images/footer/twitter.webp" alt="X"/>
            </a>
            <a href="https://www.instagram.com/cielettemps_officiel" target="_blank">
                <img src="images/footer/instagram.webp" alt="Instagram"/>
            </a>
            <a href="https://www.youtube.com/@CielEtTemps-Officiel" target="_blank">
                <img src="images/footer/youtube.webp" alt="YouTube"/>
            </a>
        </div>

        <div class="footer-column">
            <p>🌐 SortieValdoise</p>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="carte.php">Carte d'activités</a></li>
                <li><a href="stats.php">Mes activités</a></li>
                <li><a href="about.php">À propos</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <p>⚙️ Fonctionnalités</p>
            <ul>
                <li><a href="meteo.php#map">Recherche par mot clé/ville</a></li>
                <li><a href="index.php#locateWeather">Ajout d'activités</a></li>
                <?php if (!isset($_GET["style"]) || $_GET["style"] == "light"): ?>
                    <li><a href="?style=dark">Changement de mode visuel</a></li>
                <?php else: ?>
                    <li><a href="?style=light">Changement de mode visuel</a></li>
                <?php endif; ?>
                <li><a href="cookies.php">Traitement des cookies</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <p>🗂️ Ressources</p>
            <ul>
                <li><a href="sitemap.php">Sitemap</a></li>
                <li><a href="mentions.php">Mentions légales</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="index.php#faq">FAQ</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>Réalisé par Dylan Manseri et Amadou Bawol — Licence 2 Informatique, CY Cergy Paris Université</p>
        <p>© Copyright 2025</p>
    </div>
</footer>
</body>