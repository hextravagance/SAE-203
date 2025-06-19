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
    <title>Ma liste d'envie de set</title>
    <style>
        .grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-top: 20px; }
        .card { border: 1px solid #ccc; padding: 10px; text-align: center; }
        .card img { width: 100%; height: 150px; object-fit: cover; }
        form { margin-top: 10px; }
        input[type=number] { width: 50px; }
        button { cursor: pointer; }
    </style>
</head>
<body>
    <h1>Ma Wishlist LEGO</h1>

    <?php if (empty($sets)): ?>
        <p>Vous n'avez aucun set dans votre wishlist.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($sets as $set): ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($set['image_url']) ?>" alt="<?= htmlspecialchars($set['set_name']) ?>">
                    <h4><?= htmlspecialchars($set['set_name']) ?></h4>
                    <form method="POST" style="display: inline-block;">
                        <label for="quantity_<?= $set['id_set_number'] ?>">Quantité :</label>
                        <input 
                            type="number" 
                            id="quantity_<?= $set['id_set_number'] ?>" 
                            name="quantity" 
                            min="1" 
                            value="<?= $set['quantity'] ?>" 
                            required
                        >
                        <input type="hidden" name="update_set_id" value="<?= $set['id_set_number'] ?>">
                        <button type="submit">Mettre à jour</button>
                    </form>
                    <form method="POST" style="display: inline-block;">
                        <input type="hidden" name="delete_set_id" value="<?= $set['id_set_number'] ?>">
                        <button type="submit">Supprimer</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>
