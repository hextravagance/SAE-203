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
    <meta charset="UTF-8" />
    <title>Détail du set <?= htmlspecialchars($set['set_name']) ?></title>
    <style>
        /* Reset & basics */
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0; padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: url('../image/lego-1044891.jpg') no-repeat center/cover fixed;
            color: #222;
        }

        /* Navbar glass */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 60px;
            background: rgba(255 255 255 / 0.2);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            padding: 0 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        nav h1 {
            font-weight: 700;
            color: #111;
            font-size: 1.5rem;
            user-select: none;
        }

        /* Main container glass */
        main.container {
            max-width: 900px;
            margin: 80px auto 40px; /* below navbar */
            background: rgba(255 255 255 / 0.15);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            padding: 30px 40px;
            color: #222;
        }

        /* Set info styles */
        main.container h2 {
            margin-top: 0;
            font-weight: 700;
            font-size: 2rem;
            color: #222;
            text-align: center;
        }
        main.container img {
            max-width: 320px;
            display: block;
            margin: 20px auto;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }

        main.container p {
            font-size: 1.1rem;
            margin: 8px 0;
            font-weight: 500;
            text-align: center;
        }
        main.container strong {
            color: #444;
        }

        /* Actions buttons */
        .actions {
            margin: 25px 0 40px;
            text-align: center;
        }
        .actions button,
        .actions a button {
            background: rgba(255 255 255 / 0.25);
            border: 1.8px solid rgba(255 255 255 / 0.5);
            color: #222;
            font-weight: 600;
            padding: 12px 25px;
            border-radius: 12px;
            cursor: pointer;
            margin: 0 8px 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(8px);
            text-decoration: none;
            display: inline-block;
        }
        .actions button:hover,
        .actions a button:hover {
            background: rgba(255 255 255 / 0.45);
            border-color: #555;
            color: #000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        .actions a {
            text-decoration: none;
        }
        .actions .error {
            display: block;
            margin-top: 10px;
            font-weight: 600;
            font-size: 1rem;
        }

        /* Section divider */
        hr {
            border: none;
            border-top: 1px solid rgba(0,0,0,0.15);
            margin: 40px 0;
        }

        /* Ratings & comments */
        #avgRatingArea {
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 20px;
        }
        .stars {
            color: gold;
            font-size: 1.5rem;
            user-select: none;
        }
        #commentsSection h3,
        #commentsSection h4 {
            font-weight: 700;
            margin-bottom: 12px;
            color: #222;
        }
        #commentsList {
            max-height: 280px;
            overflow-y: auto;
            padding-right: 10px;
            margin-bottom: 30px;
            border-radius: 12px;
            background: rgba(255 255 255 / 0.1);
            box-shadow: inset 0 0 5px rgba(0,0,0,0.1);
        }
        .comment {
            border-bottom: 1px solid rgba(0,0,0,0.1);
            padding: 12px 15px;
        }
        .comment:last-child {
            border-bottom: none;
        }
        .comment p {
            margin: 6px 0;
            font-size: 1rem;
            color: #333;
        }
        .comment small {
            color: #666;
            font-style: italic;
            font-size: 0.85rem;
            user-select: none;
        }
        .comment a {
            color: #0056b3;
            text-decoration: none;
        }
        .comment a:hover {
            text-decoration: underline;
        }

        /* Review form */
        #reviewForm {
            background: rgba(255 255 255 / 0.15);
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.1);
        }
        #reviewForm label {
            font-weight: 600;
            display: block;
            margin: 12px 0 6px;
        }
        #starRating span {
            font-size: 2.4rem;
            cursor: pointer;
            user-select: none;
            color: gold;
            transition: transform 0.15s ease;
        }
        #starRating span:hover {
            transform: scale(1.2);
        }
        #comment {
            width: 100%;
            resize: vertical;
            border-radius: 10px;
            border: 1px solid #ccc;
            padding: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s ease;
        }
        #comment:focus {
            outline: none;
            border-color: gold;
            box-shadow: 0 0 6px gold;
        }
        #reviewForm button {
            margin-top: 15px;
            background: gold;
            color: #222;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #reviewForm button:hover {
            background: #e6c200;
        }
        #message {
            margin-top: 12px;
            font-weight: 600;
        }

        /* Back link */
        .back {
            display: inline-block;
            margin-top: 40px;
            color: #222;
            font-weight: 700;
            text-decoration: none;
            padding: 8px 18px;
            border-radius: 12px;
            background: rgba(255 255 255 / 0.3);
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 14px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease;
        }
        .back:hover {
            background: rgba(255 255 255 / 0.5);
        }

        /* Responsive */
        @media (max-width: 600px) {
            main.container {
                padding: 20px 25px;
                margin: 70px 15px 30px;
            }
            main.container img {
                max-width: 100%;
            }
            .actions button,
            .actions a button {
                width: 100%;
                margin-bottom: 12px;
            }
        }
    </style>
</head>
<body data-setid="<?= htmlspecialchars($set['id_set_number']) ?>" data-userid="<?= $user_id ?? '' ?>">
<nav>
    <h1>LEGO Catalogue</h1>
</nav>
<main class="container">
    <h2><?= htmlspecialchars($set['set_name']) ?></h2>
    <img src="<?= htmlspecialchars($set['image_url']) ?>" alt="<?= htmlspecialchars($set['set_name']) ?>" />
    <p><strong>Matricule :</strong> <?= htmlspecialchars($set['id_set_number']) ?></p>
    <p><strong>Année :</strong> <?= htmlspecialchars($set['year_released']) ?></p>
    <p><strong>Nombre de pièces :</strong> <?= htmlspecialchars($set['number_of_parts']) ?></p>
    <p><strong>Thème :</strong> <?= htmlspecialchars($set['theme_name']) ?></p>

    <?php if ($is_connected): ?>
        <div class="actions">
            <button onclick="addToList('wishlist')">Ajouter à la liste d'envie</button>
            <button onclick="addToList('owned')">Ajouter à la liste possédée</button><br />
            <button onclick="window.location.href='./wishlist.php'" class="back">Liste d'envie</button>
            <button onclick="window.location.href='./owned.php'" class="back">Sets possédés</button>
            <div id="actionMsg" class="error"></div>
        </div>
    <?php else: ?>
        <p style="text-align:center; font-style: italic; color: #444;">Connectez-vous pour gérer vos listes et laisser un avis.</p>
    <?php endif; ?>

    <hr />

    <section id="commentsSection">
        <h3>Avis & Commentaires</h3>
        <div id="avgRatingArea">Chargement...</div>
        <div id="commentsList">Chargement des commentaires...</div>

        <?php if ($is_connected): ?>
            <form id="reviewForm">
                <h4>Laisser un avis</h4>
                <label for="starRating">Note :</label>
                <div id="starRating" role="radiogroup" aria-label="Note de 1 à 5 étoiles">
                    <span data-value="1" role="radio" aria-checked="false" tabindex="0">☆</span>
                    <span data-value="2" role="radio" aria-checked="false" tabindex="-1">☆</span>
                    <span data-value="3" role="radio" aria-checked="false" tabindex="-1">☆</span>
                    <span data-value="4" role="radio" aria-checked="false" tabindex="-1">☆</span>
                    <span data-value="5" role="radio" aria-checked="false" tabindex="-1">☆</span>
                </div>
                <label for="comment">Commentaire :</label>
                <textarea id="comment" name="comment" rows="4" placeholder="Votre commentaire..."></textarea>
                <button type="submit">Envoyer</button>
                <div id="message"></div>
            </form>
        <?php else: ?>
            <p style="text-align:center; font-style: italic; color: #444;">Connectez-vous pour laisser un avis.</p>
        <?php endif; ?>
    </section>

    <a href="./sets.php" class="back">← Retour à la liste</a>
</main>

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
                            <p>${comment.comment ? comment.comment : "<em>Pas de commentaire</em>"}</p>
                            <small>Par <strong><a href="../detail_user.php?id=${comment.user_id}">${comment.username}</a></strong> le ${comment.date}</small>
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

function addToList(listName) {
    if (!userId) {
        alert("Vous devez être connecté pour ajouter à une liste.");
        return;
    }
    fetch('./add_set.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ set_id: setId, type: listName })  // Changement ici : type au lieu de list, et suppression user_id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Set ajouté avec succès à la ' + listName);
        } else {
            alert('Erreur : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}


// Review stars selector
const stars = document.querySelectorAll('#starRating span');
let selectedRating = 0;

stars.forEach(star => {
    star.addEventListener('click', () => {
        selectedRating = parseInt(star.dataset.value);
        updateStars(selectedRating);
    });
    star.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            selectedRating = parseInt(star.dataset.value);
            updateStars(selectedRating);
        }
    });
});

function updateStars(rating) {
    stars.forEach(s => {
        if (parseInt(s.dataset.value) <= rating) {
            s.textContent = '★';
            s.setAttribute('aria-checked', 'true');
            s.tabIndex = 0;
        } else {
            s.textContent = '☆';
            s.setAttribute('aria-checked', 'false');
            s.tabIndex = -1;
        }
    });
}

document.getElementById('reviewForm')?.addEventListener('submit', e => {
    e.preventDefault();

    if (selectedRating < 1 || selectedRating > 5) {
        alert("Veuillez sélectionner une note entre 1 et 5 étoiles.");
        return;
    }

    const commentText = document.getElementById('comment').value.trim();

    fetch('./submit_review.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            user_id: userId,
            set_id: setId,
            rating: selectedRating,
            comment: commentText
        })
    })
    .then(res => res.json())
    .then(data => {
        const message = document.getElementById('message');
        if (data.success) {
            message.style.color = 'green';
            message.textContent = "Merci pour votre avis !";
            document.getElementById('comment').value = '';
            selectedRating = 0;
            updateStars(0);
            fetchReviews();
        } else {
            message.style.color = 'red';
            message.textContent = data.message || "Erreur lors de l'envoi.";
        }
    })
    .catch(() => {
        const message = document.getElementById('message');
        message.style.color = 'red';
        message.textContent = "Erreur réseau.";
    });
});

fetchReviews();
</script>
</body>
</html>
