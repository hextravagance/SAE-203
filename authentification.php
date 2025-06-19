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
<?php require_once './includes/header.php'; ?>

<main class="container">
    <div class="auth-container">
        <h1>Connexion</h1>

        <?php if ($erreur): ?>
            <div class="message error"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="post" action="authentification.php">
            <div class="form-group">
                <label for="identifiant">Email ou nom d'utilisateur :</label>
                <input type="text" name="identifiant" id="identifiant" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>

        <div class="auth-links">
            <p><a href="mot_de_passe_oublie.php">Mot de passe oublié ?</a></p>
            <p>Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>
        </div>
    </div>
</main>

<?php require_once './includes/footer.php'; ?>
