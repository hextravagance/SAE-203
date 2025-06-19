<?php
include './includes/config.php';

$token = $_GET['token'] ?? '';
$message = '';
$type_message = 'danger';

if ($token) {
    $stmt = $db->prepare("SELECT * FROM SAE203_user WHERE token_validation = :token");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user['statut'] === 'validé') {
            $message = "🔄 Ce compte est déjà validé.";
            $type_message = 'info';
        } else {
            $stmt = $db->prepare("UPDATE SAE203_user SET statut = 'validé', token_validation = NULL WHERE id = :id");
            $stmt->execute([':id' => $user['id']]);
            $message = "✅ Votre compte a bien été validé ! Vous pouvez maintenant vous connecter.";
            $type_message = 'success';
        }
    } else {
        $message = "❌ Lien de validation invalide ou expiré.";
    }
} else {
    $message = "⚠️ Aucun token fourni.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Validation du compte - Brickothèque</title>

    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />

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

        .card.glass-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 30px;
            max-width: 400px;
            width: 100%;
            color: white;
            text-align: center;
        }

        .alert-info {
            background-color: rgba(23, 162, 184, 0.75);
            color: #0c5460;
            border-color: #bee5eb;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.75);
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.75);
            color: #721c24;
            border-color: #f5c6cb;
        }

        a.btn-warning {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
            font-weight: bold;
            margin-top: 20px;
            width: 100%;
            border-radius: 8px;
            padding: 10px 0;
            display: inline-block;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        a.btn-warning:hover {
            background-color: #b52a39;
            border-color: #b52a39;
            color: white;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .container-form {
                justify-content: center;
                padding-left: 0;
            }
            .card.glass-card {
                max-width: 90vw;
            }
        }
    </style>
</head>
<body>

<div class="container-form">
    <div class="card glass-card animate__animated animate__fadeInLeft">
        <h3>Validation de votre compte</h3>
        <div class="alert alert-<?= $type_message ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <a href="authentification.php" class="btn btn-warning">Se connecter</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
