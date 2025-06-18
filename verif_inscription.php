<?php
    $host = 'localhost'; // Adresse du serveur MySQL
    $dbname = 'nicolas.rapuzzi'; // Nom de la base de donn ées
    $username = 'nicolas.rapuzzi'; // Nom d' utilisateur
    $password = 'D]6LYvSr7hT40fF['; // Mot de passe
    $db = ""; // $db est initialis é avec une valeur vide en dehors du try pour être accessible dans tout le document
    try {
    $db = new PDO (" mysql : host = $host ; dbname = $dbname ; charset = utf8 ", $username ,
    $password );
    $db -> setAttribute ( PDO :: ATTR_ERRMODE , PDO :: ERRMODE_EXCEPTION );
    } catch ( Exception $e ) {
    die ('Erreur : ' . $e -> getMessage () ) ;
    }
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
    $stmt = $db->prepare("SELECT COUNT(*) FROM utilisateurs WHERE login = :login");
    $stmt->bindParam(':login', $v_login);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "Erreur : Ce login est déjà utilisé. Veuillez en choisir un autre.";
    } else {
        // Chiffrement du mot de passe
        $hashed_password = password_hash($v_password, PASSWORD_DEFAULT);

        // Insertion dans la base de donn ées
        $requete = "INSERT INTO utilisateurs (login, email, password) VALUES (:login, :email, :password)";
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

