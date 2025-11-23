/**
 * @file pins.js
 * @description Gère l'affichage des pins des activités en faisant attention aux doublons de coordonnées.
 *
 * @author Dylan Manseri
 * @version 1.0
 * @date 23/11/2025
 */

/**
 * Tableau des activités récupéré via les APIs
 * @type {*[]}
 */
const actList = [];

/**
 * Icon classique de pin pour une activité seul sur ses coordonnées
 */
const iconNormal = L.icon({
    iconUrl: "images/icons/pin.png",
    iconSize : [35,35],
    iconAnchor: [15, 35]
})

/**
 * Icon hover de pin pour une activité seul sur ses coordonnées
 */
const iconHover = L.icon({
    iconUrl: "images/icons/pin.png",
    iconSize : [50,50],
    iconAnchor : [22, 45]
})

/**
 * Icon de pin pour des activités partageant les mêmes coordonnées
 */
const iconMultiple = L.icon({
    iconUrl: "images/icons/pinMultiple.png",
    iconSize : [40,40],
    iconAnchor: [20, 40]
})

/**
 * Icon hover de pin pour des activités partageant les mêmes coordonnées
 */
const iconMultipleHover = L.icon({
    iconUrl: "images/icons/pinMultiple.png",
    iconSize : [55,55],
    iconAnchor: [26, 55]
})

/**
 * Tableau clé valeur stockant les markers (value) et leurs coordonnées (key)
 * @type {{}}
 */
const markerMap = {};

/**
 * Dessine le pin le renvoie pret à être ajouté
 * @param icon  icon à mettre
 * @param iconHover icon à mettre en hover
 * @param title titre de l'activité (si seul)
 * @param lat
 * @param lng
 * @returns {*} le marker
 */
function drawPin(icon, iconHover, title, lat, lng){
    const marker = L.marker([lat,lng], {                      // On crée le pin avec une icon
        icon : icon
    });
    marker.bindTooltip(title, {                               // On ajoute un panneau au survol avec le titre
        permanent: false,
        direction : "top",
        offset: [0,-45]
    });
    marker.on("mouseover", () => {                      // On agrandit au passage de la souris
        marker.setIcon(iconHover);
    })

    marker.on("mouseout", () => {                       // On rétrécit quand on enlève la souris
        marker.setIcon(icon);
    })

    marker.on("click", () => {                          // On zoom au clic
        map.setView([lat, lng], 16, { animate: true});
    })
    return marker;
}

/**
 * Fonction d'insertion de pin sur la carte
 * On manipule en plus un tableau de pin, servant à ne pas ajouter deux pins au même endroit
 * @param lat la latitude
 * @param lng la longitude
 * @param title le titre de l'activité qui sera affiché sur le panneau
 */
function insertPin(lat, lng, title){
    const key = `${lat},${lng}`;
    if(markerMap[key]){     // Le pin existe déjà
        const marker = drawPin(iconMultiple, iconMultipleHover, "Plusieurs évènement en ce lieu", lat, lng)
        if(!Array.isArray(markerMap[key])){     // Est-ce le premier ayant la même coordonnée qu'un autre ?
            markerMap[key].remove();            // On supprime l'ancien pin
            markerMap[key] = [markerMap[key]];
            marker.addTo(map);                  // On dessine le nouveau pin correspondant aux multiples activités
        }
        markerMap[key].push(marker);
    }
    else{
        const marker = drawPin(iconNormal, iconHover, title, lat, lng);     // L'activité est seul avec ses coords, on l'ajoute normalement
        marker.addTo(map);
        markerMap[key] = marker;
    }
}

// On récupère les données du flux généré et on affiche les activités une par une
fetch("data/activitiesJson.php")
    .then(r => r.json())
    .then(data => {
        let i=0;
        Object.entries(data).forEach(([title, value]) => {
            if(!Array.isArray(value)){
                insertPin(value['lat'],value['lng'], value['title']);
            }
            else{
                value.forEach(e => {                                    // Cas où une activité a plusieurs fois le même nom
                    insertPin(e['lat'],e['lng'], e['title']);
                })
            }
            i++;
        })
        console.log(i);
    })






