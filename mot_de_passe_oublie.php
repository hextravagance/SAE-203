<?php
session_start();
include './includes/config.php';

// Inclure PHPMailer (ajuste chemin si besoin)
require_once './includes/PHPMailer/src/PHPMailer.php';
require_once './includes/PHPMailer/src/SMTP.php';
require_once './includes/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Adresse email invalide.";
    } else {
        // Vérifier que l’email existe
        $stmt = $db->prepare("SELECT * FROM SAE203_user WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $stmt = $db->prepare("UPDATE SAE203_user SET reset_token = :token, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = :id");
            $stmt->execute([':token' => $token, ':id' => $user['id']]);

            // Envoi mail
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'brickotheque@gmail.com';
                $mail->Password = 'zowy mxlz njyt iend';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('brickotheque@gmail.com', 'Brickothèque');
                $mail->addAddress($email, $user['username']);
                $mail->isHTML(true);
                $mail->Subject = 'Réinitialisation de votre mot de passe';
                $mail->Body = "Bonjour {$user['username']},<br><br>
                    Cliquez sur ce lien pour réinitialiser votre mot de passe :<br>
                    <a href='https://web-mmi2.iutbeziers.fr/~nicolas.rapuzzi/SAE-203/reinitialiser_mdp.php?token=$token'>Réinitialiser mon mot de passe</a><br><br>
                    Ce lien est valable 1 heure.<br><br>
                    Si vous n’avez pas demandé cette réinitialisation, ignorez cet email.";

                $mail->send();
                $message = "Un email de réinitialisation a été envoyé si l'adresse existe.";
            } catch (Exception $e) {
                $message = "Erreur lors de l'envoi de l'email: " . $mail->ErrorInfo;
            }
        } else {
            $message = "Un email de réinitialisation a été envoyé si l'adresse existe.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Mot de passe oublié - Brickothèque</title>

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

        input[type="email"] {
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
        <h1>Mot de passe oublié</h1>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post" novalidate>
            <label for="email">Entrez votre adresse email :</label>
            <input type="email" id="email" name="email" required />
            <button type="submit">Envoyer le lien de réinitialisation</button>
        </form>

        <a href="authentification.php">← Retour à la connexion</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
