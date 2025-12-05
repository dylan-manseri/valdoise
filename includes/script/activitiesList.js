// Liste des favoris depuis PHP
// Assurez-vous que sorties.php contient :
// <script>const userFavorites = <?= json_encode($userFavorites) ?>;</script>

const citiesSelect = document.getElementById("cities");
const searchInput = document.getElementById("searchInput");
const resultsDiv = document.getElementById("results");
const cities = {};

function display(eventsList){
    resultsDiv.innerHTML="";
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
        favBtn.style.cssText = "background:none; border:none; cursor:pointer; font-size:1.5em; margin-left:10px;";
        favBtn.addEventListener("click", () => toggleFavorite(favBtn));
        card.appendChild(favBtn);

        resultsDiv.appendChild(card);
    });
}

function toggleFavorite(btn){
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
        } else {
            alert("Erreur lors de la mise à jour des favoris");
        }
    });
}

// Récupération des activités
fetch("data/activitiesJson.php")
    .then(response => response.json())
    .then(data => {
        const eventsArray = Object.values(data);

        // Remplir le select villes
        eventsArray.forEach(event => {
            if(event.ville) cities[event.ville] = event.ville;
        });
        Object.values(cities).forEach(c => {
            const option = document.createElement("option");
            option.value = c;
            option.textContent = c;
            citiesSelect.appendChild(option);
        });

        // Recherche en temps réel
        searchInput.addEventListener("input", () => {
            const term = searchInput.value.toLowerCase().trim();
            const filtered = eventsArray.filter(ev => {
                const title = (ev.title ?? "").toLowerCase();
                const keywordMatch = Array.isArray(ev.keywords)
                    ?
