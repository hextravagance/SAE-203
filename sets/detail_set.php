<?php
session_start();
include '../includes/config.php';

if (!isset($_GET['id'])) {
    echo "Aucun ID de set fourni.";
    exit;
}

$id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM lego_db WHERE id_set_number = :id");
$stmt->execute(['id' => $id]);
$set = $stmt->fetch();

if (!$set) {
    echo "Set introuvable.";
    exit;
}

$is_connected = isset($_SESSION['username']);
$user_id = null;

if ($is_connected) {
    $stmt = $db->prepare("SELECT id FROM SAE203_user WHERE username = :username");
    $stmt->execute(['username' => $_SESSION['username']]);
    $user = $stmt->fetch();
    if ($user) {
        $user_id = $user['id'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail du set <?= htmlspecialchars($set['set_name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        img { max-width: 300px; display: block; margin-bottom: 15px; }
        .back { margin-top: 20px; display: block; }
        .actions button { margin-right: 10px; padding: 10px 15px; }
        .stars { color: gold; font-size: 1.2em; }
        .comment { border-bottom: 1px solid #ccc; padding: 10px 0; }
        .error { color: red; }
        #commentsSection { margin-top: 20px; }
    </style>
</head>
<body data-setid="<?= htmlspecialchars($set['id_set_number']) ?>" data-userid="<?= $user_id ?? '' ?>">
    <h1><?= htmlspecialchars($set['set_name']) ?></h1>
    <img src="<?= htmlspecialchars($set['image_url']) ?>" alt="<?= htmlspecialchars($set['set_name']) ?>">
    <p><strong>Matricule :</strong> <?= htmlspecialchars($set['id_set_number']) ?></p>
    <p><strong>Année :</strong> <?= htmlspecialchars($set['year_released']) ?></p>
    <p><strong>Nombre de pièces :</strong> <?= htmlspecialchars($set['number_of_parts']) ?></p>
    <p><strong>Thème :</strong> <?= htmlspecialchars($set['theme_name']) ?></p>

    <?php if ($is_connected): ?>
        <div class="actions">
            <button onclick="addToList('wishlist')">Ajouter à la liste d'envie</button>
            <button onclick="addToList('owned')">Ajouter aux Sets Possédés</button>
            <p id="listMessage" class="error"></p>
            <a href="./wishlist.php"><button>Voir ma liste d'envie</button></a>
            <a href="./owned.php"><button>Voir mes Sets Possédés</button></a>

        </div>
    <?php else: ?>
        <p><em>Connectez-vous pour ajouter ce set à vos listes.</em></p>
        <a href="../authentification.php">Se connecter</a>
    <?php endif; ?>

    <hr>

    <h3>Note moyenne du set</h3>
    <div id="avgRatingArea">Chargement...</div>

    <div id="commentsSection">
        <h3>Commentaires</h3>
        <div id="commentsList">Chargement...</div>

        <?php if ($is_connected): ?>
            <h4>Ajouter un commentaire</h4>
            <form id="reviewForm">
                <label>Note :</label>
                <div id="starRating" style="font-size: 2em; cursor: pointer;">
                    <span data-value="1">☆</span>
                    <span data-value="2">☆</span>
                    <span data-value="3">☆</span>
                    <span data-value="4">☆</span>
                    <span data-value="5">☆</span>
                </div>
                <input type="hidden" id="rating" required>

                <label for="comment">Commentaire :</label><br>
                <textarea id="comment" rows="4" cols="50" placeholder="Votre commentaire..."></textarea><br><br>

                <button type="submit">Envoyer</button>
                <p id="message"></p>
            </form>
        <?php else: ?>
            <p><em>Connectez-vous pour commenter ce set.</em></p>
        <?php endif; ?>
    </div>

    <a class="back" href="sets.php">← Retour au catalogue</a>

    <script>
    const setId = document.body.dataset.setid;
    const userId = document.body.dataset.userid || null;

    function createStars(note) {
        const n = Math.round(note);
        return '★'.repeat(n) + '☆'.repeat(5 - n);
    }

    function fetchReviews() {
        fetch('./get_reviews.php?set_id=' + encodeURIComponent(setId))
            .then(res => res.json())
            .then(data => {
                const avgRatingDiv = document.getElementById('avgRatingArea');
                const commentsDiv = document.getElementById('commentsList');

                if (data.success) {
                    const avg = data.avg_rating || 0;
                    const count = data.count || 0;
                    avgRatingDiv.innerHTML = `<span class="stars">${createStars(avg)}</span> (${count} avis)`;

                    if (data.comments && data.comments.length > 0) {
                        commentsDiv.innerHTML = '';
                        data.comments.forEach(comment => {
                            const cDiv = document.createElement('div');
                            cDiv.className = 'comment';
                            cDiv.innerHTML = `
                                <div class="stars">${createStars(comment.rating)}</div>
                                <p>${comment.comment || "<em>Pas de commentaire</em>"}</p>
                                <small>Par <strong>${comment.username}</strong> le ${comment.date}</small>
                            `;
                            commentsDiv.appendChild(cDiv);
                        });
                    } else {
                        commentsDiv.innerHTML = '<p>Aucun commentaire pour ce set.</p>';
                    }
                } else {
                    avgRatingDiv.textContent = "Erreur de chargement.";
                    commentsDiv.textContent = "Erreur de chargement des commentaires.";
                }
            })
            .catch(error => {
                document.getElementById('avgRatingArea').textContent = "Erreur.";
                document.getElementById('commentsList').textContent = "Erreur.";
                console.error(error);
            });
    }

    function addToList(type) {
        const msg = document.getElementById('listMessage');
        msg.textContent = '';
        msg.style.color = 'red';

        if (!setId || !type) {
            msg.textContent = "Données manquantes.";
            return;
        }

        fetch('./add_set.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: type, set_id: setId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                msg.textContent = type === 'wishlist' ? "Ajouté à la wishlist." : "Ajouté aux sets possédés.";
                msg.style.color = "green";
            } else {
                msg.textContent = data.message || "Erreur lors de l'ajout.";
            }
        })
        .catch(() => {
            msg.textContent = "Erreur réseau.";
        });
    }

    let selectedRating = 0;
    document.querySelectorAll('#starRating span').forEach(star => {
        star.addEventListener('click', () => {
            selectedRating = parseInt(star.getAttribute('data-value'));
            document.getElementById('rating').value = selectedRating;

            document.querySelectorAll('#starRating span').forEach(s => {
                s.textContent = parseInt(s.getAttribute('data-value')) <= selectedRating ? '★' : '☆';
            });
        });
    });

    fetchReviews();

    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const rating = parseInt(document.getElementById('rating').value);
            const comment = document.getElementById('comment').value.trim();
            const message = document.getElementById('message');

            if (!rating || !userId || !setId) {
                message.textContent = "Tous les champs sont requis.";
                message.style.color = "red";
                return;
            }

            fetch('./add_review.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    set_id: setId,
                    user_id: userId,
                    rating: rating,
                    comment: comment
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    message.textContent = "Commentaire ajouté avec succès.";
                    message.style.color = "green";
                    reviewForm.reset();
                    document.querySelectorAll('#starRating span').forEach(s => s.textContent = '☆');
                    fetchReviews();
                } else {
                    message.textContent = data.error || "Erreur lors de l'envoi.";
                    message.style.color = "red";
                }
            })
            .catch(err => {
                message.textContent = "Erreur réseau.";
                message.style.color = "red";
            });
        });
    }
    </script>
</body>
</html>
