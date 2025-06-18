<?php
require_once './includes/config.php';

$token = $_GET['token'] ?? '';
$message = '';

if ($token) {
    $stmt = $db->prepare("SELECT * FROM SAE203_user WHERE suppression_token = :token");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Supprimer l'utilisateur
        $stmt = $db->prepare("DELETE FROM SAE203_user WHERE id = :id");
        $stmt->execute([':id' => $user['id']]);
        $message = "Votre compte a bien été supprimé.";
    } else {
        $message = "Lien invalide ou compte déjà supprimé.";
    }
} else {
    $message = "Aucun token fourni.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suppression de compte</title>
</head>
<body>
    <h1>Suppression de compte</h1>
    <p style="color: green;"><?= htmlspecialchars($message) ?></p>
    <a href="index.php">Retour à l'accueil</a>
</body>
</html>
