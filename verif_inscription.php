<?php
$pdo = new PDO('mysql:host=localhost;dbname=lego;charset=utf8', 'root', '');

$pseudo = trim($_POST['pseudo'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// Validation de base
$erreurs = [];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = "Email invalide.";
}

if ($password !== $password_confirm) {
    $erreurs[] = "Les mots de passe ne correspondent pas.";
}

if (strlen($password) < 8) {
    $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères.";
}

// Vérifie que l'email n'est pas déjà utilisé
$check_stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email");
$check_stmt->execute([':email' => $email]);
if ($check_stmt->fetch()) {
    $erreurs[] = "Cet email est déjà utilisé.";
}

if ($erreurs) {
    foreach ($erreurs as $err) {
        echo "<p>$err</p>";
    }
    echo '<a href="inscription.php">Retour</a>';
    exit;
}

// Hash du mot de passe
$hash = password_hash($password, PASSWORD_DEFAULT);

// Génération d'un token
$token = bin2hex(random_bytes(16));

// Insertion dans la base
$insert = $pdo->prepare("
    INSERT INTO utilisateurs (username, email, mot_de_passe, statut, token_validation, date_inscription)
    VALUES (:username, :email, :mot_de_passe, 'non_validé', :token, NOW())
");
$insert->execute([
    ':username' => $pseudo,
    ':email' => $email,
    ':mot_de_passe' => $hash,
    ':token' => $token
]);

// Envoi de l'email (exemple simplifié)
$validation_link = "http://localhost/valider_compte.php?token=" . urlencode($token);
$message = "Bonjour $pseudo,\nCliquez sur ce lien pour valider votre compte : $validation_link";

mail($email, "Validation de votre compte LEGO", $message);

header('Location: authentification.php?msg=inscription_reussie');
exit;
