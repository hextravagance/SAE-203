<?php session_start(); ?>
<?php require_once './includes/config.php'; ?>
<?php require_once './includes/header.php'; ?>

<main class="container">
    <div class="auth-container">
        <h1>Connexion</h1>

        <?php if (isset($_SESSION['error_message_connexion'])): /* Hypothetical error message for this page */ ?>
            <div class="message error"><?= htmlspecialchars($_SESSION['error_message_connexion']) ?></div>
            <?php unset($_SESSION['error_message_connexion']); ?>
        <?php endif; ?>

        <form method="POST" action="authentification.php">
            <div class="form-group">
                <label for="login">Email ou nom d'utilisateur :</label>
                <input type="text" id="login" name="identifiant" required> <!-- Changed name to identifiant to match authentification.php -->
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>
        <div class="auth-links">
            <p><a href="authentification.php">Plus d'options (mot de passe oublié, inscription)</a></p>
        </div>
    </div>
</main>

<?php require_once './includes/footer.php'; ?>