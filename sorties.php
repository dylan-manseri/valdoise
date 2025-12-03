<?php

//clé de l'api
$apiKey = "808e208d9b9144a289e3655652d24d0f";
//  3 agendas : à revoir
$agendaIds = ['56500817', '90134339', '2624769'];

//
function chargerAgenda($id, $key) {
    // filtre département dans l'URL
    $params = [
        'key' => $key,
        'size' => 200,
        'department' => ["Val-d'Oise"] 
    ];
    
    $url = "https://api.openagenda.com/v2/agendas/{$id}/events?" . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($result, true);
    return $data['events'] ?? [];
}


// recup de la data
$tousLesEvenements = [];

foreach ($agendaIds as $id) {
    $events = chargerAgenda($id, $apiKey);
    
    foreach ($events as $evt) {
        
        $ville = $evt['location']['city'] ?? '';
        $adresse = $evt['location']['address'] ?? '';
        $cp = $evt['location']['postalCode'] ?? ''; 

        // Si le code postal ne commence pas par 95 et l'adresse ne contient pas "95" on ignore.
        if (strpos($cp, '95') !== 0 && strpos($adresse, '95') === false) {
            continue; 
        }

        // image =base + filename 
        $imageFull = null;
        if (isset($evt['image']['base']) && isset($evt['image']['filename'])) {
            $imageFull = $evt['image']['base'] . $evt['image']['filename'];
        }

   
        $dateEvent = null;
        if (isset($evt['nextTiming']['begin'])) {
            $dateEvent = $evt['nextTiming']['begin'];
        } elseif (isset($evt['timings'][0]['start'])) {
            $dateEvent = $evt['timings'][0]['start'];
        }


        $uid = $evt['uid'];
        
        $tousLesEvenements[$uid] = [
            'uid'   => $uid,
            'titre' => $evt['title']['fr'] ?? $evt['title']['en'] ?? 'Sans titre',
            'desc'  => $evt['description']['fr'] ?? '',
            'image' => $imageFull,
            'ville' => $ville,
            'date'  => $dateEvent
        ];
    }
}

$recherche = $_GET['q'] ?? '';
$filtreVille = $_GET['ville'] ?? '';

$evenementsFiltres = array_filter($tousLesEvenements, function($evt) use ($recherche, $filtreVille) {
    if ($recherche && stripos($evt['titre'], $recherche) === false) return false;
    if ($filtreVille && stripos($evt['ville'], $filtreVille) === false) return false;
    return true;
});

// Tri par date
usort($evenementsFiltres, function($a, $b) {
    if (!$a['date']) return 1;
    if (!$b['date']) return -1;
    return strcmp($a['date'], $b['date']);
});

$villesDispo = array_unique(array_column($tousLesEvenements, 'ville'));
sort($villesDispo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Sorties 95</title>
    <style>
        body { font-family: sans-serif; max-width: 900px; margin: auto; padding: 20px; }
        .card { display: flex; gap: 15px; border: 1px solid #ddd; margin-bottom: 15px; padding: 15px; border-radius: 8px; }
        .card img { width: 150px; height: 120px; object-fit: cover; border-radius: 4px; }
        .infos { display: flex; flex-direction: column; justify-content: center; }
        .date-badge { font-weight: bold; color: #e67e22; }
    </style>
</head>
<body>
    <h1>Sorties Val-d'Oise</h1>
    
    <form style="background:#f4f4f4; padding:15px; margin-bottom:20px; border-radius:5px;">
        <select name="ville" style="padding:8px;">
            <option value="">Toutes les villes</option>
            <?php foreach($villesDispo as $v): ?>
                <option value="<?= htmlspecialchars($v) ?>" <?= $filtreVille == $v ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filtrer</button>
    </form>

    <?php foreach($evenementsFiltres as $evt): ?>
        <div class="card">
            <?php if($evt['image']): ?>
                <img src="<?= htmlspecialchars($evt['image']) ?>" alt="Affiche">
            <?php else: ?>
                <div style="width:150px; height:120px; background:#ccc;"></div>
            <?php endif; ?>
            
            <div class="infos">
                <h2 style="margin:0 0 10px 0;">
                    <a href="detail_evenement.php?uid=<?= $evt['uid'] ?>" style="text-decoration:none; color:#333;">
                        <?= htmlspecialchars($evt['titre']) ?>
                    </a>
                </h2>
                <div>
                    <span style="background:#3498db; color:white; padding:3px 8px; border-radius:3px; font-size:0.8em;"><?= htmlspecialchars($evt['ville']) ?></span>
                    <?php if($evt['date']): ?>
                        <span class="date-badge">Le <?= date("d/m/Y", strtotime($evt['date'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</body>
</html>