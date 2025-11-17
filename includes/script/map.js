/** @type {typeof import("leaflet")} */
var L;

/*--------------------------CREATION DE LA CARTE--------------------------*/

let map = L.map('map', {
    /*maxBounds: bounds,      // Limite la navigation
    maxBoundsViscosity: 1.0*/ // Effet rebondit quand on sort de la limite
}).setView([49.0616, 2.1581], 10);

L.DomUtil.get(map.getContainer()).style.background="white";
    // Fond de carte
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
}).addTo(map);

/*--------------------------DELIMITATION DU VAL D'OISE--------------------------*/
let coords;
fetch("https://public.opendatasoft.com/api/records/1.0/search/?dataset=georef-france-departement&rows=101&format=geojson")
    .then(f => f.json())
    .then(fc => {  // then va attendre que la dernière instruction est terminé avant d'exécuter la suite, il prend en paramètre la réponse de la dernière promesse
    // Filtrer le Val-d'Oise
    const dep = fc.features.find( f => f.properties.dep_code === "95");    // find prend en paramètre une fonction qui renvoie true ou false et va parcourir le tableau en appliquant à chaque élement la fonction

    coords = dep.properties.geo_shape.coordinates[0];
    let tmp;
    for(let i=0; i<coords.length; i++){
    tmp = coords[i][0];
    coords[i][0] = coords[i][1];
    coords[i][1] = tmp;
}

    // Ajout du contour

    const layer = L.polygon(coords, {
        stroke: false,
        fillOpacity: 0,
        weight: 0
    }).addTo(map);
    map.fitBounds(layer.getBounds());

        const world = [
            [-90, -180],
            [-90,  180],
            [ 90,  180],
            [ 90, -180]
        ];

        const mask = L.polygon([world,coords], {
            fillColor: "white",
            fillOpacity: 1,
            color: "#636363"
        }).addTo(map);

        map.setMaxBounds(layer.getBounds());
        map.setMinZoom(map.getBoundsZoom(layer.getBounds()));
});

/*--------------------------FOND DE CARTE --------------------------*/
