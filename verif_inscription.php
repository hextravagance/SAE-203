<?php
include './includes/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/includes/PHPMailer/src/Exception.php';
require __DIR__ . '/includes/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/includes/PHPMailer/src/SMTP.php';

if (
    isset($_POST['login'], $_POST['email'], $_POST['password']) &&
    !empty($_POST['login']) &&
    !empty($_POST['email']) &&
    !empty($_POST['password'])
) {

    $v_login = $_POST['login'];
    $v_email = $_POST['email'];
    $v_password = $_POST['password'];

    // Vérification de l'unicité du login
    $stmt = $db->prepare("SELECT COUNT(*) FROM SAE203_user WHERE username = :login OR email = :email");
    $stmt->bindParam(':login', $v_login);
    $stmt->bindParam(':email', $v_email);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "Erreur : Ce login ou cet email est déjà utilisé. Veuillez en choisir un autre.";
    } else {
        // Génération du token de validation
        $token = bin2hex(random_bytes(32));

        // Chiffrement du mot de passe
        $hashed_password = password_hash($v_password, PASSWORD_DEFAULT);

        // Insertion dans la base de données avec statut non validé
        $requete = "INSERT INTO SAE203_user (username, email, password, statut, token_validation) VALUES (:login, :email, :password, 'non validé', :token)";
        $stmt = $db->prepare($requete);
        $stmt->bindParam(':login', $v_login);
        $stmt->bindParam(':email', $v_email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':token', $token);

        $result = $stmt->execute();

        if ($result) {
            // Envoi de l'email de validation
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'brickotheque@gmail.com';  // Ton email
                $mail->Password = 'zowy mxlz njyt iend';    // Ton mot de passe ou App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('brickotheque@gmail.com', 'Brickothèque');
                $mail->addAddress($v_email);

                $mail->isHTML(true);
                $mail->Subject = 'Validation de votre compte Brickothèque';
                $mail->Body = "
                    <p>Bonjour,</p>
                    <p>Merci pour votre inscription. Veuillez cliquer sur le lien suivant pour valider votre compte :</p>
                    <p><a href='https://web-mmi2.iutbeziers.fr/~nicolas.rapuzzi/SAE-203/valider_compte.php?token=$token'>Valider mon compte</a></p>
                    <p>Si vous n'êtes pas à l'origine de cette inscription, veuillez ignorer cet email.</p>
                ";

                $mail->send();
                echo "Inscription réussie ! Un email de validation vous a été envoyé.";
            } catch (Exception $e) {
                echo "Inscription réussie, mais l'email n'a pas pu être envoyé. Erreur: {$mail->ErrorInfo}";
            }
        } else {
            echo "Erreur lors de l'inscription.";
        }
    }
} else {
    echo "Veuillez remplir tous les champs.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Inscription Réussie</title>
</head>
<body>
    <form action="authentification.php" method="get">
        <button type="submit">Connexion</button>
    </form>
</body>
</html>
