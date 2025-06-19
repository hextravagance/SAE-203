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
    SELECT l.id_set_number AS id, l.set_name AS nom
    FROM SAE203_owned o
    JOIN lego_db l ON o.id_set_number = l.id_set_number
    WHERE o.id_user = :id
");
$sets_possedes_stmt->execute([':id' => $id_utilisateur]);
$sets_possedes = $sets_possedes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Sets en wishlist
$sets_wishlist_stmt = $db->prepare("
    SELECT l.id_set_number AS id, l.set_name AS nom
    FROM SAE203_wishlisted w
    JOIN lego_db l ON w.id_set_number = l.id_set_number
    WHERE w.id_user = :id
");
$sets_wishlist_stmt->execute([':id' => $id_utilisateur]);
$sets_wishlist = $sets_wishlist_stmt->fetchAll(PDO::FETCH_ASSOC);

// Commentaires (avec note, texte, date, image)
$commentaires_stmt = $db->prepare("
    SELECT l.id_set_number AS id, l.set_name AS nom, l.image_url, r.rating, r.comment, r.created_at
    FROM SAE203_reviews r
    JOIN lego_db l ON r.set_id = l.id_set_number
    WHERE r.user_id = :id
    ORDER BY r.created_at DESC
");
$commentaires_stmt->execute([':id' => $id_utilisateur]);
$commentaires = $commentaires_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Profil utilisateur - <?= htmlspecialchars($utilisateur['username']) ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        body {
            background: url('./image/lego-2539844.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            padding-top: 70px;
            color: #fff;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 20px;
        }
        a {
            color: #ffc107;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .commentaire {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
        }
        .commentaire img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-right: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            background: #fff;
        }
        .commentaire-content {
            flex: 1;
        }
        hr {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.2);
            margin: 1.5rem 0;
        }
        h1, h2 {
            color: #ffc107;
        }
    </style>
</head>
<body>

<div class="container">

    <h1 class="mb-4 animate__animated animate__fadeInDown">Profil de <?= htmlspecialchars($utilisateur['username']) ?></h1>

    <div class="row">

        <div class="col-md-6 mb-4">
            <div class="glass-card animate__animated animate__fadeInLeft">
                <h2>Sets possédés</h2>
                <?php if (count($sets_possedes) > 0): ?>
                    <ul class="list-unstyled">
                        <?php foreach ($sets_possedes as $set): ?>
                            <li><a href="./sets/detail_set.php?id=<?= $set['id'] ?>"><?= htmlspecialchars($set['nom']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucun set possédé.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="glass-card animate__animated animate__fadeInRight">
                <h2>Sets en wishlist</h2>
                <?php if (count($sets_wishlist) > 0): ?>
                    <ul class="list-unstyled">
                        <?php foreach ($sets_wishlist as $set): ?>
                            <li><a href="./sets/detail_set.php?id=<?= $set['id'] ?>"><?= htmlspecialchars($set['nom']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucun set dans la wishlist.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div class="glass-card animate__animated animate__fadeInUp">
        <h2>Commentaires</h2>
        <?php if (count($commentaires) > 0): ?>
            <ul class="list-unstyled p-0 m-0">
                <?php foreach ($commentaires as $com): ?>
                    <li class="commentaire">
                        <img src="<?= htmlspecialchars($com['image_url']) ?>" alt="Image du set <?= htmlspecialchars($com['nom']) ?>" />
                        <div class="commentaire-content">
                            <strong><a href="./sets/detail_set.php?id=<?= $com['id'] ?>"><?= htmlspecialchars($com['nom']) ?></a></strong><br>
                            Note : <?= (int)$com['rating'] ?>/5<br>
                            <p><?= nl2br(htmlspecialchars($com['comment'])) ?></p>
                            <em>Posté le <?= htmlspecialchars($com['created_at']) ?></em>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucun commentaire publié.</p>
        <?php endif; ?>
    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
