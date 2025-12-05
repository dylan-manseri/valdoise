<?php
$h1="Carte des activités";
$title="Carte des activités";
$description="Les activité du Val-d'Oise affiché sur une carte.";
$css="carte";
include "includes/pageParts/header.php";
?>
<!-- Import Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <div id="map"></div>

    <?php

    ?>
    <script src="includes/script/map.js" defer></script>
    <script src="includes/script/pins.js" defer></script>
</body>
</html>