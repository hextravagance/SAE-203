<?php
session_start();
require_once __DIR__ . '/includes/config.php';

$token = $_GET['token'] ?? '';
$message = '';

if ($token) {
    $stmt = $db->prepare("SELECT * FROM SAE203_user WHERE suppression_token = :token");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Supprimer l'utilisateur
        $stmt = $db->prepare("DELETE FROM SAE203_user WHERE id = :id");
        $stmt->execute([':id' => $user['id']]);
        $message = "Votre compte a bien été supprimé.";
    } else {
        $message = "Lien invalide ou compte déjà supprimé.";
    }
} else {
    $message = "Aucun token fourni.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Suppression de compte - Brickothèque</title>

    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        body {
            background: url('./image/lego-4924237_1920.jpg') no-repeat center center fixed;
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
            text-align: center;
            color: white;
        }

        a.back-link {
            color: #fff;
            text-decoration: underline;
            display: inline-block;
            margin-top: 20px;
            font-weight: bold;
        }

        a.back-link:hover {
            color: #dc3545;
        }

        h1 {
            margin-bottom: 20px;
        }

        p.message {
            font-weight: 600;
            color: lightgreen;
            margin-bottom: 20px;
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
        <h1>Suppression de compte</h1>

        <p class="message"><?= htmlspecialchars($message) ?></p>

        <a href="index.php" class="back-link">← Retour à l'accueil</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
