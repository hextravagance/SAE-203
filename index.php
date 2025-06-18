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
    <style>
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: #f2f2f2;
            border-bottom: 1px solid #ccc;
        }
        .header-left h1 {
            margin: 0;
            font-size: 24px;
        }
        .header-left a {
            text-decoration: none;
            color: #333;
        }
        .header-right a {
            margin-left: 15px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
        nav ul {
            list-style-type: none;
            padding-left: 0;
        }
        .message.success {
            margin: 20px;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <header>
        <div class="header-left">
            <h1>
                <a href="index.php">
                    Brickothèque<?= $username ? " - Bonjour $username" : "" ?>
                </a>
            </h1>
        </div>
        <div class="header-right">
            <?php if ($is_connected): ?>
                <a href="modifier_compte.php">Compte</a>
                <a href="deconnexion.php">Déconnexion</a>
            <?php else: ?>
                <a href="authentification.php">Se connecter</a>
                <a href="inscription.php">S'inscrire</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'suppression'): ?>
        <div class="message success">Votre compte a bien été supprimé.</div>
    <?php endif; ?>

    <main>
        <nav>
            <ul>
                <li><a href="./sets/sets.php">Voir tous les sets</a></li>
            </ul>
        </nav>

        <section>
            <h2>Statistiques générales</h2>
            <p>Nombre de sets : <?= $nombre_sets ?></p>
            <p>Nombre d’utilisateurs : <?= $nombre_utilisateurs ?></p>
        </section>
    </main>

</body>
</html>
