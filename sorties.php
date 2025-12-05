<?php
$h1="Sorties Val-d'Oise";
$css = "sortie";
$description="ici plein de choses intéressantes!";
include "includes/fonctions/activities.php";
include "includes/pageParts/header.php";
?>

    <section class="main-container">
        <form style="background:#f4f4f4; padding:15px; margin-bottom:20px; border-radius:5px;">
            <label>
                <select id="cities">
                    <option value="">-- Ville --</option>
                </select>
            </label>
            <button type="submit">Filtrer</button>
        </form>
        <form>
            <div class="search">
                <span class="search-icon material-symbols-outlined">search</span>
            <input id="searchInput" class="search-input" type="search" placeholder="Rechercher">
            </div>
        </form>
        <div id="results" style="padding-top: 10px;">

        </div>
    </section>

<?php include "includes/pageParts/footer.php" ?>
<script src="includes/script/activitiesList.js" defer></script>

</html>
