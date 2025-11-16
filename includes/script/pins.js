// Fonction dédié au chargement de l'agenda et création du pin pour l'api opendataagenda
function chargerAgendaOpen(agendaId, param){
    fetch("https://api.openagenda.com/v2/agendas/"+agendaId+"/events?key=808e208d9b9144a289e3655652d24d0f"+param)
        .then(r => r.json())
        .then(data => {
            data.events.forEach(event => {
                const infos = {
                    lat: event.location.latitude,
                    long: event.location.longitude,
                    //title: event.title.fr,
                    //desc: event.description?.fr,
                    //image: event.image.base + event.image.filename,
                    //keywords: event.keywords?.fr,
                }
                console.log(infos.lat);
                const marker = L.marker([infos.lat,infos.long]).addTo(map);
            })
        })
}

// Fonction dédié au chargement de l'agenda et création du pin pour l'api datailedefrance
function chargerDataAgenda(url){
    fetch(url)
        .then(r => r.json())
        .then(data => {
            data.results.forEach(result => {
                const infos = {
                    lat: result.location_coordinates.lat,
                    long: result.location_coordinates.lon,
                    //title: event.title.fr,
                    //desc: event.description?.fr,
                    //image: event.image.base + event.image.filename,
                    //keywords: event.keywords?.fr,
                }
                console.log(infos.lat);
                const marker = L.marker([infos.lat,infos.long]).addTo(map);
            })
        })
}

const agendas = [
    56500817,
    90134339,
    2624769,
]

fetch("./includes/script/url.txt").then(r => r.text());


chargerAgendaOpen(56500817, "&department[]=Val-d%27Oise");
chargerAgendaOpen(90134339, "");
chargerAgendaOpen(2624769, "");
fetch("./includes/script/url.txt").then(r => r.text()).then(url => chargerDataAgenda(url));



