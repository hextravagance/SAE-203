<?php
session_start();
include './includes/config.php';

try {
    $stmt_sets = $db->query("SELECT COUNT(*) FROM lego_db");
    $nombre_sets = $stmt_sets->fetchColumn();

    $stmt_users = $db->query("SELECT COUNT(*) FROM SAE203_user");
    $nombre_utilisateurs = $stmt_users->fetchColumn();
} catch (PDOException $e) {
    $nombre_sets = 'Données indisponibles';
    $nombre_utilisateurs = 'Données indisponibles';
    error_log("Erreur BDD dans index.php : " . $e->getMessage());
}

$is_connected = isset($_SESSION['username']);
$username = $is_connected ? htmlspecialchars($_SESSION['username']) : null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Brickothèque</title>
</head>
<body>
<?php include './includes/header.php'; ?>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'suppression'): ?>
        <div class="message success" style="margin-top: 80px;">Votre compte a bien été supprimé.</div>
    <?php endif; ?>

    <main style="margin-top: 80px;">
        <nav>
            <ul>
                <li><a href="./sets/sets.php">Voir tous les sets</a></li>
                <?php if ($is_connected): ?>
                    <li><a href="./sets/wishlist.php">Ma Wishlist</a></li>
                    <li><a href="./sets/owned.php">Mes Sets Possédés</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <section>
            <h2>Statistiques générales</h2>
            <p>Nombre de sets : <?= $nombre_sets ?></p>
            <p>Nombre d’utilisateurs : <?= $nombre_utilisateurs ?></p>
        </section>
        <div style="height: 2000px; margin-top: 20px; background-color: #f0f0f0; padding:10px;">Scrollable Content to Test Header</div>
    </main>
<?php include './includes/footer.php'; ?>
</body>
</html>
