<?php
$title="Découvrez le 95";
$description="ici plein de choses intéressantes!";
$h1="";
$css = "index";
include "includes/pageParts/header.php";
?>
<div class="carousel">
    <h1 class="carousel-title">Découvrez des activités dans le Val-d'Oise.</h1>
    <button class="btn-decouvrir carousel-btn" onclick="window.location.href='carte.php'">Découvrir</button>
    <div class="group">
        <div class="card">
            <img src="images/landscape/arbre_valdoise.jpg" alt="image foret"/>
        </div>
        <div class="card">
            <img src="images/landscape/theatre.jpg" alt="image banc"/>
        </div>
        <div class="card">
            <img src="images/landscape/banc_valdoise.jpg" alt="image banc"/>
        </div>
        <div class="card">
            <div class="card">
                <img src="images/landscape/mediatheque.jpg" alt="image foret"/>
            </div>
        </div>
    </div>
    <div aria-hidden class="group">
        <div class="card">
            <div class="card">
                <img src="images/landscape/arbre_valdoise.jpg" alt="image foret"/>
            </div>
        </div>
        <div class="card">
            <img src="images/landscape/theatre.jpg" alt="image banc"/>
        </div>
        <div class="card">3</div>
        <div class="card">4</div>
    </div>
</div>
    <section class="default-section" style="margin: 10% auto 10% auto">
        <h2 class="h2-presentation">S'amuser devient simple</h2>
        <p style="font-size: 20px; margin-bottom: 20px">
            Bienvenue sur <strong>SortieValdoise</strong>, votre plateforme de gestion de sortie dans le Val-d'Oise.
            Ce site vous permet de consulter en temps réel les <strong>activités</strong> de votre département.
            De plus grâce à notre système d<strong>'analyse météo</strong>, vous pouvez connaître immédiatement la météo là où vous rechercher des activités.
        </p>
        <p style="font-size: 20px; margin-bottom: 20px">
            <strong>Que l’on ait envie de s’évader au grand air ou de découvrir des lieux chargés d’histoire</strong>, le Val-d’Oise offre une richesse
            de sorties qui rythment <strong>notre quotidien.</strong>
            Le département influence nos loisirs, nos envies de découverte, et parfois même notre façon de planifier nos week-ends.
            Explorer ses espaces verts, flâner dans ses villages, profiter <strong>d’activités culturelles</strong> ou
            <strong>familiales</strong> : sortir dans le Val-d’Oise,
            c’est varier les ambiances et se laisser surprendre. Comprendre ce que propose le territoire, c’est aussi mieux anticiper ses sorties,
            s’adapter à ses envies, et <strong>développer une véritable curiosité</strong> pour un département aux multiples facettes.
        </p>
        <p style="font-size: 20px; margin-bottom: 20px">
            Notre défi est de vous offrir un accès <strong>simple, clair</strong> et pratique aux <strong>activités</strong>
            de votre département, chaque jour.
        </p>
        <p style="font-size: 20px">
            Pour consulter les activités, vous pouvez consulter notre <strong>carte interactive</strong> du Val-d'Oise ou
            indiquer des mots clé via notre <strong>barre de recherche</strong>. Par ailleurs il vous est possible d'accéder à diverses informations liées
            <strong>aux statistiques</strong>,
            de notre site.
        </p>
    </section>


<?php
include "includes/pageParts/footer.php"
?>