<?php
session_start();
include '/includes/config.php';

$erreur = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $password = $_POST['password'] ?? '';

    // Requête avec deux paramètres nommés différents
    $stmt = $db->prepare("SELECT * FROM SAE203_user WHERE email = :email OR username = :username");
    $stmt->execute([
        ':email' => $identifiant,
        ':username' => $identifiant
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $erreur = "Identifiants invalides.";
    } elseif ($user['statut'] !== 'validé') {
        $erreur = "Votre compte n'est pas encore validé. Veuillez vérifier votre email.";
    } elseif (!password_verify($password, $user['password'])) {
        $erreur = "Mot de passe incorrect.";
    } else {
        $_SESSION['id_utilisateur'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Connexion - Brickothèque</title>

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

        /* Navbar placeholder for alignment if needed */

        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 30px;
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
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"], input[type="password"] {
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

        a {
            color: #fff;
            text-decoration: underline;
        }

        p {
            margin-top: 10px;
        }

        .error-message {
            background-color: rgba(212, 237, 218, 0.75);
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 10px;
            color: #721c24;
            margin-bottom: 15px;
            backdrop-filter: blur(6px);
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
    <div class="form-wrapper glass-card animate__animated animate__fadeInLeft">
        <h1>Connexion</h1>

        <?php if ($erreur): ?>
            <div class="error-message"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="post" action="authentification.php" novalidate>
            <label for="identifiant">Email ou nom d'utilisateur :
                <input type="text" id="identifiant" name="identifiant" required />
            </label>

            <label for="password">Mot de passe :
                <input type="password" id="password" name="password" required />
            </label>

            <p><a href="mot_de_passe_oublie.php">Mot de passe oublié ?</a></p>

            <button type="submit">Se connecter</button>
        </form>

        <p>Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
