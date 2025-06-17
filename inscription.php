<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>

<h1>Créer un compte</h1>

<form method="post" action="verif_inscription.php">
    <label>Pseudo : <input type="text" name="pseudo" required></label><br>
    <label>Email : <input type="email" name="email" required></label><br>
    <label>Mot de passe : <input type="password" name="password" required></label><br>
    <label>Confirmer mot de passe : <input type="password" name="password_confirm" required></label><br>
    <button type="submit">S'inscrire</button>
</form>

</body>
</html>
