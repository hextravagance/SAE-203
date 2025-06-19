<?php
include './includes/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/includes/PHPMailer/src/Exception.php';
require __DIR__ . '/includes/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/includes/PHPMailer/src/SMTP.php';

$message = '';
$type_message = 'success';

if (
    isset($_POST['login'], $_POST['email'], $_POST['password']) &&
    !empty($_POST['login']) &&
    !empty($_POST['email']) &&
    !empty($_POST['password'])
) {
    $v_login = $_POST['login'];
    $v_email = $_POST['email'];
    $v_password = $_POST['password'];

    $stmt = $db->prepare("SELECT COUNT(*) FROM SAE203_user WHERE username = :login OR email = :email");
    $stmt->bindParam(':login', $v_login);
    $stmt->bindParam(':email', $v_email);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $message = "Erreur : Ce login ou cet email est déjà utilisé. Veuillez en choisir un autre.";
        $type_message = 'danger';
    } else {
        $token = bin2hex(random_bytes(32));
        $hashed_password = password_hash($v_password, PASSWORD_DEFAULT);

        $requete = "INSERT INTO SAE203_user (username, email, password, statut, token_validation) 
                    VALUES (:login, :email, :password, 'non validé', :token)";
        $stmt = $db->prepare($requete);
        $stmt->bindParam(':login', $v_login);
        $stmt->bindParam(':email', $v_email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':token', $token);

        $result = $stmt->execute();

        if ($result) {
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'brickotheque@gmail.com';
                $mail->Password = 'zowy mxlz njyt iend';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('brickotheque@gmail.com', 'Brickothèque');
                $mail->addAddress($v_email);

                $mail->isHTML(true);
                $mail->Subject = 'Validation de votre compte Brickothèque';
                $mail->Body = "
                    <p>Bonjour,</p>
                    <p>Merci pour votre inscription. Cliquez sur le lien suivant pour valider votre compte :</p>
                    <p><a href='https://web-mmi2.iutbeziers.fr/~nicolas.rapuzzi/SAE-203/valider_compte.php?token=$token'>Valider mon compte</a></p>
                    <p>Si vous n'êtes pas à l'origine de cette inscription, ignorez cet email.</p>
                ";

                $mail->send();
                $message = "✅ Inscription réussie ! Un email de validation vous a été envoyé.";
            } catch (Exception $e) {
                $message = "⚠️ Inscription réussie, mais l'email n'a pas pu être envoyé. Erreur: {$mail->ErrorInfo}";
                $type_message = 'warning';
            }
        } else {
            $message = "Erreur lors de l'inscription.";
            $type_message = 'danger';
        }
    }
} else {
    $message = "Veuillez remplir tous les champs.";
    $type_message = 'danger';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Inscription - Brickothèque</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />

    <style>
        body {
            background-color: #1c1c1c;
            color: white;
            padding-top: 70px;
        }

        .glass-card {
            background: rgba(25, 25, 25, 0.3);
            border-radius: 16px;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .navbar-brand span {
            font-weight: bold;
            color: #ffc107;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-50 fixed-top glass-card">
    <div class="container">
        <a class="navbar-brand" href="index.php">Brickothèque</a>
    </div>
</nav>

<!-- Contenu -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card glass-card p-4 animate__animated animate__fadeInDown">
                <div class="card-body text-center">
                    <h3 class="card-title mb-4">Résultat de l'inscription</h3>
                    <div class="alert alert-<?= $type_message ?>"><?= $message ?></div>
                    <a href="authentification.php" class="btn btn-warning mt-3">Se connecter</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
