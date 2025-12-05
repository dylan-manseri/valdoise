<?php
/**
 * Fichier : activities.php
 * Description :    Contient toutes les fonctions relative à la manipulation des APIs OpenDataIleDeFrance et OpenAgenda.
 *                  Traite les flux, manipule les données pour obtenir les activités sous un format trié et utilisable.
 * Auteur : Dylan Manseri
 * Date : 23/11/2025
 */

ini_set("log_errors", 1);
ini_set("error_log", "C:/wamp64/www/debug.log");

/**
 * Construit un tableau associatif des principales information de l'événement selon OpenAgendaAPI
 * @param $event l'évènement tel qu'il nous est donné dans l'API
 * @return array tableau des informations
 */
function buildArrayOpenAgenda($event) : array
{
    $infos['uid'] = $event['uid'];
    $infos['ville'] = $event['location']['city'] ?? null;
    $infos['source'] = 'OpenAgendaAPI';
    $infos['lat'] = $event['location']['latitude'];
    $infos['lng'] = $event['location']['longitude'];
    $infos['title'] = $event['title']['fr'] ?? null;
    $infos['description'] = $event['description']['fr'] ?? null;
    if (is_array($event['image'])) {
        $infos['image'] = ($event['image']['base'] ?? '') . ($event['image']['filename'] ?? '');
    } else {
        $infos['image'] = $event['image'] ?? "";
    }
    $infos['keywords'] = $event['keywords']['fr'] ?? null;
    $infos['date'] = explode("T", $event["lastTiming"]["end"])[0];
    return $infos;
}

/**
 * Cas particulier d'insertion, fonction utilisé dans le seul cas où un évènement à inséré possède le même nom qu'un déjà présent dans le tableau
 * Soit il s'agit du premier évènement de ce type rencontrer deux fois, auquel cas on construit un sous tableau dans la tableau des activités
 * Soit d'autres évènement du même nom ont déjà été traité, auquel cas on les compare et on réalise l'insertion (si nécessaire cf. fonction compare)
 * @param mixed $activities
 * @param array $aInserer
 * @return void
 */
function inserSameTitle(mixed &$activities, array $aInserer): void
{
    $title = $aInserer['title'];
    if(!(is_array($activities[$title][0] ?? null))){    // L'évènement est le premier doublon de ce type
        $r = compare($activities[$title], $aInserer);         // On compare les deux évènements pour connaitre le type d'insertion
        switch ($r){
            case 1:         // On crée un sous tableaux pour regroupé tous les évènement du même nom
                $infoTmp = $activities[$title];
                unset($activities[$title]);
                $activities[$title][] = $infoTmp;
                $activities[$title][] = $aInserer;
                break;
            case 2 :        // Cas où les deux évènement sont les même, on n'insère rien
                break;
            case 3 :        // Cas où les deux évènement sont les même mais un évènement possède plus d'information que l'autre
                $activities[$title] = $aInserer;
                break;
        }
    }
    else{       // Un sous tableau a déjà été crée précédemment, on les compare avec notre nouvelle recrue pour l'insertion
        $i=0;
        $arret = false;
        while(isset($activities[$title][$i]) && !$arret){
            $r = compare($activities[$title][$i], $aInserer);
            switch ($r){
                case 1:
                    break;
                case 2:
                    $arret = true;
                    break;
                case 3:
                    $activities[$title][$i] = $aInserer;
                    break;
            }
            $i++;
        }
        if(!isset($activities[$title][$i])){    // Si l'évènement est different des autres, on l'insère à la dernier place du sous tableau
            $activities[$title][] = $aInserer;
        }
    }
}

/**
 * Compare deux évènements supposé similaire pour connaitre le cas d'insertion
 * Cas 1 : Les deux évènement sont different, on insère.
 * Cas 2 : Les deux évènements sont les même, on ne fait rien.
 * Cas 3 : Les deux évènements sont les même mais un possède plus d'information que l'autre donc on le remplace.
 * @param array $infos
 * @param mixed $aInserer
 * @return int
 */
function compare(array $infos, mixed $aInserer): int
{
    if($infos['lat'] != $aInserer['lat']){
        return 1;
    }
    if($infos['lng'] != $aInserer['lng']){
        return 1;
    }
    if($infos['keywords'] != $aInserer['keywords']){
        if(empty($infos['keywords']) && !empty($aInserer['keywords'])){
            return 3;
        }
    }
    return 2;
}

/**
 * Lie et filtre les activités fournit par l'API OpenAgenda
 * On prend les activités après la date d'aujourd'hui.
 * @throws DateMalformedStringException
 */
function readActivities($agendaId, &$activities): void
{
    $baseUrl="https://api.openagenda.com/v2/agendas/".$agendaId."/events?key=808e208d9b9144a289e3655652d24d0f&department[]=Val-d%27Oise&sort=lastTiming.asc&size=300";
    $after = [];        // Variable utile à la pagination, l'API fournit 300 données par pages, au delà il faut modifier l'URL pour accéder à la page suivante
    do {
        $url = $baseUrl;
        if(!empty($after)) {    // On ajoute after à notre URL pour se retrouver sur la bonne page
            foreach($after as $a){
                $url.="&after[]".$a;
            }
        }
        $json = file_get_contents($url);
        $data = json_decode($json, true);
        $event = $data["events"];
        $arret = false;
        $i=0;
        $lastDate = new DateTime();
        $lastDate->setTime(0, 0, 0);
        while(isset($event[$i]) && !$arret){
            $fullDate = $event[$i]["lastTiming"]["end"];
            $eventDate = explode('T',$fullDate)[0];
            if(verifyDate($eventDate, $lastDate)){      // On vérifie si l'activité est après aujourd'hui
                $infos = buildArrayOpenAgenda($event[$i]);  // On construit son tableau d'information
                if(!empty($activities[$infos['title']])){
                    inserSameTitle($activities, $infos);    // Si une activité avec le même nom existe on regarde au cas par cas
                }
                else{
                    $activities[$infos['title']] = $infos;      // Sinon on insère dans le tableau d'activité
                }
                $lastDate = new DateTime($eventDate);
            }
            else{   // La pagination étant infinie, on s'arrête dés que notre filtre des jours ne fais plus effet cad l'API fournit des activité > aujourd'hui
                $arret = true;
            }
            $i++;
        }
        if(!empty($data['after'])){
            $after = $data["after"];
        }
    }while(!$arret);
}



/**
 * Fonction qui récupère les activités via l'API OpenAgenda en fonction de plusieurs agendas stocké dans agendaId.json
 * @throws DateMalformedStringException
 */
function getActivitiesOpenAgenda(&$activities): void
{
    $json = file_get_contents("../includes/script/agendaId.json");
    $agendas = json_decode($json);
    $activities = array();
    foreach($agendas as $agendaId){
        readActivities($agendaId, $activities);
    }
}

/**
 * Construit un tableau associatif des principales information de l'événement selon OpenDataIleDeFranceAPI
 * @param $event l'évènement tel qu'il nous est donné dans l'API
 * @return array tableau des informations
 */
function buildArrayDataIDF($event): array
{
    $infos['ville'] = $event['location_city'] ?? null;
    $infos['uid'] = $event['uid'];
    $infos['sources'] = 'DataIleDeFranceAPI';
    $infos['lat'] = $event['location_coordinates']['lat'];
    $infos['lng'] = $event['location_coordinates']['lon'];
    $infos['title'] = $event['title_fr'];
    $infos['description'] = $event['description_fr'];
    $infos['image'] = $event['image'];
    $infos['keywords'] = $event['keywords_fr'];
    $infos['date'] = explode("T",$event['lastdate_end'])[0];
    return $infos;
}

/**
 * Vérifie si la date de l'évènement est bien supérieur à la date d'aujourd'hui.
 * @throws DateMalformedStringException
 */
function verifyDate($eventDate, $lastDate) : bool
{
    $date = new DateTime($eventDate);
    $date->setTime(0, 0, 0);
    return $date >= $lastDate;
}

/**
 * Lie et filtre les activités fournit par l'API OpenDataIleDeFranceAPI.
 * On prend les activités après la date d'aujourd'hui.
 * @param $activities
 * @return void
 */
function getActivitiesDataIDF(&$activities) : void
{
    $url = "https://data.iledefrance.fr/api/explore/v2.1/catalog/datasets/evenements-publics-cibul/records?limit=100&refine=location_department%3A%22Val-d%27Oise%22&where=lastdate_begin%20%3E=%20";
    $year = '"'.date("Y-m-d").'"';
    $i =0;
    $json = file_get_contents($url.$year."&offset=".$i);    // On utilise offset pour la pagination du flux
    $data = json_decode($json, true);
    $count = $data['total_count'];
    while ($count != 0) {
        foreach ($data["results"] as $event) {
            $infos = buildArrayDataIDF($event);
            if(!empty($activities[$infos['title']])){
                inserSameTitle($activities, $infos);
            }
            else{
                $activities[$infos['title']] = $infos;
            }
        }
        if ($count > 100) {
            $i += 100;
            $count -= 100;
            $data = file_get_contents($url . $year . "&offset=" . $i);
        } else {
            $count = 0;
        }
    }
}

/**
 * Fonction qui récupère les activités des deux APIs et les stockes dans un même tableau associatif.
 * Dans ce tableaux on y trouve toutes leurs informations.
 * L'algorithme gère les dates et les doublons.
 * @throws DateMalformedStringException
 */
function getActivities(): array
{
    $activities = array();
    getActivitiesOpenAgenda($activities);   // Attention, passage par adresse.
    getActivitiesDataIDF($activities);
    return $activities;
}

/**
 * Renvoie toutes les villes des activités sous forme d'un select
 * @return string : le select
 */
function getAllCities(): string
{
    $json = file_get_contents("https://sortievaldoise.alwaysdata.net/data/activitiesJson.php");
    $data = json_decode($json, true);
    $select = "<select name='cities' id='cities'>";

    foreach($data as $event){
        if (isset($event['ville'])) $city[$event['ville']] = $event['ville'];
    }
    foreach($city as $c){
        $select .= "<option value='".$c."'>".$c."</option>";
    }
    $select .= "</select>";
    return $select;
}