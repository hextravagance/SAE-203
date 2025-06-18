<?php

include '../includes/config.php';


$sql = "SELECT * FROM lego_db LIMIT 50";

$stmt = $db->query($sql);

$sets = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des sets LEGO</title>
</head>
<body>

    <h1>Liste des sets LEGO</h1>

    <form method="get" action="sets.php">
        <label for="theme">Filtrer par thème :</label>
        <select name="theme" id="theme">
            <option value="">-- Tous les thèmes --</option>
            <?php foreach ($themes as $theme): ?>
                <option value="<?= htmlspecialchars($theme) ?>" <?= $filtre_theme === $theme ? 'selected' : '' ?>>
                    <?= htmlspecialchars($theme) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="tri">Trier par :</label>
        <select name="tri" id="tri">
            <option value="nom" <?= $tri === 'nom' ? 'selected' : '' ?>>Nom</option>
            <option value="numero" <?= $tri === 'numero' ? 'selected' : '' ?>>Numéro</option>
            <option value="date_sortie" <?= $tri === 'date_sortie' ? 'selected' : '' ?>>Date de sortie</option>
        </select>

        <button type="submit">Appliquer</button>
    </form>

    <ul>
        <?php foreach ($sets as $set): ?>
            <li>
                <img src="<?= htmlspecialchars($set['image']) ?>" alt="Image du set" width="100">
                <strong><?= htmlspecialchars($set['nom']) ?></strong> (n°<?= htmlspecialchars($set['numero']) ?>)
                <a href="set_detail.php?id=<?= $set['id'] ?>">Voir détails</a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div>
        <?php if ($page_courante > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page_courante - 1])) ?>">Page précédente</a>
        <?php endif; ?>

        <span>Page <?= $page_courante ?> / <?= $total_pages ?></span>

        <?php if ($page_courante < $total_pages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page_courante + 1])) ?>">Page suivante</a>
        <?php endif; ?>
    </div>

</body>
</html>
