<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Récupération de l'ID du set depuis l'URL
$id_set = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Récupération des infos du set
$stmt = $db->prepare("SELECT * FROM sets WHERE id = :id");
$stmt->execute([':id' => $id_set]);
$set = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$set) {
    echo "<p>Erreur : ce set n'existe pas.</p>";
    exit;
}

// Note moyenne
$note_stmt = $db->prepare("SELECT AVG(note) AS moyenne FROM commentaires WHERE id_set = :id");
$note_stmt->execute([':id' => $id_set]);
$note = $note_stmt->fetchColumn();
$note = $note ? round($note, 2) : 'Aucune note';

// Commentaires
$commentaires_stmt = $db->prepare("SELECT u.username, c.commentaire, c.note
                                    FROM commentaires c 
                                    JOIN utilisateurs u ON c.id_utilisateur = u.id 
                                    WHERE c.id_set = :id
                                    ORDER BY c.id DESC");
$commentaires_stmt->execute([':id' => $id_set]);
$commentaires = $commentaires_stmt->fetchAll(PDO::FETCH_ASSOC);

// Utilisateurs possédant ce set
$possesseurs_stmt = $db->prepare("SELECT u.username
                                   FROM possession p 
                                   JOIN utilisateurs u ON p.id_utilisateur = u.id 
                                   WHERE p.id_set = :id");
$possesseurs_stmt->execute([':id' => $id_set]);
$possesseurs = $possesseurs_stmt->fetchAll(PDO::FETCH_COLUMN);

// Vérifie si l'utilisateur est connecté
$utilisateur_connecte = isset($_SESSION['id_utilisateur']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail du set</title>
</head>
<body>

    <h1><?= htmlspecialchars($set['nom']) ?></h1>
    <img src="<?= htmlspecialchars($set['image']) ?>" alt="Image du set" width="300">
    <p><strong>Numéro :</strong> <?= htmlspecialchars($set['numero']) ?></p>
    <p><strong>Nombre de pièces :</strong> <?= htmlspecialchars($set['pieces']) ?></p>
    <p><strong>Date de sortie :</strong> <?= htmlspecialchars($set['date_sortie']) ?></p>

    <h2>Note moyenne : <?= $note ?>/5</h2>

    <?php if ($utilisateur_connecte): ?>
        <form action="ajouter_possession.php" method="post">
            <input type="hidden" name="id_set" value="<?= $id_set ?>">
            <button type="submit">Je possède ce set</button>
        </form>

        <form action="ajouter_wishlist.php" method="post">
            <input type="hidden" name="id_set" value="<?= $id_set ?>">
            <button type="submit">Ajouter à la wishlist</button>
        </form>
    <?php endif; ?>

    <h2>Commentaires</h2>
    <?php if (count($commentaires) > 0): ?>
        <ul>
            <?php foreach ($commentaires as $com): ?>
                <li>
                    <strong><?= htmlspecialchars($com['username']) ?></strong> (<?= $com['note'] ?>/5) :
                    <?= htmlspecialchars($com['commentaire']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun commentaire pour ce set.</p>
    <?php endif; ?>

    <h2>Utilisateurs possédant ce set</h2>
    <?php if (count($possesseurs) > 0): ?>
        <ul>
            <?php foreach ($possesseurs as $user): ?>
                <li><?= htmlspecialchars($user) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun utilisateur ne possède encore ce set.</p>
    <?php endif; ?>

</body>
</html>
