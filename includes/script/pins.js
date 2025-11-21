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
fetch("data/activitiesJson.php")
    .then(r => r.json())
    .then(data => {
        data.forEach($info => {
            insertPin($info['lat'],$info['lng'], $info['title']);
        })
    })





