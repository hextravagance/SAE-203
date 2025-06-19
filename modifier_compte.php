<?php
session_start();
include './includes/config.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: authentification.php");
    exit;
}

$id = $_SESSION['id_utilisateur'];
$message = '';
$message_type = '';

$stmt = $db->prepare("SELECT * FROM SAE203_user WHERE id = :id");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: deconnexion.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['modifier_infos'])) {
        $new_username = trim($_POST['username']);
        $new_email = trim($_POST['email']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (!password_verify($current_password, $user['password'])) {
            $message = "Mot de passe actuel incorrect.";
            $message_type = 'danger';
        } else {
            if (!empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    $message = "Les nouveaux mots de passe ne correspondent pas.";
                    $message_type = 'danger';
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE SAE203_user SET username = :username, email = :email, password = :password WHERE id = :id");
                    $stmt->execute([
                        ':username' => $new_username,
                        ':email' => $new_email,
                        ':password' => $hashed_password,
                        ':id' => $id
                    ]);
                    $_SESSION['username'] = $new_username;
                    $message = "Informations mises à jour avec succès.";
                    $message_type = 'success';

                    $user['username'] = $new_username;
                    $user['email'] = $new_email;
                    $user['password'] = $hashed_password;
                }
            } else {
                $stmt = $db->prepare("UPDATE SAE203_user SET username = :username, email = :email WHERE id = :id");
                $stmt->execute([
                    ':username' => $new_username,
                    ':email' => $new_email,
                    ':id' => $id
                ]);
                $_SESSION['username'] = $new_username;
                $message = "Informations mises à jour avec succès.";
                $message_type = 'success';

                $user['username'] = $new_username;
                $user['email'] = $new_email;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Modifier mon compte - Brickothèque</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- Meta Responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            background: url('./image/car-7947765.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            padding-top: 70px;
            font-family: Arial, sans-serif;
        }

        .navbar-brand span {
            font-weight: bold;
            color: #dc3545;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(255, 255, 255, 0.21);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .container-main {
            max-width: 700px;
            margin: auto;
        }

        form label {
            margin-top: 15px;
            font-weight: 600;
        }

        form input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button[type="submit"] {
            margin-top: 20px;
            padding: 10px 15px;
        }

        .delete-button {
            background-color: #ff4d4d;
            color: white;
            padding: 10px 15px;
            border: none;
            margin-top: 30px;
            cursor: pointer;
            border-radius: 5px;
        }

        .delete-button:hover {
            background-color: #e60000;
        }

        hr {
            margin-top: 40px;
        }
    </style>
</head>
<body>

<!-- Navbar identique à index -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-50 fixed-top shadow-sm glass-card">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            Brickothèque<?= isset($_SESSION['username']) ? " - Bonjour <span>" . htmlspecialchars($_SESSION['username']) . "</span>" : "" ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="modifier_compte.php">Compte</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="deconnexion.php">Déconnexion</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container container-main mt-5 glass-card p-4 animate__animated animate__fadeIn">
    <h1>Modifier mes informations</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> animate__animated animate__fadeInDown" role="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <input type="hidden" name="modifier_infos" value="1">

        <label for="username">Nom d'utilisateur</label>
        <input type="text" id="username" name="username" required value="<?= htmlspecialchars($user['username']) ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">

        <label for="current_password">Mot de passe actuel</label>
        <input type="password" id="current_password" name="current_password" required>

        <label for="new_password">Nouveau mot de passe (laisser vide si inchangé)</label>
        <input type="password" id="new_password" name="new_password">

        <label for="confirm_password">Confirmer le nouveau mot de passe</label>
        <input type="password" id="confirm_password" name="confirm_password">

        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    </form>

    <hr>

    <h2>Supprimer mon compte</h2>
    <p>Pour supprimer votre compte, une confirmation vous sera envoyée par e-mail.</p>
    <form method="post" action="demande_suppression.php" onsubmit="return confirm('Êtes-vous sûr de vouloir demander la suppression de votre compte ? Vous recevrez un e-mail de confirmation.');">
        <button type="submit" class="delete-button">Demander la suppression</button>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
