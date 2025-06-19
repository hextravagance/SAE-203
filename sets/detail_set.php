<?php
session_start();
include '../includes/config.php';

if (!isset($_GET['id'])) {
    echo "Aucun ID de set fourni.";
    exit;
}

$id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM lego_db WHERE id_set_number = :id");
$stmt->execute(['id' => $id]);
$set = $stmt->fetch();

if (!$set) {
    echo "Set introuvable.";
    exit;
}

$is_connected = isset($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail du set <?= htmlspecialchars($set['set_name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        img { max-width: 300px; display: block; margin-bottom: 15px; }
        .back { margin-top: 20px; display: block; }
        .actions button { margin-right: 10px; padding: 10px 15px; }
    </style>
</head>
<body>
    <h1><?= htmlspecialchars($set['set_name']) ?></h1>
    <img src="<?= htmlspecialchars($set['image_url']) ?>" alt="<?= htmlspecialchars($set['set_name']) ?>">
    <p><strong>Matricule :</strong> <?= htmlspecialchars($set['id_set_number']) ?></p>
    <p><strong>Année :</strong> <?= htmlspecialchars($set['year_released']) ?></p>
    <p><strong>Nombre de pièces :</strong> <?= htmlspecialchars($set['number_of_parts']) ?></p>
    <p><strong>Thème :</strong> <?= htmlspecialchars($set['theme_name']) ?></p>

    <?php if ($is_connected): ?>
        <div class="actions">
            <button onclick="alert('Ajout à la wishlist non implémenté')">Ajouter à la Wishlist</button>
            <button onclick="alert('Ajout aux sets possédés non implémenté')">Ajouter aux Sets Possédés</button>
        </div>
    <?php else: ?>
        <p><em>Connectez-vous pour ajouter ce set à vos listes.</em></p>
        <a href="authentification.php">Se connecter</a>
    <?php endif; ?>

    <a class="back" href="sets.php">← Retour au catalogue</a>
</body>
</html>
