<?php
include './includes/config.php';

$token = $_GET['token'] ?? '';
$message = '';

if ($token) {
    $stmt = $db->prepare("SELECT * FROM SAE203_user WHERE token_validation = :token");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user['statut'] === 'validé') {
            $message = "Ce compte est déjà validé.";
        } else {
            // Mise à jour du statut et suppression du token
            $stmt = $db->prepare("UPDATE SAE203_user SET statut = 'validé', token_validation = NULL WHERE id = :id");
            $stmt->execute([':id' => $user['id']]);
            $message = "Votre compte a bien été validé ! Vous pouvez maintenant vous connecter.";
        }
    } else {
        $message = "Lien de validation invalide ou expiré.";
    }
} else {
    $message = "Aucun token fourni.";
}
?>
<?php require_once './includes/header.php'; ?>

<main class="container">
    <div class="auth-container">
        <h1>Validation de compte</h1>

        <?php if ($message): ?>
            <div class="message <?= (strpos($message, 'validé !') !== false || strpos($message, 'déjà validé') !== false) ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="auth-links">
            <p><a href="authentification.php">Aller à la connexion</a></p>
        </div>
    </div>
</main>

<?php require_once './includes/footer.php'; ?>
