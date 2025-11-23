<?php

function buildArrayInfos($event) : array
{
    $infos['lat'] = $event['location']['latitude'];
    $infos['lng'] = $event['location']['longitude'] ?? "miaou";
    $infos['title'] = $event['title']['fr'] ?? null;
    $infos['description'] = $event['description']['fr'] ?? null;
    if (is_array($event['image'])) {
        $infos['image'] = ($event['image']['base'] ?? '') . ($event['image']['filename'] ?? '');
    } else {
        $infos['image'] = $event['image'] ?? "";
    }
    $infos['keywords'] = $event['keywords']['fr'] ?? null;
    return $infos;
}

function readActivities($agendaId, &$activities)
{
    $date = (new DateTime())->format('Y-m-d\TH:i:sP');
    $baseUrl="https://api.openagenda.com/v2/agendas/".$agendaId."/events?key=808e208d9b9144a289e3655652d24d0f&department[]=Val-d%27Oise&filters[timings.begin][gte]=".$date."&size=300";
    $after = [];
    $i=1;
    do {
        $url = $baseUrl;
        if(!empty($after)) {
            foreach($after as $a){
                $url.="&after[]=".urlencode($a);
            }
        }
        $json = file_get_contents($url);
        $data = json_decode($json, true);
        foreach($data['events'] as $event){
            $activities[] = buildArrayInfos($event);
        }
        if(!empty($data['after'])){
            $after = $data["after"];
        }
        $i=0;
    }while(/*!empty($after)*/ $i==1);
}

function getActivitiesOpenAgenda(): array
{
    $json = file_get_contents("../includes/script/agendaId.json");
    $agendas = json_decode($json);
    $activities = array();
    foreach($agendas as $agendaId){
        readActivities($agendaId, $activities);
    }
    return $activities;
}

function buildArray($event, $date): array
{
    $infos['lat'] = $event['location_coordinates']['lat'];
    $infos['lng'] = $event['location_coordinates']['lon'] ?? "miaou";
    $infos['title'] = $event['title']['fr'];
    $infos['description'] = $event['description_fr'];
    $infos['image'] = $event['image'];
    $infos['keywords'] = $event['keywords']['fr'];
    $infos['date'] = $date;
    return $infos;
}

/**
 * @throws DateMalformedStringException
 */
function verifyDate($event, $dateOnly) : bool
{
    $date = new DateTime($dateOnly);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    $date->setTime(0, 0, 0);
    return $date >= $today;
}

/**
 * @throws DateMalformedStringException
 */
function getActivitiesDataIDF() : array
{
    $url = "https://data.iledefrance.fr/api/explore/v2.1/catalog/datasets/evenements-publics-cibul/records?limit=100&refine=location_department%3A%22Val-d%27Oise%22&refine=firstdate_begin%3A";
    $year = "'".date("Y")."'";
    $i =0;
    $data = file_get_contents($url.$year."&offset=".$i);
    $count = $data['data']['total_count'];
    $activities = array();
    while ($count != 0) {
        foreach ($data["results"] as $event) {
            $raw = $event['firstdate_begin'];
            $date = explode('T', $raw, 2)[0];
            if (verifyDate($event, $date)) {
                $activities[] = buildArray($event, $date);
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
    return $activities;
}
