<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>

<h1>Créer un compte</h1>

<form method="POST" action="verif_inscription.php">
    <label>Nom d'utilisateur :</label><input type="text" name="username" required><br>
    <label>Email :</label><input type="email" name="email" required><br>
    <label>Mot de passe :</label><input type="password" name="password" required><br>
    <input type="submit" value="S'inscrire">
</form>
</body>
</html>

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
