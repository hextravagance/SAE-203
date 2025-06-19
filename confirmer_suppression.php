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
<?php require_once './includes/header.php'; ?>

<main class="container">
    <div class="auth-container">
        <h1>Suppression de compte</h1>
        <?php
        // Determine message type based on content
        $message_type = 'info'; // Default
        if (strpos(strtolower($message), 'supprimé') !== false && strpos(strtolower($message), 'bien') !== false) {
            $message_type = 'success';
        } elseif (strpos(strtolower($message), 'invalide') !== false || strpos(strtolower($message), 'aucun token') !== false) {
            $message_type = 'error';
        }
        ?>
        <div class="message <?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <div class="auth-links">
            <p><a href="index.php" class="button">Retour à l'accueil</a></p>
        </div>
    </div>
</main>

<?php require_once './includes/footer.php'; ?>
