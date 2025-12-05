function display(eventsList){
    const results = document.getElementById("results");
    results.innerHTML="";
    eventsList.forEach(event => {
        const div = document.createElement("div")
        const card = document.createElement("div");
        card.classList.add("card");

        card.innerHTML = `
            <div style="width:150px; height:120px; background:#ccc;"><img src="${event.image}" alt="illustration"/></div>
            <div class="infos">
                <h2 style="margin:0 0 10px 0;font-family: 'Playfair Display', serif;">
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
        results.appendChild(card);
    })
}

fetch("data/activitiesJson.php")
    .then(response => response.json())
    .then(data => {
        const eventsArray = Object.values(data);
        const searchInput = document.getElementById("searchInput");
        const selectCities = document.getElementById("cities");

        eventsArray.forEach(event => {
            if (event.ville !== undefined && event.ville !== null && event.ville !== "") {
                cities[event.ville] = event.ville;
            }
        });
        Object.values(cities).forEach(c => {
            const option = document.createElement("option");
            option.value = c;
            option.textContent = c;
            selectCities.appendChild(option);
        })

        searchInput.addEventListener("input", () => {
            if(searchInput.value === ""){
                display(eventsArray);
            }
            document.createElement("div").innerHTML="";
            const term = searchInput.value.toLowerCase().trim();
            let i = 0
            const filtered = eventsArray.filter(ev => {
                const title = (ev.title ?? "").toLowerCase();
                const keywordMatch = Array.isArray(ev.keywords)
                    ? ev.keywords.some(kw => kw.toLowerCase().includes(term))
                    : false;
                let cityTest = true;
                if(selectCities.value !== ""){
                    if(ev.ville){
                        cityTest = ev.ville.includes(selectCities.value);
                    }
                }
                return (title.includes(term) || keywordMatch) && cityTest;
            });
            display(filtered);
        });

        selectCities.addEventListener("change", () => {
            if(selectCities.value !== "" && searchInput.value === ""){
                const actByCity = eventsArray.filter(ev => {
                    if(ev.ville){
                        return ev.ville.includes(selectCities.value);
                    }
                })
                display(actByCity);
            }
        })
        display(eventsArray);
    })