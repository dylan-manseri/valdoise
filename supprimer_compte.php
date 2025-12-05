<?php
session_start();
require_once 'conf/bd_conf.php';

if (!isset($_SESSION['login'])) {
    header('Location: index.php');
    exit;
}

$login = $_SESSION['login'];

$stmt = $pdo->prepare("DELETE FROM users WHERE login = ?");
$stmt->execute([$login]);

session_destroy();

header('Location: index.php');
exit;
