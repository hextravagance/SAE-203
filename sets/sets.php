<?php
session_start();
include '../includes/config.php';

// Récupération des sets depuis la base de données
$sql = "SELECT id_set_number, set_name, REPLACE(year_released, '.0', '') as year_released, number_of_parts, image_url, theme_name FROM lego_db";
$stmt = $db->query($sql);
$sets = $stmt->fetchAll();

// Extraire tous les thèmes uniques
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

        /* Popup styles */
        #popupOverlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0,0,0,0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        #popupContent {
            background: #fff;
            padding: 20px;
            width: 400px;
            max-width: 90%;
            border-radius: 8px;
            position: relative;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        #popupContent img {
            max-width: 100%;
            height: auto;
            margin-bottom: 15px;
        }
        #popupClose {
            position: absolute;
            top: 10px; right: 10px;
            background: #f44336;
            border: none;
            color: white;
            font-size: 18px;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
        }
        #popupContent h3 {
            margin-top: 0;
        }
        #popupContent p {
            margin: 6px 0;
        }
    </style>
</head>
<body>
    <h1>Liste Complète LEGO</h1>

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

    <!-- Popup -->
    <div id="popupOverlay">
        <div id="popupContent">
            <button id="popupClose">&times;</button>
            <img id="popupImage" src="" alt="Image du set">
            <h3 id="popupName"></h3>
            <p><strong>Matricule :</strong> <span id="popupId"></span></p>
            <p><strong>Année :</strong> <span id="popupYear"></span></p>
            <p><strong>Pièces :</strong> <span id="popupParts"></span></p>
            <p><strong>Thème :</strong> <span id="popupTheme"></span></p>
        </div>
    </div>

    <script>
        const allSets = <?php echo json_encode($sets); ?>;

        const grid = document.getElementById('setsGrid');
        const pagination = document.getElementById('pagination');
        const searchInput = document.getElementById('searchInput');
        const themeFilter = document.getElementById('themeFilter');
        const sortSelect = document.getElementById('sortSelect');
        const itemsPerPageSelect = document.getElementById('itemsPerPage');

        const popupOverlay = document.getElementById('popupOverlay');
        const popupClose = document.getElementById('popupClose');
        const popupImage = document.getElementById('popupImage');
        const popupName = document.getElementById('popupName');
        const popupId = document.getElementById('popupId');
        const popupYear = document.getElementById('popupYear');
        const popupParts = document.getElementById('popupParts');
        const popupTheme = document.getElementById('popupTheme');

        let currentPage = 1;

        function displaySets(data) {
            const itemsPerPage = parseInt(itemsPerPageSelect.value);
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedData = data.slice(start, end);

            grid.innerHTML = '';
            paginatedData.forEach(set => {
                const div = document.createElement('div');
                div.className = 'card';
                div.tabIndex = 0; // pour focus clavier
                div.setAttribute('role', 'button');
                div.setAttribute('aria-pressed', 'false');
                div.innerHTML = `
                    <img src="${set.image_url}" alt="${set.set_name}">
                    <h4>${set.set_name}</h4>
                    <p><strong>Matricule:</strong> ${set.id_set_number}</p>
                    <p><strong>Année:</strong> ${set.year_released}</p>
                    <p><strong>Pièces:</strong> ${set.number_of_parts}</p>
                    <p><strong>Thème:</strong> ${set.theme_name}</p>
                `;

                // Ouvrir popup au clic
                div.addEventListener('click', () => {
                    openPopup(set);
                });

                // Aussi ouverture au clavier (Enter)
                div.addEventListener('keydown', e => {
                    if(e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        openPopup(set);
                    }
                });

                grid.appendChild(div);
            });

            const totalPages = Math.ceil(data.length / itemsPerPage);
            pagination.innerHTML = '';

            const pageInput = document.createElement('input');
            pageInput.type = 'number';
            pageInput.min = 1;
            pageInput.max = totalPages;
            pageInput.value = currentPage;
            pageInput.addEventListener('change', () => {
                const pageNum = parseInt(pageInput.value);
                if (pageNum >= 1 && pageNum <= totalPages) {
                    currentPage = pageNum;
                    updateDisplay();
                }
            });

            const quickJumps = [1, 2, 15, 30, 50, 75, 100];
            quickJumps.forEach(p => {
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

            pagination.appendChild(document.createTextNode(' Aller à la page : '));
            pagination.appendChild(pageInput);
        }

        function updateDisplay() {
            const search = searchInput.value.toLowerCase();
            const theme = themeFilter.value.toLowerCase();
            let filtered = allSets.filter(set =>
                set.set_name.toLowerCase().includes(search) &&
                (theme === '' || set.theme_name.toLowerCase() === theme)
            );

            const sort = sortSelect.value;
            filtered.sort((a, b) => {
                if (sort === 'name-asc') return a.set_name.localeCompare(b.set_name);
                if (sort === 'name-desc') return b.set_name.localeCompare(a.set_name);
                if (sort === 'id-asc') return a.id_set_number.localeCompare(b.id_set_number);
                if (sort === 'id-desc') return b.id_set_number.localeCompare(a.id_set_number);
                if (sort === 'year-asc') return parseInt(a.year_released) - parseInt(b.year_released);
                if (sort === 'year-desc') return parseInt(b.year_released) - parseInt(a.year_released);
                return 0;
            });

            const totalPages = Math.ceil(filtered.length / parseInt(itemsPerPageSelect.value));
            if (currentPage > totalPages) currentPage = 1;

            displaySets(filtered);
        }

        function openPopup(set) {
            popupImage.src = set.image_url;
            popupImage.alt = set.set_name;
            popupName.textContent = set.set_name;
            popupId.textContent = set.id_set_number;
            popupYear.textContent = set.year_released;
            popupParts.textContent = set.number_of_parts;
            popupTheme.textContent = set.theme_name;

            popupOverlay.style.display = 'flex';
        }

        function closePopup() {
            popupOverlay.style.display = 'none';
            popupImage.src = '';
            popupImage.alt = '';
        }

        // Fermeture popup au clic sur bouton ou en cliquant hors du contenu
        popupClose.addEventListener('click', closePopup);
        popupOverlay.addEventListener('click', e => {
            if (e.target === popupOverlay) {
                closePopup();
            }
        });

        // Initialisation
        updateDisplay();

        // Événements filtres et pagination
        searchInput.addEventListener('input', () => { currentPage = 1; updateDisplay(); });
        themeFilter.addEventListener('change', () => { currentPage = 1; updateDisplay(); });
        sortSelect.addEventListener('change', () => { currentPage = 1; updateDisplay(); });
        itemsPerPageSelect.addEventListener('change', () => { currentPage = 1; updateDisplay(); });
    </script>
</body>
</html>
