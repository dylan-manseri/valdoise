// Définition des constantes
const actList = [];         // Liste complète des activités et leurs infos
const iconNormal = L.icon({     // L'icône du pin
    iconUrl: "images/icons/pin.png",
    iconSize : [35,35],
    iconAnchor: [15, 35]
})

const iconHover = L.icon({      // Le pin en hoover
    iconUrl: "images/icons/pin.png",
    iconSize : [50,50],
    iconAnchor : [22, 45]
})

const agendas = [       // Liste des agendas que l'on utilise
    56500817,
    90134339,
    2624769,
]

/**
 * Fonction d'insertion de pin sur la carte
 * @param lat la latitude
 * @param lon la longitude
 * @param title le titre de l'activité qui sera affiché sur le panneau
 */
function insertPin(lat, lon, title){
    const marker = L.marker([lat,lon], {    // On insert le pin avec une icone
        icon : iconNormal
    }).addTo(map);
    marker.bindTooltip(title, {                               // On ajoute un panneau au survol avec le titre
        permanent: false,
        direction : "top",
        offset: [0,-45]
    });
    marker.on("mouseover", () => {                      // On agrandit au passage de la souris
        marker.setIcon(iconHover);
    })

    marker.on("mouseout", () => {                       // On rétrécit quand on enlève la souris
        marker.setIcon(iconNormal);
    })

    marker.on("click", () => {                          // On zoom au clic
        map.setView([lat, lon], 16, { animate: true});
    })
}

/**
 * Fonction récupérant les activités de l'agenda OpenAgenda API
 * @param agendaId  ID de l'agenda sur OpenAgenda
 * @param param     Paramètre appliqué sur l'url
 */
function chargerAgendaOpen(agendaId, param){
    fetch("https://api.openagenda.com/v2/agendas/"+agendaId+"/events?key=808e208d9b9144a289e3655652d24d0f"+param)
        .then(r => r.json())
        .then(data => {
            data.events.forEach(event => {
                const infos = {
                    lat: event.location.latitude,
                    long: event.location.longitude,
                    title: event.title.fr,
                    desc: event.description?.fr,
                    image: event.image.base + event.image.filename,
                    keywords: event.keywords?.fr,
                }
                actList.push(infos);
                insertPin(infos.lat, infos.long, infos.title)
            })
        })
}

/**
 * Fonction récupérant les activités de l'API DataIleDeFrance filtré dans le Val-d'Oise
 * @param url   l'url API
 */
function chargerDataAgenda(url){
    let count
    let i=0
    const year = new Date().getFullYear();
    const yearS = '"'+year+'"';
    fetch(url+yearS).then(r => r.json()).then(data => {
        count = parseInt(data.total_count);                                 // On fait une premiere requête pour récupérer le nombre d'activités total
        while(count!==0){                                                   // On va fetch tant que le nbr total n'est pas atteint
            let arret = count > 100 ? 100 : count;
            fetch(url+yearS+"&offset="+i)                           // On précise l'offset pour recommencer le fetch là ou il s'était arrêté avant
                .then(r => r.json())
                .then(data => {
                    data.results.forEach(result => {
                        const raw = result.firstdate_begin;
                        const dateOnly = raw.split("T")[0];
                        const eventDate = new Date(dateOnly);
                        const today = new Date();
                        if(eventDate>= today){                          // On compare la date de l'event avec la date d'aujourd'huis
                            const infos = {
                                lat: result.location_coordinates.lat,
                                long: result.location_coordinates.lon,
                                title: result.title_fr,
                                desc: result.description_fr,
                                image: result.image,
                                keywords: result.keywords?.fr,
                                date : eventDate
                            }
                            actList.push(infos);
                            insertPin(infos.lat, infos.long, infos.title)   // On insert le pin
                        }
                    })
                })
            i+=100;
            count = count > 100 ? count-100 : 0;
        }
    })

}

// On exécute nos fonction
chargerAgendaOpen(56500817, "&department[]=Val-d%27Oise");
chargerAgendaOpen(90134339, "");
chargerAgendaOpen(2624769, "");
fetch("./includes/script/url.txt").then(r => r.text()).then(url => chargerDataAgenda(url));     //  On stocke l'url dans un fichier à part





