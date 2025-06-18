<?php
session_start();
include './includes/config.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: authentification.php");
    exit;
}

$id = $_SESSION['id_utilisateur'];
$message = '';
$message_type = '';

// Récupérer les données actuelles de l'utilisateur pour pré-remplir le formulaire
$stmt = $db->prepare("SELECT * FROM SAE203_user WHERE id = :id");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Si l'utilisateur n'existe pas (problème de session potentiellement)
    header("Location: deconnexion.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ----- MODIFICATION INFOS -----
    if (isset($_POST['modifier_infos'])) {
        $new_username = trim($_POST['username']);
        $new_email = trim($_POST['email']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (!password_verify($current_password, $user['password'])) {
            $message = "Mot de passe actuel incorrect.";
            $message_type = 'error';
        } else {
            if (!empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    $message = "Les nouveaux mots de passe ne correspondent pas.";
                    $message_type = 'error';
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE SAE203_user SET username = :username, email = :email, password = :password WHERE id = :id");
                    $stmt->execute([
                        ':username' => $new_username,
                        ':email' => $new_email,
                        ':password' => $hashed_password,
                        ':id' => $id
                    ]);
                    $_SESSION['username'] = $new_username;
                    $message = "Informations mises à jour avec succès.";
                    $message_type = 'success';

                    // Mettre à jour l'objet $user pour refléter les changements dans le formulaire
                    $user['username'] = $new_username;
                    $user['email'] = $new_email;
                    $user['password'] = $hashed_password;
                }
            } else {
                $stmt = $db->prepare("UPDATE SAE203_user SET username = :username, email = :email WHERE id = :id");
                $stmt->execute([
                    ':username' => $new_username,
                    ':email' => $new_email,
                    ':id' => $id
                ]);
                $_SESSION['username'] = $new_username;
                $message = "Informations mises à jour avec succès.";
                $message_type = 'success';

                $user['username'] = $new_username;
                $user['email'] = $new_email;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mon compte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        form {
            max-width: 450px;
        }
        label {
            display: block;
            margin-top: 15px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 6px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .delete-button {
            background-color: #ff4d4d;
            color: white;
            padding: 10px;
            border: none;
            margin-top: 30px;
            cursor: pointer;
            border-radius: 5px;
        }
        .delete-button:hover {
            background-color: #e60000;
        }
        button[type="submit"] {
            margin-top: 20px;
            padding: 10px;
        }
        hr {
            margin-top: 40px;
        }
    </style>
</head>
<body>

    <h1>Modifier mes informations</h1>

    <?php if ($message): ?>
        <div class="message <?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="modifier_infos" value="1">
        
        <label for="username">Nom d'utilisateur</label>
        <input type="text" id="username" name="username" required value="<?= htmlspecialchars($user['username']) ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">

        <label for="current_password">Mot de passe actuel</label>
        <input type="password" id="current_password" name="current_password" required>

        <label for="new_password">Nouveau mot de passe (laisser vide si inchangé)</label>
        <input type="password" id="new_password" name="new_password">

        <label for="confirm_password">Confirmer le nouveau mot de passe</label>
        <input type="password" id="confirm_password" name="confirm_password">

        <button type="submit">Enregistrer les modifications</button>
    </form>

    <hr>

    <h2>Supprimer mon compte</h2>
    <p>Pour supprimer votre compte, une confirmation vous sera envoyée par e-mail.</p>
    <form method="post" action="demande_suppression.php" onsubmit="return confirm('Êtes-vous sûr de vouloir demander la suppression de votre compte ? Vous recevrez un e-mail de confirmation.');">
        <button type="submit" class="delete-button">Demander la suppression</button>
    </form>

</body>
</html>
