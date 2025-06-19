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
<?php require_once '../includes/header.php'; ?>

<main class="container" data-setid="<?= htmlspecialchars($set['id_set_number']) ?>" data-userid="<?= $user_id ?? '' ?>">
    <div class="set-detail-layout">
        <div class="set-detail-image">
            <img src="<?= htmlspecialchars($set['image_url'] ? $set['image_url'] : '../assets/images/default_lego.png') ?>"
                 alt="<?= htmlspecialchars($set['set_name']) ?>"
                 onerror="this.onerror=null;this.src='../assets/images/default_lego.png';">
        </div>
        <div class="set-detail-info">
            <h1><?= htmlspecialchars($set['set_name']) ?></h1>
            <p><strong>Matricule :</strong> <?= htmlspecialchars($set['id_set_number']) ?></p>
            <p><strong>Année :</strong> <?= htmlspecialchars(str_replace('.0', '', $set['year_released'])) ?></p>
            <p><strong>Nombre de pièces :</strong> <?= htmlspecialchars($set['number_of_parts']) ?></p>
            <p><strong>Thème :</strong> <?= htmlspecialchars($set['theme_name']) ?></p>

            <?php if ($is_connected): ?>
                <div class="set-detail-actions">
                    <button onclick="addToList('wishlist')" class="button">Ajouter à ma Wishlist</button>
                    <button onclick="addToList('owned')" class="button">Ajouter à mes Sets Possédés</button>
                    <a href="./wishlist.php" class="button">Voir ma Wishlist</a>
                    <a href="./owned.php" class="button">Voir mes Sets Possédés</a>
                </div>
                <p id="listMessage" class="message" style="display:none;"></p>
            <?php else: ?>
                <p><em><a href="../authentification.php">Connectez-vous</a> pour ajouter ce set à vos listes ou pour commenter.</em></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="reviews-section">
        <h3>Note moyenne du set</h3>
        <div id="avgRatingArea" class="star-display">Chargement...</div>

        <div id="commentsContainer"> <!-- Renamed from commentsSection to avoid duplicate ID -->
            <h3>Commentaires</h3>
            <div id="commentsList">Chargement...</div>

            <?php if ($is_connected): ?>
                <h4>Ajouter un commentaire</h4>
                <form id="reviewForm" class="form-group">
                    <label for="starRating">Votre note :</label>
                    <div id="starRating">
                        <span data-value="1">☆</span><span data-value="2">☆</span><span data-value="3">☆</span><span data-value="4">☆</span><span data-value="5">☆</span>
                    </div>
                    <input type="hidden" id="rating" name="rating" required>

                    <label for="comment">Votre commentaire :</label>
                    <textarea id="comment" name="comment" rows="4" placeholder="Écrivez votre avis ici..."></textarea>

                    <button type="submit">Envoyer mon commentaire</button>
                    <p id="message" class="message" style="display:none;"></p>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="auth-links" style="margin-top: 2rem;"> <!-- Using auth-links for consistent link styling -->
         <p><a href="sets.php" class="button">← Retour au catalogue</a></p>
    </div>
</main>

<script>
    const container = document.querySelector('main.container');
    const setId = container.dataset.setid;
    const userId = container.dataset.userid || null;

    function createStarsDisplay(note) { // Renamed and wrapped output
        const n = Math.round(parseFloat(note)); // Ensure note is a number
        const starsHtml = '★'.repeat(n) + '☆'.repeat(5 - n);
        return `<span class="star-display">${starsHtml}</span>`;
    }

    function fetchReviews() {
        fetch('./get_reviews.php?set_id=' + encodeURIComponent(setId)) // Corrected path
            .then(res => res.json())
            .then(data => {
                const avgRatingDiv = document.getElementById('avgRatingArea');
                const commentsListDiv = document.getElementById('commentsList'); // Corrected variable name

                if (data.success) {
                    const avg = data.avg_rating || 0;
                    const count = data.count || 0;
                    avgRatingDiv.innerHTML = `${createStarsDisplay(avg)} (${count} avis)`;

                    if (data.comments && data.comments.length > 0) {
                        commentsListDiv.innerHTML = ''; // Clear previous comments
                        data.comments.forEach(comment => {
                            const commentDiv = document.createElement('div'); // Renamed variable
                            commentDiv.className = 'comment';
                            commentDiv.innerHTML = `
                                ${createStarsDisplay(comment.rating)}
                                <p>${comment.comment || "<em>Pas de commentaire écrit.</em>"}</p>
                                <small>Par <strong>${comment.username}</strong> le ${new Date(comment.date).toLocaleDateString('fr-FR')}</small>
                            `;
                            commentsListDiv.appendChild(commentDiv); // Append to correct div
                        });
                    } else {
                        commentsListDiv.innerHTML = '<p>Aucun commentaire pour ce set pour le moment.</p>';
                    }
                } else {
                    avgRatingDiv.innerHTML = createStarsDisplay(0) + " (0 avis)";
                    commentsListDiv.innerHTML = "<p>Erreur lors du chargement des commentaires.</p>";
                }
            })
            .catch(error => {
                document.getElementById('avgRatingArea').innerHTML = createStarsDisplay(0) + " (Erreur)";
                document.getElementById('commentsList').innerHTML = "<p>Erreur de connexion pour charger les commentaires.</p>";
                console.error('FetchReviews Error:', error);
            });
    }

    function addToList(type) {
        const msgElement = document.getElementById('listMessage');
        if (!msgElement) return; // Guard clause

        msgElement.textContent = '';
        msgElement.className = 'message'; // Reset classes
        msgElement.style.display = 'none';


        if (!setId || !type || !userId) { // Added userId check
            msgElement.textContent = "Informations manquantes pour cette action.";
            msgElement.classList.add('error');
            msgElement.style.display = 'block';
            return;
        }

        fetch('./add_set.php', { // Corrected path
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: type, set_id: setId, user_id: userId }) // Added user_id
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                msgElement.textContent = data.message || (type === 'wishlist' ? "Ajouté à la wishlist." : "Ajouté aux sets possédés.");
                msgElement.classList.add('success');
            } else {
                msgElement.textContent = data.message || "Erreur lors de l'ajout.";
                msgElement.classList.add('error');
            }
            msgElement.style.display = 'block';
        })
        .catch((error) => {
            msgElement.textContent = "Erreur réseau.";
            msgElement.classList.add('error');
            msgElement.style.display = 'block';
            console.error('AddToList Error:', error);
        });
    }

    let selectedRating = 0;
    const starRatingContainer = document.getElementById('starRating');
    if (starRatingContainer) {
        starRatingContainer.querySelectorAll('span').forEach(star => {
            star.addEventListener('click', () => {
                selectedRating = parseInt(star.getAttribute('data-value'));
                const ratingInput = document.getElementById('rating');
                if (ratingInput) ratingInput.value = selectedRating;

                starRatingContainer.querySelectorAll('span').forEach(s => {
                    s.textContent = parseInt(s.getAttribute('data-value')) <= selectedRating ? '★' : '☆';
                });
            });
        });
    }

    fetchReviews(); // Initial fetch

    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm && userId) { // Ensure user is connected to attach listener
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const ratingInput = document.getElementById('rating');
            const rating = ratingInput ? parseInt(ratingInput.value) : 0;
            const commentInput = document.getElementById('comment');
            const comment = commentInput ? commentInput.value.trim() : '';
            const messageElement = document.getElementById('message');

            if (!messageElement) return;
            messageElement.textContent = '';
            messageElement.className = 'message'; // Reset classes
            messageElement.style.display = 'none';

            if (!rating || rating < 1 || rating > 5) {
                messageElement.textContent = "Veuillez sélectionner une note entre 1 et 5 étoiles.";
                messageElement.classList.add('error');
                messageElement.style.display = 'block';
                return;
            }
            if (!comment) {
                 messageElement.textContent = "Veuillez écrire un commentaire.";
                messageElement.classList.add('error');
                messageElement.style.display = 'block';
                return;
            }


            fetch('./add_review.php', { // Corrected path
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
                    messageElement.textContent = "Commentaire ajouté avec succès !";
                    messageElement.classList.add('success');
                    if (reviewForm) reviewForm.reset();
                    if (starRatingContainer) {
                         starRatingContainer.querySelectorAll('span').forEach(s => s.textContent = '☆');
                    }
                    selectedRating = 0; // Reset selected rating
                    if (ratingInput) ratingInput.value = '';
                    fetchReviews(); // Refresh comments and average rating
                } else {
                    messageElement.textContent = data.message || "Erreur lors de l'envoi du commentaire.";
                    messageElement.classList.add('error');
                }
                messageElement.style.display = 'block';
            })
            .catch(error => {
                messageElement.textContent = "Erreur réseau lors de l'ajout du commentaire.";
                messageElement.classList.add('error');
                messageElement.style.display = 'block';
                console.error('AddReview Error:', error);
            });
        });
    }
</script>
</main>
<?php require_once '../includes/footer.php'; ?>
