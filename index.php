<?php
session_start();
include '/includes/config.php';

// Simulations de données pour l'exemple (à remplacer par des requêtes réelles plus tard)
$nombre_sets = 1200;
$nombre_utilisateurs = 350;

$utilisateur_connecte = isset($_SESSION['username']) ? $_SESSION['username'] : null;
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
                <?php if ($utilisateur_connecte): ?>
                    Bienvenue <?= htmlspecialchars($utilisateur_connecte) ?> |
                    <a href="profil.php">Voir votre profil</a>
                <?php else: ?>
                    <a href="connexion.php">S’inscrire / Se connecter</a>
                <?php endif; ?>
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
