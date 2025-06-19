<?php
session_start();
require './includes/config.php';

$message = '';
$show_form = true;

if (!isset($_GET['token'])) {
    $message = "Token manquant.";
    $show_form = false;
} else {
    $token = $_GET['token'];

    // Vérifier token valide et non expiré
    $stmt = $db->prepare("SELECT * FROM SAE203_user WHERE reset_token = :token AND reset_token_expiry > NOW()");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = "Lien invalide ou expiré.";
        $show_form = false;
    }
}

if ($show_form && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $message = "Veuillez remplir tous les champs.";
    } elseif ($new_password !== $confirm_password) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {
        // Mettre à jour mot de passe et supprimer token
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE SAE203_user SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id");
        $stmt->execute([':password' => $hashed, ':id' => $user['id']]);

        $message = "Mot de passe modifié avec succès. Vous pouvez maintenant vous connecter.";
        $show_form = false;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Réinitialiser le mot de passe - Brickothèque</title>

    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        body {
            background: url('./image/car-7947765.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            padding-top: 70px;
            font-family: Arial, sans-serif;
            color: #fff;
        }

        .container-form {
            padding-top: 40px;
            display: flex;
            justify-content: flex-start;
            padding-left: 5vw;
            box-sizing: border-box;
            min-height: 80vh;
        }

        .form-wrapper {
            max-width: 400px;
            width: 100%;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 30px;
            color: white;
        }

        h1 {
            margin-bottom: 20px;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="password"] {
            width: 100%;
            padding: 8px 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: none;
            outline: none;
            font-size: 1em;
        }

        button {
            background-color: #dc3545;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1em;
            margin-top: 15px;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #b52a39;
        }

        p.message {
            background-color: rgba(212, 237, 218, 0.75);
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 10px;
            color: #721c24;
            margin-bottom: 15px;
            backdrop-filter: blur(6px);
            font-weight: 600;
        }

        a {
            color: #fff;
            text-decoration: underline;
            display: block;
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
        }

        a:hover {
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .container-form {
                justify-content: center;
                padding-left: 0;
            }
            .form-wrapper {
                max-width: 90vw;
            }
        }
    </style>
</head>
<body>

<div class="container-form">
    <div class="form-wrapper animate__animated animate__fadeInLeft">
        <h1>Réinitialiser le mot de passe</h1>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($show_form): ?>
            <form method="post" novalidate>
                <label for="new_password">Nouveau mot de passe :</label>
                <input type="password" id="new_password" name="new_password" required />

                <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" required />

                <button type="submit">Valider</button>
            </form>
        <?php endif; ?>

        <a href="authentification.php">← Retour à la connexion</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
