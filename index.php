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
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- For responsiveness -->
</head>
<body>
<?php include './includes/header.php'; ?>

<section class="hero-section">
    <div class="container"> <!-- Inner container for content width -->
        <h1>Bienvenue sur Brickothèque</h1>
        <p class="hero-subtitle">Votre encyclopédie et collection personnelle de sets LEGO.</p>
        <a href="./sets/sets.php" class="button-primary">Découvrir les Sets</a>
    </div>
</section>

<main class="container home-main-content">
    <?php if (isset($_GET['message']) && $_GET['message'] === 'suppression'): ?>
        <div class="message success">Votre compte a bien été supprimé.</div>
    <?php endif; ?>
    <?php
        // Display messages from other potential session flash messages
        if (isset($_SESSION['message']) && $_SESSION['message']) {
            echo '<div class="message ' . htmlspecialchars($_SESSION['message_type']) . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
            $_SESSION['message'] = '';
            $_SESSION['message_type'] = '';
        }
    ?>

    <section class="home-navigation">
        <h2>Accès Rapide</h2>
        <ul>
            <li><a href="./sets/sets.php">Voir tous les sets</a></li>
            <?php if ($is_connected): ?>
                <li><a href="./sets/wishlist.php">Ma Wishlist</a></li>
                <li><a href="./sets/owned.php">Mes Sets Possédés</a></li>
                <li><a href="modifier_compte.php">Mon Compte</a></li>
            <?php else: ?>
                <li><a href="authentification.php">Se Connecter</a></li>
                <li><a href="inscription.php">S'inscrire</a></li>
            <?php endif; ?>
        </ul>
    </section>

    <section class="stats-section">
        <h2>Notre Communauté en Chiffres</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-number"><?= $nombre_sets ?></span>
                <span class="stat-label">Sets Référencés</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= $nombre_utilisateurs ?></span>
                <span class="stat-label">Utilisateurs Inscrits</span>
            </div>
            <!-- Add more stats here if available -->
        </div>
    </section>

</main>
<?php include './includes/footer.php'; ?>
</body>
</html>
