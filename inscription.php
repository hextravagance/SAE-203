<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Inscription</title>
</head>
<body>
    <h2>Formulaire d'inscription</h2>
    <form method="POST" action="verif_inscription.php">
        <label for="login">Login :</label><br>
        <input type="text" id="login" name="login" required><br>

        <label for="password">Mot de passe :</label><br>
        <input type="password" id="password" name="password" required><br>

        <label for="confirm_password">Confirmer le mot de passe :</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required><br>

        <label for="email">Email :</label><br>
        <input type="email" id="email" name="email" required><br>

        <button type="submit">S'inscrire</button>
    </form>
</body>
</html>
