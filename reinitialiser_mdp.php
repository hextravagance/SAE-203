<?php
session_start();
require './includes/config.php';

$message = '';
$show_form = true;

if (!isset($_GET['token'])) {
    $message = "Token manquant.";
    $show_form = false;
} else {
    $token = $_GET['token'];

    // Vérifier token valide et non expiré
    $stmt = $db->prepare("SELECT * FROM SAE203_user WHERE reset_token = :token AND reset_token_expiry > NOW()");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = "Lien invalide ou expiré.";
        $show_form = false;
    }
}

if ($show_form && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $message = "Veuillez remplir tous les champs.";
    } elseif ($new_password !== $confirm_password) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {
        // Mettre à jour mot de passe et supprimer token
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE SAE203_user SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id");
        $stmt->execute([':password' => $hashed, ':id' => $user['id']]);

        $message = "Mot de passe modifié avec succès. Vous pouvez maintenant vous connecter.";
        $show_form = false;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialiser le mot de passe</title>
</head>
<body>
    <h1>Réinitialiser le mot de passe</h1>
    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($show_form): ?>
        <form method="post">
            <label for="new_password">Nouveau mot de passe :</label><br>
            <input type="password" id="new_password" name="new_password" required><br>

            <label for="confirm_password">Confirmer le nouveau mot de passe :</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required><br>

            <button type="submit">Valider</button>
        </form>
    <?php endif; ?>

    <p><a href="authentification.php">Retour à la connexion</a></p>
</body>
</html>
