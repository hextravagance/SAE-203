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

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- Meta Responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        /* Background GIF */
        body {
            background: url('./image/lego-2539844.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            padding-top: 70px;
        }

        .navbar-brand span {
            font-weight: bold;
            color: #dc3545;
        }

        /* Glassmorphism style */
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .alert {
            backdrop-filter: blur(6px);
            background-color: rgba(212, 237, 218, 0.75);
            border: 1px solid #c3e6cb;
        }

        .nav-link, .card-title, .card-text, .list-group-item a {
            color: #fff;
        }

        .list-group-item {
            background: transparent;
            border: none;
        }

        .list-group-item a {
            text-decoration: none;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-50 fixed-top shadow-sm glass-card">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            Brickothèque<?= $username ? " - Bonjour <span>$username</span>" : "" ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <?php if ($is_connected): ?>
                    <li class="nav-item"><a class="nav-link" href="modifier_compte.php">Compte</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="deconnexion.php">Déconnexion</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="authentification.php">Se connecter</a></li>
                    <li class="nav-item"><a class="nav-link" href="inscription.php">S'inscrire</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Message de succès -->
<div class="container mt-4">
    <?php if (isset($_GET['message']) && $_GET['message'] === 'suppression'): ?>
        <div class="alert alert-success animate__animated animate__fadeInDown" role="alert">
            Votre compte a bien été supprimé.
        </div>
    <?php endif; ?>
</div>

<!-- Contenu principal -->
<main class="container mt-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card glass-card animate__animated animate__fadeInLeft">
                <div class="card-body">
                    <h5 class="card-title">Navigation</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="./sets/sets.php">Voir tous les sets</a></li>
                        <?php if ($is_connected): ?>
                            <li class="list-group-item"><a href="./sets/wishlist.php">Ma Wishlist</a></li>
                            <li class="list-group-item"><a href="./sets/owned.php">Mes Sets Possédés</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card glass-card shadow animate__animated animate__fadeInRight">
                <div class="card-body">
                    <h2 class="card-title">Statistiques générales</h2>
                    <p class="card-text">📦 Nombre de sets : <strong><?= $nombre_sets ?></strong></p>
                    <p class="card-text">👤 Nombre d’utilisateurs : <strong><?= $nombre_utilisateurs ?></strong></p>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Nouveau bloc description -->
<div class="container" style="margin-top: 20%;">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card glass-card shadow animate__animated animate__fadeInUp" style="padding: 20px;">
                <p class="mb-0" style="color: white; font-size: 1.1rem; text-align: center;">
                    Avec Brickothèque vous pouvez trouver la liste complète de tout les sets LEGO, les enregistrer ainsi que de pouvoir crée une wishlist afin de la partager avec tout vos amis !
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
