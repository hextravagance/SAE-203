<?php
// Connexion à la base de données (à adapter selon votre config)
$pdo = new PDO('mysql:host=localhost;dbname=lego;charset=utf8', 'root', '');

// Pagination
$sets_par_page = 50;
$page_courante = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page_courante - 1) * $sets_par_page;

// Filtrage
$filtre_theme = isset($_GET['theme']) ? $_GET['theme'] : null;

// Tri
$tri_valide = ['nom', 'numero', 'date_sortie'];
$tri = in_array($_GET['tri'] ?? '', $tri_valide) ? $_GET['tri'] : 'nom';

// Récupération des thèmes (pour le menu déroulant)
$themes_stmt = $pdo->query("SELECT DISTINCT theme FROM sets ORDER BY theme ASC");
$themes = $themes_stmt->fetchAll(PDO::FETCH_COLUMN);

// Requête SQL avec filtres
$sql = "SELECT * FROM sets";
$params = [];

if ($filtre_theme) {
    $sql .= " WHERE theme = :theme";
    $params[':theme'] = $filtre_theme;
}

$sql .= " ORDER BY $tri LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $sets_par_page, PDO::PARAM_INT);
$stmt->execute();
$sets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération du nombre total de sets (pour la pagination)
$count_sql = "SELECT COUNT(*) FROM sets" . ($filtre_theme ? " WHERE theme = :theme" : "");
$count_stmt = $pdo->prepare($count_sql);
if ($filtre_theme) {
    $count_stmt->bindValue(':theme', $filtre_theme);
}
$count_stmt->execute();
$total_sets = $count_stmt->fetchColumn();
$total_pages = ceil($total_sets / $sets_par_page);
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
