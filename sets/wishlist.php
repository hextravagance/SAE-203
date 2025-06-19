<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: authentification.php");
    exit;
}

$user_id = $_SESSION['id_utilisateur'];

// Suppression d’un set de la wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_set_id'])) {
        $delete_id = $_POST['delete_set_id'];
        $stmt = $db->prepare("DELETE FROM SAE203_wishlisted WHERE id_user = ? AND id_set_number = ?");
        $stmt->execute([$user_id, $delete_id]);
    }

    // Mise à jour de la quantité
    if (isset($_POST['update_set_id'], $_POST['quantity'])) {
        $update_id = $_POST['update_set_id'];
        $quantity = max(1, intval($_POST['quantity'])); // minimum 1

        $stmt = $db->prepare("UPDATE SAE203_wishlisted SET quantity = ? WHERE id_user = ? AND id_set_number = ?");
        $stmt->execute([$quantity, $user_id, $update_id]);
    }
}

// Récupération des sets
$stmt = $db->prepare("
    SELECT l.id_set_number, l.set_name, l.image_url, w.quantity
    FROM SAE203_wishlisted w
    JOIN lego_db l ON w.id_set_number = l.id_set_number
    WHERE w.id_user = ?
");
$stmt->execute([$user_id]);
$sets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ma Wishlist LEGO - Brickothèque</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- Meta Responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            background: url('<?= empty($sets) ? "../image/lego-2539844.jpg" : "../image/lego-2614046.jpg" ?>') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            padding-top: 70px;
            font-family: Arial, sans-serif;
        }

        .navbar-brand span {
            font-weight: bold;
            color: #dc3545;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(255, 255, 255, 0.21);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        h1 {
            color: white;
            margin-bottom: 20px;
        }

        a.back-link {
            color: white;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }
        a.back-link:hover {
            text-decoration: underline;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
        }

        .card {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
            padding: 15px;
            color: white;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }

        form.update-form,
        form.delete-form {
            margin-top: 10px;
        }

        input[type=number] {
            width: 60px;
            display: inline-block;
            margin-right: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            padding: 5px;
            text-align: center;
        }

        button {
            cursor: pointer;
            border-radius: 5px;
            border: none;
        }

        button.update-btn {
            background-color: #0d6efd;
            color: white;
            padding: 6px 12px;
        }
        button.update-btn:hover {
            background-color: #084cd6;
        }

        button.delete-btn {
            background-color: #dc3545;
            color: white;
            padding: 6px 12px;
            margin-top: 8px;
            width: 100%;
        }
        button.delete-btn:hover {
            background-color: #a52727;
        }
    </style>
</head>
<body>

<!-- Navbar identique à index.php -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-50 fixed-top shadow-sm glass-card">
    <div class="container">
        <a class="navbar-brand" href="../index.php">
            Brickothèque<?= isset($_SESSION['username']) ? " - Bonjour <span>" . htmlspecialchars($_SESSION['username']) . "</span>" : "" ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavWishlist">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNavWishlist">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="../index.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="../sets/sets.php">Sets</a></li>
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="wishlist.php">Ma Wishlist</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="../deconnexion.php">Déconnexion</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5 glass-card p-4 animate__animated animate__fadeIn">
    <h1>Ma Wishlist LEGO</h1>
    <a href="sets.php" class="back-link">← Retour aux sets</a>

    <?php if (empty($sets)): ?>
        <p style="color: white;">Vous n'avez aucun set dans votre wishlist.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($sets as $set): ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($set['image_url']) ?>" alt="<?= htmlspecialchars($set['set_name']) ?>">
                    <h4><?= htmlspecialchars($set['set_name']) ?></h4>

                    <form method="POST" class="update-form">
                        <label for="quantity_<?= $set['id_set_number'] ?>" class="form-label">Quantité :</label><br>
                        <input 
                            type="number" 
                            id="quantity_<?= $set['id_set_number'] ?>" 
                            name="quantity" 
                            min="1" 
                            value="<?= $set['quantity'] ?>" 
                            required
                        >
                        <input type="hidden" name="update_set_id" value="<?= $set['id_set_number'] ?>">
                        <button type="submit" class="update-btn btn">Mettre à jour</button>
                    </form>

                    <form method="POST" class="delete-form">
                        <input type="hidden" name="delete_set_id" value="<?= $set['id_set_number'] ?>">
                        <button type="submit" class="delete-btn btn">Supprimer</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
