<?php
$pdo = new PDO('mysql:host=localhost;dbname=lego;charset=utf8', 'root', '');

$token = $_GET['token'] ?? '';

if (!$token) {
    echo "<p>Token manquant.</p>";
    exit;
}

// Vérifie le token
$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE token_validation = :token AND statut = 'non_validé'");
$stmt->execute([':token' => $token]);
$user = $stmt->fetch();

if ($user) {
    // Mise à jour du statut
    $update = $pdo->prepare("UPDATE utilisateurs SET statut = 'validé', token_validation = NULL WHERE id = :id");
    $update->execute([':id' => $user['id']]);
    echo "<p>Compte validé. Vous pouvez maintenant vous connecter.</p>";
    echo '<a href="authentification.php">Connexion</a>';
} else {
    echo "<p>Token invalide ou compte déjà validé.</p>";
}
