<?php session_start(); // Required for potential messages ?>
<?php require_once './includes/config.php'; // For consistency, though not strictly used in this form ?>
<?php require_once './includes/header.php'; ?>

<main class="container">
    <div class="auth-container">
        <h1>Inscription</h1>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message error"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="message success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <form method="POST" action="verif_inscription.php">
            <div class="form-group">
                <label for="login">Nom d'utilisateur :</label>
                <input type="text" id="login" name="login" required>
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">S'inscrire</button>
        </form>
        <div class="auth-links">
            <p>Déjà un compte ? <a href="authentification.php">Se connecter</a></p>
        </div>
    </div>
</main>

<?php require_once './includes/footer.php'; ?>
