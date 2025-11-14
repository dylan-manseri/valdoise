<?php
$dbFilePath = './mdp.json';
$token = $_GET['token'] ?? null;

if (empty($token) || strlen($token) !== 64) {
    $error = "Jeton de vérification invalide.";
    header("Location: connexion.php?error=" . urlencode($error));
    exit;
}

$users = file_exists($dbFilePath) ? json_decode(file_get_contents($dbFilePath), true) : [];
$userIndex = -1;

foreach ($users as $index => $user) {
    if (isset($user['verification_token']) && $user['verification_token'] === $token && ($user['is_verified'] === false || $user['is_verified'] === 0)) {
        $userIndex = $index;
        break;
    }
}

if ($userIndex !== -1) {
    $users[$userIndex]['is_verified'] = true;
    unset($users[$userIndex]['verification_token']);

    file_put_contents($dbFilePath, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);

    $message = "Votre compte est maintenant activé ! Vous pouvez vous connecter.";
    header("Location: connexion.php?message=" . urlencode($message));
    exit;

} else {
    $error = "Ce lien de vérification est invalide ou a expiré.";
    header("Location: connexion.php?error=" . urlencode($error));
    exit;
}
?>