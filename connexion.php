<?php
include 'config.php';
$success = '';
if (isset($_GET['reset']) && $_GET['reset'] == 'success') {
    $success = "Mot de passe réinitialisé avec succès !";
}

if ($_POST) {
    $stmt = $db->prepare("SELECT * FROM users WHERE pseudo = ?");
    $stmt->execute([$_POST['pseudo']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Identifiants incorrects";
    }
}