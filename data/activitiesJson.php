<?php
/**
 * Fichier : activitiesJson.php
 * Description : Récupération des activités pour JSON.
 */

include "../includes/fonctions/activities.php";
header('Content-Type: application/json');

try {
    $activities = getActivities();

    // Vérifier que $activities est un tableau
    if (!is_array($activities)) {
        $activities = []; // vide si problème
    }

    // Générer un ID unique pour chaque sortie si ce n'est pas déjà défini
    foreach ($activities as &$activity) {
        if (!isset($activity['id'])) {
            $activity['id'] = uniqid();
        }
        // Assurer que titre et description existent pour éviter les warnings
        if (!isset($activity['titre'])) $activity['titre'] = '';
        if (!isset($activity['description'])) $activity['description'] = '';
        if (!isset($activity['categorie'])) $activity['categorie'] = '';
    }
    unset($activity);

    echo json_encode($activities);

} catch (Exception $e) {
    // Si getActivities plante, renvoyer un tableau vide JSON
    echo json_encode([]);
}
