<?php
session_start();
include './includes/config.php'; // Assure-toi que ce fichier définit bien $db (objet PDO)

try {
    $stmt_sets = $db->query("SELECT COUNT(*) FROM lego_db");
    $nombre_sets = $stmt_sets->fetchColumn();

    $stmt_users = $db->query("SELECT COUNT(*) FROM SAE203_user");
    $nombre_utilisateurs = $stmt_users->fetchColumn();
} catch (PDOException $e) {
    $nombre_sets = 'données manquantes';
    $nombre_utilisateurs = 'données manquantes';
    error_log("Erreur BDD dans index.php : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Gestionnaire de sets LEGO</title>
</head>
<body>

    <h1>Bienvenue sur le gestionnaire de sets LEGO</h1>

    <nav>
        <ul>
            <li><a href="./sets/sets.php">Voir tous les sets</a></li>
            <li>
                <?php
                    if (isset($_SESSION['username'])) {
                        echo "<h2>Bienvenue " . htmlspecialchars($_SESSION['username']) . "</h2>";
                        echo "<p>Tu es connecté</p>";
                        echo "<a href='deconnexion.php'>Déconnexion</a>";
                    } else {
                        echo "<h2>Bienvenue</h2>";
                        echo "<p>Tu n'es pas connecté</p>";
                        echo "<a href='authentification.php'>Connexion</a><br>";
                        echo "<a href='inscription.php'>S'inscrire</a>";
                    }
                ?>
            </li>
        </ul>
    </nav>

    <section>
        <h2>Statistiques générales</h2>
        <p>Nombre de sets : <?= $nombre_sets ?></p>
        <p>Nombre d'utilisateurs : <?= $nombre_utilisateurs ?></p>
    </section>

</body>
</html>
