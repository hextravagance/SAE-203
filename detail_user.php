<?php
require_once __DIR__ . '/includes/config.php';

// Récupération de l'ID utilisateur
$id_utilisateur = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Infos utilisateur
$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id = :id");
$stmt->execute([':id' => $id_utilisateur]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utilisateur) {
    echo "<p>Erreur : cet utilisateur n'existe pas.</p>";
    exit;
}

// Sets possédés
$sets_possedes_stmt = $db->prepare("
    SELECT s.id, s.nom FROM possession p
    JOIN sets s ON p.id_set = s.id
    WHERE p.id_utilisateur = :id
");
$sets_possedes_stmt->execute([':id' => $id_utilisateur]);
$sets_possedes = $sets_possedes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Sets wishlist
$sets_wishlist_stmt = $db->prepare("
    SELECT s.id, s.nom FROM wishlist w
    JOIN sets s ON w.id_set = s.id
    WHERE w.id_utilisateur = :id
");
$sets_wishlist_stmt->execute([':id' => $id_utilisateur]);
$sets_wishlist = $sets_wishlist_stmt->fetchAll(PDO::FETCH_ASSOC);

// Commentaires
$commentaires_stmt = $db->prepare("
    SELECT DISTINCT s.id, s.nom
    FROM commentaires c
    JOIN sets s ON c.id_set = s.id
    WHERE c.id_utilisateur = :id
");
$commentaires_stmt->execute([':id' => $id_utilisateur]);
$sets_commentes = $commentaires_stmt->fetchAll(PDO::FETCH_ASSOC);

// Nombre de commentaires
$nb_com_stmt = $db->prepare("SELECT COUNT(*) FROM commentaires WHERE id_utilisateur = :id");
$nb_com_stmt->execute([':id' => $id_utilisateur]);
$nb_commentaires = $nb_com_stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil utilisateur</title>
</head>
<body>

    <h1>Profil de <?= htmlspecialchars($utilisateur['username']) ?></h1>
    <p>Date d'inscription : <?= htmlspecialchars($utilisateur['date_inscription']) ?></p>

    <h2>Sets possédés</h2>
    <?php if (count($sets_possedes) > 0): ?>
        <ul>
            <?php foreach ($sets_possedes as $set): ?>
                <li><a href="detail_set.php?id=<?= $set['id'] ?>"><?= htmlspecialchars($set['nom']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun set possédé.</p>
    <?php endif; ?>

    <h2>Sets en wishlist</h2>
    <?php if (count($sets_wishlist) > 0): ?>
        <ul>
            <?php foreach ($sets_wishlist as $set): ?>
                <li><a href="detail_set.php?id=<?= $set['id'] ?>"><?= htmlspecialchars($set['nom']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun set dans la wishlist.</p>
    <?php endif; ?>

    <h2>Commentaires</h2>
    <p>Nombre de commentaires : <?= $nb_commentaires ?></p>
    <?php if (count($sets_commentes) > 0): ?>
        <ul>
            <?php foreach ($sets_commentes as $set): ?>
                <li><a href="detail_set.php?id=<?= $set['id'] ?>"><?= htmlspecialchars($set['nom']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun commentaire publié.</p>
    <?php endif; ?>

</body>
</html>
