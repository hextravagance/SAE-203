<?php
require_once __DIR__ . '/includes/config.php';

// Récupération de l'ID utilisateur
$id_utilisateur = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Infos utilisateur
$stmt = $db->prepare("SELECT * FROM SAE203_user WHERE id = :id");
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
<?php require_once './includes/header.php'; ?>

<main class="container">
    <h1>Profil de <?= htmlspecialchars($utilisateur['username']) ?></h1>
    <p>Date d'inscription : <?= htmlspecialchars(date("d/m/Y", strtotime($utilisateur['date_inscription']))) ?></p>

    <section class="user-profile-section">
        <h2>Sets possédés (<?= count($sets_possedes) ?>)</h2>
        <?php if (count($sets_possedes) > 0): ?>
            <ul>
                <?php foreach ($sets_possedes as $set): ?>
                    <li><a href="sets/detail_set.php?id=<?= $set['id'] ?>"><?= htmlspecialchars($set['nom']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Cet utilisateur n'a ajouté aucun set à sa collection.</p>
        <?php endif; ?>
    </section>

    <section class="user-profile-section">
        <h2>Sets en wishlist (<?= count($sets_wishlist) ?>)</h2>
        <?php if (count($sets_wishlist) > 0): ?>
            <ul>
                <?php foreach ($sets_wishlist as $set): ?>
                    <li><a href="sets/detail_set.php?id=<?= $set['id'] ?>"><?= htmlspecialchars($set['nom']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Cet utilisateur n'a aucun set dans sa wishlist.</p>
        <?php endif; ?>
    </section>

    <section class="user-profile-section">
        <h2>Commentaires (<?= $nb_commentaires ?>)</h2>
        <p>L'utilisateur a commenté les sets suivants :</p>
        <?php if (count($sets_commentes) > 0): ?>
            <ul>
                <?php foreach ($sets_commentes as $set): ?>
                    <li><a href="sets/detail_set.php?id=<?= $set['id'] ?>"><?= htmlspecialchars($set['nom']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Cet utilisateur n'a publié aucun commentaire.</p>
        <?php endif; ?>
    </section>

</main>

<?php require_once './includes/footer.php'; ?>
