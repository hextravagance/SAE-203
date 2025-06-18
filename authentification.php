<?php
session_start();
require_once __DIR__ . '/includes/config.php';

$erreur = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $password = $_POST['password'] ?? '';

    // Requête avec deux paramètres nommés différents
    $stmt = $db->prepare("SELECT * FROM SAE203_user WHERE email = :email OR username = :username");
    $stmt->execute([
        ':email' => $identifiant,
        ':username' => $identifiant
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $erreur = "Identifiants invalides.";
    } elseif ($user['statut'] !== 'validé') {
        $erreur = "Votre compte n'est pas encore validé. Veuillez vérifier votre email.";
    } elseif (!password_verify($password, $user['password'])) {
        $erreur = "Mot de passe incorrect.";
    } else {
        $_SESSION['id_utilisateur'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>

<h1>Connexion</h1>

<?php if ($erreur): ?>
    <p style="color:red"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="post" action="authentification.php">
    <label>Email ou nom d'utilisateur : <input type="text" name="identifiant" required></label><br>
    <label>Mot de passe : <input type="password" name="password" required></label><br>
    <p><a href="mot_de_passe_oublie.php">Mot de passe oublié ?</a></p>
    <button type="submit">Se connecter</button>
</form>

<p>Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>

</body>
</html>
