<?php
/**
 * Fichier : activitiesJson.php
 * Description :    Fichier stockant les activités récupéré (cf. fonctions/activities.php).
 *                  L'intérêt est de pouvoir les manipuler via js, sans avoir à réaliser de multiples requête navigateur.
 * Auteur : Dylan Manseri
 * Date : 23/11/2025
 */

include "../includes/fonctions/activities.php";
header('Content-Type: application/json');   // On définit la structure de la page (json)

$cacheFile = "../cache/activities.json";

$cacheDuration = 24 * 3600;
if(file_exists($cacheFile)){
    $age = time() - filemtime($cacheFile);
    if($age < $cacheDuration){
        echo file_get_contents($cacheFile);
    }
}
else{
    $activities = null;
    try {
        $activities = json_encode(getActivities()); // On écrit en json le tableau d'activité construit au préalable.
    } catch (DateMalformedStringException $e) {

    }

    file_put_contents($cacheFile, $activities);
    echo $activities;
}


