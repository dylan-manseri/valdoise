<?php
include "../includes/fonctions/activities.php";
header('Content-Type: application/json');
echo json_encode(getActivitiesOpenAgenda());
