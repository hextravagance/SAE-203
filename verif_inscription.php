<?php
session_start();
require_once 'includes/config.php';

if (!empty($_POST['username']) && !empty($_POST['email']) && !empty($_POST['password'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Vérifier si le nom d'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM SAE203_user WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->fetchColumn() > 0) {
        echo "Ce nom d'utilisateur est déjà pris.";
        exit;
    }

    // Insérer l'utilisateur
    $stmt = $pdo->prepare("INSERT INTO SAE203_user (username, email, password) VALUES (:username, :email, :password)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $passwordHash);
    $stmt->execute();

    echo "Inscription réussie. <a href='connexion.php'>Connectez-vous ici</a>";
} else {
    echo "Tous les champs sont obligatoires.";
}
?>
