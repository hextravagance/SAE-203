<?php
include './includes/config.php';

if (
    isset($_POST['login'], $_POST['email'], $_POST['password'], $_POST['confirm_password']) &&
    !empty($_POST['login']) &&
    !empty($_POST['email']) &&
    !empty($_POST['password']) &&
    !empty($_POST['confirm_password'])
) {
    $v_login = trim($_POST['login']);
    $v_email = trim($_POST['email']);
    $v_password = $_POST['password'];
    $v_confirm_password = $_POST['confirm_password'];

    // Vérification : mots de passe identiques
    if ($v_password !== $v_confirm_password) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérification de l'unicité du login
        $stmt = $db->prepare("SELECT COUNT(*) FROM SAE203_user WHERE username = :login");
        $stmt->bindParam(':login', $v_login);
        $stmt->execute();
        $login_count = $stmt->fetchColumn();

        // Vérification de l'unicité de l'email
        $stmt = $db->prepare("SELECT COUNT(*) FROM SAE203_user WHERE email = :email");
        $stmt->bindParam(':email', $v_email);
        $stmt->execute();
        $email_count = $stmt->fetchColumn();

        if ($login_count > 0) {
            $erreur = "Erreur : Ce login est déjà utilisé. Veuillez en choisir un autre.";
        } elseif ($email_count > 0) {
            $erreur = "Erreur : Cet email est déjà utilisé. Veuillez en utiliser un autre.";
        } else {
            // Hachage du mot de passe
            $hashed_password = password_hash($v_password, PASSWORD_DEFAULT);

            // Insertion dans la base de données
            $requete = "INSERT INTO SAE203_user (username, email, password) VALUES (:login, :email, :password)";
            $stmt = $db->prepare($requete);
            $stmt->bindParam(':login', $v_login);
            $stmt->bindParam(':email', $v_email);
            $stmt->bindParam(':password', $hashed_password);

            $result = $stmt->execute();

            if ($result) {
                $success = true;
            } else {
                $erreur = "Erreur lors de l'inscription.";
            }
        }
    }
} else {
    $erreur = "Veuillez remplir tous les champs.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>
    <?php if (!empty($erreur)): ?>
        <p style="color:red"><?= htmlspecialchars($erreur) ?></p>
        <a href="inscription.php">Revenir au formulaire</a>
    <?php elseif (!empty($success)): ?>
        <p style="color:green">Inscription réussie !</p>
        <form action="authentification.php" method="get">
            <button type="submit">Connexion</button>
        </form>
    <?php endif; ?>
</body>
</html>
