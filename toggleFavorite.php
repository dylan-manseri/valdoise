<?php
session_start();
require_once 'conf/bd_conf.php';
header('Content-Type: application/json');

$login = $_SESSION['login'] ?? null;
if (!$login) {
    echo json_encode(['success'=>false]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id_sortie = $data['id_sortie'] ?? null;
if (!$id_sortie) {
    echo json_encode(['success'=>false]);
    exit;
}

// Vérifier si déjà favori
$stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE user_login=? AND id_sortie=?");
$stmt->execute([$login, $id_sortie]);
$isFav = $stmt->fetchColumn() > 0;

if ($isFav) {
    $del = $pdo->prepare("DELETE FROM favoris WHERE user_login=? AND id_sortie=?");
    $del->execute([$login, $id_sortie]);
    echo json_encode(['success'=>true, 'isFavorite'=>false]);
} else {
    $add = $pdo->prepare("INSERT INTO favoris (user_login,id_sortie) VALUES (?,?)");
    $add->execute([$login, $id_sortie]);
    echo json_encode(['success'=>true, 'isFavorite'=>true]);
}
