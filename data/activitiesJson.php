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
try {
    echo json_encode(getActivities());      // On écrit en json le tableau d'activité construit au préalable.
} catch (DateMalformedStringException $e) {
    echo "Erreur de formation de la date";
}
