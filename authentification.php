<?php
session_start();
require_once __DIR__ . './includes/config.php';

$erreur = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Recherche de l'utilisateur par email
    $stmt = $db->prepare("SELECT * FROM SAE203_user WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $erreur = "Identifiants invalides.";
    } elseif ($user['statut'] !== 'validé') {
        $erreur = "Compte non validé. Vérifiez vos emails.";
    } elseif (!password_verify($password, $user['mot_de_passe'])) {
        $erreur = "Mot de passe incorrect.";
    } else {
        // Connexion réussie
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
    <label>Email : <input type="email" name="email" required></label><br>
    <label>Mot de passe : <input type="password" name="password" required></label><br>
    <button type="submit">Se connecter</button>
</form>

<p>Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>

</body>
</html>
