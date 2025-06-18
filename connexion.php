<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Connexion</title>
</head>
    <body>
        <h2>Formulaire de connexion</h2>
        <form method="POST" action="verif_connexion.php">
            <label for="login">Login :</label><br>
            <input type="text" id="login" name="login" required><br><br>
            <label for="password">Mot de passe :</label><br>
            <input type="password" id="password" name="password" required><br><br>
            <button type="submit">Se connecter</button>
        </form>
    </body>
</html>