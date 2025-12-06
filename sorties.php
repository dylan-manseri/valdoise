<?php
session_start();

$title = "Activités dans le Val-d'Oise";
$h1 = "Liste d'activités";
$css = "sortie";
$description = "Liste d'activités dans le Val-d'Oise";

include "includes/fonctions/activities.php";
include "includes/pageParts/header.php";

// Connexion à la base
require_once 'conf/bd_conf.php';

// Récupérer les favoris
$login = $_SESSION['login'] ?? null;
$userFavorites = [];
if ($login) {
    $stmt = $pdo->prepare("SELECT id_sortie FROM favoris WHERE user_login = ?");
    $stmt->execute([$login]);
    $userFavorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<section class="main-container">

    <!-- Recherche + sélecteur ville -->
    <div style="display: flex; gap: 5px; text-align: center; justify-content: center;">
        
        <div style="display:flex; flex-direction: column;">
            <label for="searchInput">Indiquez des mots clés</label>
            <div class="search">
                <span class="search-icon material-symbols-outlined">search</span>
                <input id="searchInput" class="search-input" type="search" placeholder="Rechercher">
            </div>
        </div>

        <div class="select-container">
            <label for="cities">Sélectionner une ville</label>
            <select id="cities">
                <option value="">-- Ville --</option>
            </select>
        </div>

    </div>

    <!-- Résultats -->
    <div id="results" style="padding-top:10px;"></div>
</section>

<?php include "includes/pageParts/footer.php"; ?>

<style>
.favorite-btn {
    background:none;
    border:none;
    cursor:pointer;
    font-size:1.5em;
    color:#ff0000;
    transition: transform 0.2s;
}
.favorite-btn:hover {
    transform: scale(1.3);
}
.card {
    border:1px solid #ddd;
    padding:10px;
    margin-bottom:10px;
    border-radius:5px;
    display:flex;
    gap:10px;
}
.infos {
    flex:1;
}
</style>

<script>
// Favoris depuis PHP
const userFavorites = <?= json_encode($userFavorites) ?>;

// DOM
const citiesSelect = document.getElementById("cities");
const searchInput = document.getElementById("searchInput");
const resultsDiv = document.getElementById("results");

const cities = {};

// Affichage
function display(eventsList) {
    resultsDiv.innerHTML = "";

    eventsList.forEach(event => {
        const card = document.createElement("div");
        card.classList.add("card");

        card.innerHTML = `
            <div style="width:150px; height:120px; background:#ccc;">
                <img src="${event.image}" alt="illustration" style="width:100%; height:100%; object-fit:cover;"/>
            </div>
            <div class="infos">
                <h2 style="margin:0 0 10px 0;">
                    <a href="detail_evenement.php?uid=${event.uid}" style="text-decoration:none; color:#333;">
                        ${event.title}
                    </a>
                </h2>
                <div>
                    <span style="background:#3498db; color:white; padding:3px 8px; border-radius:3px; font-size:0.8em;">
                        ${event.ville}
                    </span>
                    <span class="date-badge">
                        Le ${new Date(event.date).toLocaleDateString("fr-FR")}
                    </span>
                </div>
            </div>
        `;

        // Bouton favoris
        const favBtn = document.createElement("button");
        favBtn.className = "favorite-btn";
        favBtn.dataset.id = event.uid;
        favBtn.textContent = userFavorites.includes(event.uid) ? "❤️" : "♡";
        
        favBtn.addEventListener("click", () => toggleFavorite(favBtn));
        card.appendChild(favBtn);

        resultsDiv.appendChild(card);
    });
}

function toggleFavorite(btn) {
    const id = btn.dataset.id;

    fetch("toggleFavorite.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_sortie: id })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            btn.textContent = data.isFavorite ? "❤️" : "♡";

            if(data.isFavorite){
                if(!userFavorites.includes(id)) userFavorites.push(id);
            } else {
                const index = userFavorites.indexOf(id);
                if(index > -1) userFavorites.splice(index,1);
            }
        }
    });
}

// Chargement JSON
fetch("data/activitiesJson.php")
    .then(response => response.json())
    .then(data => {
        const eventsArray = Object.values(data);

        // Remplir les villes
        eventsArray.forEach(event => {
            if(event.ville) cities[event.ville] = event.ville;
        });

        Object.values(cities).forEach(c => {
            const option = document.createElement("option");
            option.value = c;
            option.textContent = c;
            citiesSelect.appendChild(option);
        });

        // Recherche + filtre ville
        function filter() {
            const term = searchInput.value.toLowerCase().trim();
            const city = citiesSelect.value;

            const filtered = eventsArray.filter(ev => {
                const title = (ev.title ?? "").toLowerCase();
                const keywordMatch = Array.isArray(ev.keywords)
                    ? ev.keywords.some(kw => kw.toLowerCase().includes(term))
                    : false;
                let cityMatch = true;

                if(city !== ""){
                    cityMatch = ev.ville && ev.ville.includes(city);
                }

                return (title.includes(term) || keywordMatch) && cityMatch;
            });

            display(filtered);
        }

        searchInput.addEventListener("input", filter);
        citiesSelect.addEventListener("change", filter);

        // Affichage initial
        display(eventsArray);
    });
</script>
