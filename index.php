<?php
session_start();
include './includes/config.php'; // Corrigé le chemin s’il était erroné

// Simulations de données pour l'exemple (à remplacer par des requêtes réelles plus tard)
$nombre_sets = 1200;
$nombre_utilisateurs = 350;
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
        <p>Nombre d’utilisateurs : <?= $nombre_utilisateurs ?></p>
    </section>

</body>
</html>
