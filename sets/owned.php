<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../authentification.php"); // Corrected path
    exit;
}

$user_id = $_SESSION['id_utilisateur'];
$message = ''; // For potential feedback messages
$message_type = '';

// Suppression d’un set possédé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_set_id'])) {
        $delete_id = $_POST['delete_set_id'];
        $stmt = $db->prepare("DELETE FROM SAE203_owned WHERE id_user = ? AND id_set_number = ?");
        if ($stmt->execute([$user_id, $delete_id])) {
            $message = "Set supprimé de vos possessions.";
            $message_type = "success";
        } else {
            $message = "Erreur lors de la suppression du set.";
            $message_type = "error";
        }
    }

    // Mise à jour de la quantité
    if (isset($_POST['update_set_id'], $_POST['quantity'])) {
        $update_id = $_POST['update_set_id'];
        $quantity = max(1, intval($_POST['quantity'])); // minimum 1

        $stmt = $db->prepare("UPDATE SAE203_owned SET quantity = ? WHERE id_user = ? AND id_set_number = ?");
        if ($stmt->execute([$quantity, $user_id, $update_id])) {
            $message = "Quantité mise à jour.";
            $message_type = "success";
        } else {
            $message = "Erreur lors de la mise à jour de la quantité.";
            $message_type = "error";
        }
    }
}

// Récupération des sets
$stmt = $db->prepare("
    SELECT l.id_set_number, l.set_name, l.image_url, o.quantity
    FROM SAE203_owned o
    JOIN lego_db l ON o.id_set_number = l.id_set_number
    WHERE o.id_user = ?
");
$stmt->execute([$user_id]);
$sets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once '../includes/header.php'; ?>

<main class="container">
    <h1>Mes Sets Possédés</h1>
    <p><a href="sets.php" class="button">← Retour au catalogue</a></p>

    <?php if ($message): ?>
        <div class="message <?= htmlspecialchars($message_type) ?>" style="margin-top:1rem;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($sets)): ?>
        <div class="message info" style="margin-top:1rem;">
            <p>Vous n'avez aucun set dans votre collection pour le moment.</p>
            <p><a href="sets.php" class="button">Parcourir le catalogue</a></p>
        </div>
    <?php else: ?>
        <div class="set-grid" style="margin-top:1rem;">
            <?php foreach ($sets as $set): ?>
                <div class="set-card">
                    <a href="detail_set.php?id=<?= htmlspecialchars($set['id_set_number']) ?>" class="set-card-link">
                        <img src="<?= htmlspecialchars($set['image_url'] ? $set['image_url'] : '../assets/images/default_lego.png') ?>"
                             alt="<?= htmlspecialchars($set['set_name']) ?>"
                             onerror="this.onerror=null;this.src='../assets/images/default_lego.png';">
                        <h4><?= htmlspecialchars($set['set_name']) ?></h4>
                        <p>Matricule: <?= htmlspecialchars($set['id_set_number']) ?></p>
                    </a>
                    <div class="set-card-actions">
                        <form method="POST" style="margin-bottom: 0.5rem;">
                            <div class="form-group">
                                <label for="quantity_<?= $set['id_set_number'] ?>">Quantité :</label>
                                <input
                                    type="number"
                                    id="quantity_<?= $set['id_set_number'] ?>"
                                    name="quantity"
                                    min="1"
                                    value="<?= htmlspecialchars($set['quantity']) ?>"
                                    required
                                >
                            </div>
                            <input type="hidden" name="update_set_id" value="<?= htmlspecialchars($set['id_set_number']) ?>">
                            <button type="submit">Mettre à jour quantité</button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce set de vos possessions ?');">
                            <input type="hidden" name="delete_set_id" value="<?= htmlspecialchars($set['id_set_number']) ?>">
                            <button type="submit" class="button-danger">Supprimer</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>
