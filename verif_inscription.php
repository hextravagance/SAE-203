<?php
    include './includes/config.php'; 

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
    $stmt = $db->prepare("SELECT COUNT(*) FROM SAE203_user WHERE username = :login");
    $stmt->bindParam(':login', $v_login);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "Erreur : Ce login est déjà utilisé. Veuillez en choisir un autre.";
    } else {
        // Chiffrement du mot de passe
        $hashed_password = password_hash($v_password, PASSWORD_DEFAULT);

        // Insertion dans la base de données
        $requete = "INSERT INTO SAE203_user (username, email, password) VALUES (:login, :email, :password)";
        $stmt = $db->prepare($requete);
        $stmt->bindParam(':login', $v_login);
        $stmt->bindParam(':email', $v_email);
        $stmt->bindParam(':password', $hashed_password);

        $result = $stmt->execute();

        if ($result) {
            echo "Inscription réussie !";
        } else {
            echo "Erreur lors de l'inscription.";
        }
    }
} else {
    echo "Veuillez remplir tous les champs.";
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Inscription Réussie</title>
    </head>
    <body>
        <form action="authentification.php" method="get">
        <button type="submit">Connexion</button>
        </form>
    </body>
</html>
