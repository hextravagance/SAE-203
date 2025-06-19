<?php
session_start();
include '../includes/config.php';

$is_connected = isset($_SESSION['username']);
$username = $is_connected ? $_SESSION['username'] : null;
$id_user = null;

if ($is_connected) {
    $stmt = $db->prepare("SELECT id FROM SAE203_user WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();
    if ($user) {
        $id_user = $user['id'];
    } else {
        $is_connected = false;
    }
}

$sql = "SELECT id_set_number, set_name, REPLACE(year_released, '.0', '') as year_released, number_of_parts, image_url, theme_name FROM lego_db";
$stmt = $db->query($sql);
$sets = $stmt->fetchAll();

$themes = array_unique(array_map(fn($s) => $s['theme_name'], $sets));
sort($themes);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catalogue LEGO</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .controls { margin-bottom: 20px; }
        .controls input, .controls select { padding: 8px; margin-right: 10px; }
        .grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 20px; }
        .card { border: 1px solid #ccc; padding: 10px; text-align: center; cursor: pointer; }
        .card img { width: 100%; height: 150px; object-fit: cover; }
        .card h4 { margin: 10px 0 5px; }
        .card p { margin: 5px 0; font-size: 14px; }
        .pagination { text-align: center; margin-top: 20px; }
        .pagination button { padding: 8px 12px; margin: 0 3px; }
        .pagination input { width: 50px; text-align: center; padding: 6px; }
        #popup { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border: 2px solid #333; padding: 20px; max-width: 400px; box-shadow: 0 0 10px rgba(0,0,0,0.5); display: none; z-index: 1000; }
        #popup img { max-width: 100%; height: auto; display: block; margin-bottom: 10px; }
        #popup button { margin: 5px 5px 5px 0; padding: 8px 12px; cursor: pointer; }
        #popupClose { position: absolute; top: 5px; right: 10px; cursor: pointer; font-weight: bold; font-size: 18px; }
        #message { margin-top: 10px; font-weight: bold; color: green; }
        #message.error { color: red; }
    </style>
</head>
<body>
    <h1>Liste Complète LEGO</h1>
    <a href="../index.php">← Retour a l'accueil</a>

    <?php if ($is_connected): ?>
        <div style="margin-bottom: 10px;">
            <a href="./wishlist.php">Ma Wishlist</a>
            <a href="./owned.php">Mes Sets Possédés</a>
        </div>
        <p>Connecté en tant que : <strong><?= htmlspecialchars($username) ?></strong></p>
    <?php else: ?>
        <p><em>Vous n'êtes pas connecté. Connectez-vous pour ajouter des sets à vos listes.</em></p>
        <a href="authentification.php">Se connecter</a>
    <?php endif; ?>

    <div class="controls">
        <input type="text" id="searchInput" placeholder="Rechercher un set...">
        <select id="themeFilter">
            <option value="">-- Filtrer par thème --</option>
            <?php foreach ($themes as $theme): ?>
                <option value="<?= htmlspecialchars($theme) ?>"><?= htmlspecialchars($theme) ?></option>
            <?php endforeach; ?>
        </select>
        <select id="sortSelect">
            <option value="name-asc">Nom A-Z</option>
            <option value="name-desc">Nom Z-A</option>
            <option value="id-asc">Matricule croissant</option>
            <option value="id-desc">Matricule décroissant</option>
            <option value="year-desc">Année (récent - ancien)</option>
            <option value="year-asc">Année (ancien - récent)</option>
        </select>
        <select id="itemsPerPage">
            <option value="25">25 par page</option>
            <option value="50" selected>50 par page</option>
            <option value="75">75 par page</option>
            <option value="100">100 par page</option>
        </select>
    </div>

    <div class="grid" id="setsGrid"></div>
    <div class="pagination" id="pagination"></div>

    <div id="popup">
        <span id="popupClose">✖</span>
        <div id="popupContent"></div>
    </div>

    <script>
        const allSets = <?php echo json_encode($sets); ?>;
        const isConnected = <?= $is_connected ? 'true' : 'false' ?>;

        const grid = document.getElementById('setsGrid');
        const pagination = document.getElementById('pagination');
        const searchInput = document.getElementById('searchInput');
        const themeFilter = document.getElementById('themeFilter');
        const sortSelect = document.getElementById('sortSelect');
        const itemsPerPageSelect = document.getElementById('itemsPerPage');

        const popup = document.getElementById('popup');
        const popupContent = document.getElementById('popupContent');
        const popupClose = document.getElementById('popupClose');

        let currentPage = 1;
        let filteredSets = [];

        function displaySets(data) {
            const itemsPerPage = parseInt(itemsPerPageSelect.value);
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedData = data.slice(start, end);

            grid.innerHTML = '';
            paginatedData.forEach(set => {
                const div = document.createElement('div');
                div.className = 'card';
                div.tabIndex = 0;
                div.setAttribute('role', 'button');
                div.innerHTML = `
                    <img src="${set.image_url}" alt="${set.set_name}">
                    <h4>${set.set_name}</h4>
                    <p><strong>Matricule:</strong> ${set.id_set_number}</p>
                    <p><strong>Année:</strong> ${set.year_released}</p>
                    <p><strong>Pièces:</strong> ${set.number_of_parts}</p>
                    <p><strong>Thème:</strong> ${set.theme_name}</p>
                `;
                div.addEventListener('click', () => openPopup(set));
                div.addEventListener('keypress', e => { if(e.key === 'Enter') openPopup(set); });
                grid.appendChild(div);
            });

            const totalPages = Math.ceil(data.length / itemsPerPage);
            pagination.innerHTML = '';

            // Boutons pages rapides
            [1, 2, 15, 30, 50, 75, 100].forEach(p => {
                if (p <= totalPages) {
                    const btn = document.createElement('button');
                    btn.textContent = p;
                    if (p === currentPage) btn.disabled = true;
                    btn.addEventListener('click', () => {
                        currentPage = p;
                        updateDisplay();
                    });
                    pagination.appendChild(btn);
                }
            });

            // Input pour aller à une page précise
            const pageInput = document.createElement('input');
            pageInput.type = 'number';
            pageInput.min = 1;
            pageInput.max = totalPages;
            pageInput.value = currentPage;
            pageInput.addEventListener('change', () => {
                const val = parseInt(pageInput.value);
                if(val >= 1 && val <= totalPages) {
                    currentPage = val;
                    updateDisplay();
                }
            });
            pagination.appendChild(document.createTextNode(' Aller à la page : '));
            pagination.appendChild(pageInput);
        }

        function updateDisplay() {
            const search = searchInput.value.toLowerCase();
            const theme = themeFilter.value.toLowerCase();
            filteredSets = allSets.filter(set =>
                set.set_name.toLowerCase().includes(search) &&
                (theme === '' || set.theme_name.toLowerCase() === theme)
            );

            const sort = sortSelect.value;
            filteredSets.sort((a, b) => {
                switch(sort) {
                    case 'name-asc': return a.set_name.localeCompare(b.set_name);
                    case 'name-desc': return b.set_name.localeCompare(a.set_name);
                    case 'id-asc': return a.id_set_number.localeCompare(b.id_set_number);
                    case 'id-desc': return b.id_set_number.localeCompare(a.id_set_number);
                    case 'year-asc': return parseInt(a.year_released) - parseInt(b.year_released);
                    case 'year-desc': return parseInt(b.year_released) - parseInt(a.year_released);
                }
            });

            const totalPages = Math.ceil(filteredSets.length / parseInt(itemsPerPageSelect.value));
            if(currentPage > totalPages) currentPage = totalPages > 0 ? totalPages : 1;
            displaySets(filteredSets);
        }

        function openPopup(set) {
            let html = `
                <img src="${set.image_url}" alt="${set.set_name}">
                <h2>${set.set_name}</h2>
                <p><strong>Matricule:</strong> ${set.id_set_number}</p>
                <p><strong>Année:</strong> ${set.year_released}</p>
                <p><strong>Pièces:</strong> ${set.number_of_parts}</p>
                <p><strong>Thème:</strong> ${set.theme_name}</p>
            `;

            if(isConnected) {
                html += `
                    <button id="btnWishlist">Ajouter à la Wishlist</button>
                    <button id="btnOwned">Ajouter aux Sets Possédés</button>
                    <div id="message"></div>
                `;
            } else {
                html += `<p><em>Connectez-vous pour ajouter ce set à vos listes.</em></p>`;
            }

            popupContent.innerHTML = html;
            popup.style.display = 'block';

            if(isConnected) {
                document.getElementById('btnWishlist').onclick = () => addSet('wishlist', set.id_set_number);
                document.getElementById('btnOwned').onclick = () => addSet('owned', set.id_set_number);
            }
        }

        popupClose.onclick = () => { popup.style.display = 'none'; };

        window.onclick = e => {
            if(e.target === popup) popup.style.display = 'none';
        };

        function addSet(type, setId) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = 'Ajout en cours...';
            messageDiv.className = '';

            fetch('./add_set.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type, set_id: setId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    messageDiv.textContent = `Set ajouté à ${type === 'wishlist' ? 'la Wishlist' : 'vos Sets Possédés'} !`;
                    messageDiv.className = '';
                } else {
                    messageDiv.textContent = data.message || 'Erreur lors de l\'ajout.';
                    messageDiv.className = 'error';
                }
            })
            .catch(() => {
                messageDiv.textContent = 'Erreur réseau.';
                messageDiv.className = 'error';
            });
        }


        // Événements filtres et recherche
        [searchInput, themeFilter, sortSelect, itemsPerPageSelect].forEach(el => {
            el.addEventListener('input', () => {
                currentPage = 1;
                updateDisplay();
            });
        });

        // Initial display
        updateDisplay();
    </script>
</body>
</html>
