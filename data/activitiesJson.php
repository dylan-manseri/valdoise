<?php
include "../includes/fonctions/activities.php";
header('Content-Type: application/json');

$cacheFile = "../cache/activities.json";
$cacheDuration = 24 * 3600;

if(file_exists($cacheFile)){
    $age = time() - filemtime($cacheFile);
    if($age < $cacheDuration){
        echo file_get_contents($cacheFile);
        exit;
    }
}

try {
    $activities = getActivities();

    if (!is_array($activities)) {
        $activities = [];
    }

    foreach ($activities as &$activity) {
        if (!isset($activity['id'])) $activity['id'] = uniqid();
        if (!isset($activity['titre'])) $activity['titre'] = '';
        if (!isset($activity['description'])) $activity['description'] = '';
        if (!isset($activity['categorie'])) $activity['categorie'] = '';
    }
    unset($activity);

    file_put_contents($cacheFile, json_encode($activities));
    echo json_encode($activities);

} catch (Exception $e) {
    echo json_encode([]);
}
