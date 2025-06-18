<?php
session_start();
require './includes/config.php';

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
            // Pas d’erreur spécifique pour ne pas révéler si l’email existe
            $message = "Un email de réinitialisation a été envoyé si l'adresse existe.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
</head>
<body>
    <h1>Mot de passe oublié</h1>
    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="email">Entrez votre adresse email :</label><br>
        <input type="email" id="email" name="email" required><br>
        <button type="submit">Envoyer le lien de réinitialisation</button>
    </form>

    <p><a href="authentification.php">Retour à la connexion</a></p>
</body>
</html>
