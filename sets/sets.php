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
    </style>
</head>
<body>
    <h1>Liste Complète LEGO</h1>
    <a href="../index.php">← Retour à l'accueil</a>

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

    <script>
        const allSets = <?php echo json_encode($sets); ?>;

        const grid = document.getElementById('setsGrid');
        const pagination = document.getElementById('pagination');
        const searchInput = document.getElementById('searchInput');
        const themeFilter = document.getElementById('themeFilter');
        const sortSelect = document.getElementById('sortSelect');
        const itemsPerPageSelect = document.getElementById('itemsPerPage');

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
                    div.innerHTML = `
                        <a href="detail_set.php?id=${set.id_set_number}" target="_blank" style="text-decoration: none; color: inherit;">
                        <img src="${set.image_url}" alt="${set.set_name}">
                        <h4>${set.set_name}</h4>
                        <p><strong>Matricule:</strong> ${set.id_set_number}</p>
                        <p><strong>Année:</strong> ${set.year_released}</p>
                        <p><strong>Pièces:</strong> ${set.number_of_parts}</p>
                        <p><strong>Thème:</strong> ${set.theme_name}</p>
                    </a>
                `;
                grid.appendChild(div);
            });

            const totalPages = Math.ceil(data.length / itemsPerPage);
            pagination.innerHTML = '';

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


            const pageInput = document.createElement('input');
            pageInput.type = 'number';
            pageInput.min = 1;
            pageInput.max = totalPages;
            pageInput.value = currentPage;
            pageInput.addEventListener('change', () => {
                const val = parseInt(pageInput.value);
                if (val >= 1 && val <= totalPages) {
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
                switch (sort) {
                    case 'name-asc': return a.set_name.localeCompare(b.set_name);
                    case 'name-desc': return b.set_name.localeCompare(a.set_name);
                    case 'id-asc': return a.id_set_number.localeCompare(b.id_set_number);
                    case 'id-desc': return b.id_set_number.localeCompare(a.id_set_number);
                    case 'year-asc': return parseInt(a.year_released) - parseInt(b.year_released);
                    case 'year-desc': return parseInt(b.year_released) - parseInt(a.year_released);
                }
            });

            const totalPages = Math.ceil(filteredSets.length / parseInt(itemsPerPageSelect.value));
            if (currentPage > totalPages) currentPage = totalPages > 0 ? totalPages : 1;
            displaySets(filteredSets);
        }

        searchInput.oninput = () => { currentPage = 1; updateDisplay(); };
        themeFilter.onchange = () => { currentPage = 1; updateDisplay(); };
        sortSelect.onchange = () => updateDisplay();
        itemsPerPageSelect.onchange = () => { currentPage = 1; updateDisplay(); };

        updateDisplay();
    </script>
</body>
</html>
