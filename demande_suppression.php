<?php
session_start();
require_once './includes/config.php';

// Inclure PHPMailer (ajuste le chemin si besoin)
require_once './includes/PHPMailer/src/PHPMailer.php';
require_once './includes/PHPMailer/src/SMTP.php';
require_once './includes/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['id_utilisateur'], $_SESSION['username'])) {
    header("Location: authentification.php");
    exit;
}

$id = $_SESSION['id_utilisateur'];

$stmt = $db->prepare("SELECT * FROM SAE203_user WHERE id = :id");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit;
}

// Générer un token unique
$token = bin2hex(random_bytes(32));

// Stocker le token dans la base (ajoute une colonne suppression_token VARCHAR(64) dans ta table si besoin)
$stmt = $db->prepare("UPDATE SAE203_user SET suppression_token = :token WHERE id = :id");
$stmt->execute([':token' => $token, ':id' => $id]);

// Préparer et envoyer le mail
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'brickotheque@gmail.com'; // ton mail
    $mail->Password = 'zowy mxlz njyt iend';   // ton mdp (attention sécurité)
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('brickotheque@gmail.com', 'Brickothèque');
    $mail->addAddress($user['email'], $user['username']);
    $mail->isHTML(true);
    $mail->Subject = 'Confirmation de suppression de compte';
    $mail->Body = "Bonjour {$user['username']},<br><br>
        Veuillez cliquer sur le lien suivant pour confirmer la suppression de votre compte :<br>
        <a href='https://web-mmi2.iutbeziers.fr/~nicolas.rapuzzi/SAE-203/confirmer_suppression.php?token=$token'>
        Confirmer la suppression</a><br><br>
        Si vous n’avez pas demandé la suppression, ignorez cet e-mail.";

    $mail->send();
    echo "Un e-mail de confirmation a été envoyé à votre adresse.";
} catch (Exception $e) {
    echo "Erreur lors de l'envoi du mail : " . $mail->ErrorInfo;
}
