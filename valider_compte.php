<?php
require_once __DIR__ . '/includes/config.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    echo "<p>Token manquant.</p>";
    exit;
}

// Vérifie le token
$stmt = $db->prepare("SELECT id FROM SAE203_user WHERE token_validation = :token AND statut = 'non_validé'");
$stmt->execute([':token' => $token]);
$user = $stmt->fetch();

if ($user) {
    // Mise à jour du statut
    $update = $db->prepare("UPDATE SAE203_user SET statut = 'validé', token_validation = NULL WHERE id = :id");
    $update->execute([':id' => $user['id']]);
    echo "<p>Compte validé. Vous pouvez maintenant vous connecter.</p>";
    echo '<a href="authentification.php">Connexion</a>';
} else {
    echo "<p>Token invalide ou compte déjà validé.</p>";
}
?>
