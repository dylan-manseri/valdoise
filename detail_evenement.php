<?php
$id = $_GET['id'] ?? 0;
?>

<html>
<head>
    <meta charset="UTF-8">
</head>
<body>

<h1>Détail de l'événement</h1>
<div id="detail"></div>

<script>

const eventId = <?= json_encode($id) ?>;
const actList = [];
let fini = 0;



/* --- CHARGER OPEN AGENDA --- */
function chargerAgendaOpen(agendaId, param){
    fetch("https://api.openagenda.com/v2/agendas/" + agendaId + "/events?key=808e208d9b9144a289e3655652d24d0f" + param)
        .then(r => r.json())
        .then(data => {
            data.events.forEach(event => {
                actList.push({
                    title : event.title.fr,
                    desc  : event.description?.fr,
                    image : event.image.base + event.image.filename,
                });
            });
            finChargement(); // <<< important
        });
}

/* --- CHARGER DATA IDF --- */
function chargerDataAgenda(urlFile){
    fetch(urlFile)
        .then(r => r.text())
        .then(apiUrl => {
            const year = new Date().getFullYear();
            const yearS = '"' + year + '"';

            fetch(apiUrl + yearS)
                .then(r => r.json())
                .then(json => {
                    const total = json.total_count;
                    let offset = 0;

                    // On fait tous les fetch jusqu'à atteindre total_count
                    function fetchBloc(){
                        fetch(apiUrl + yearS + "&offset=" + offset)
                            .then(r => r.json())
                            .then(data => {
                                data.results.forEach(result => {
                                    const raw = result.firstdate_begin;
                                    const dateOnly = raw.split("T")[0];
                                    const eventDate = new Date(dateOnly);
                                    const today = new Date();

                                    if(eventDate >= today){
                                        actList.push({
                                            title : result.title_fr,
                                            desc  : result.description_fr,
                                            image : result.image,
                                            date  : eventDate
                                        });
                                    }
                                });

                                offset += 100;
                                if(offset < total){
                                    fetchBloc(); // continuer
                                } else {
                                    finChargement(); // <<< fini entièrement
                                }
                            });
                    }

                    fetchBloc();
                });
        });
}

/* --- AFFICHAGE --- */
function afficherListe(){
    const c = document.getElementById("liste-sorties");
    c.innerHTML = "";

    actList.forEach((evt, index) => {
        const div = document.createElement("div");
        div.innerHTML = `
            <h2><a href="detail_evenement.php?id=${index}">${evt.title}</a></h2>
            ${evt.image ? `<img src="${evt.image}" width="200">` : ""}
            <p>${evt.desc ?? ""}</p>
            ${evt.date ? `<p><strong>Date :</strong> ${evt.date.toLocaleDateString()}</p>` : ""}
            <hr>
        `;
        c.appendChild(div);
    });
}



function afficherDetail(){
    const evt = actList[eventId];
    const d = document.getElementById("detail");

    d.innerHTML = `
        <h2>${evt.title}</h2>
        ${evt.image ? `<img src="${evt.image}" width="300">` : ""}
        <p>${evt.desc ?? ""}</p>
        ${evt.date ? `<p><strong>Date :</strong> ${evt.date.toLocaleDateString()}</p>` : ""}
        <br><a href="sorties.php">← Retour aux sorties</a>
    `;
}

// Lancer les chargements
chargerAgendaOpen(56500817, "&department[]=Val-d%27Oise");
chargerAgendaOpen(90134339, "");
chargerAgendaOpen(2624769, "");
chargerDataAgenda("./includes/script/url.txt");


    afficherDetail();
</script>

</body>
</html>
