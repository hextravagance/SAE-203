<?php

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
        .card { border: 1px solid #ccc; padding: 10px; text-align: center; }
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
                    <img src="${set.image_url}" alt="${set.set_name}">
                    <h4>${set.set_name}</h4>
                    <p><strong>Matricule:</strong> ${set.id_set_number}</p>
                    <p><strong>Année:</strong> ${set.year_released}</p>
                    <p><strong>Pièces:</strong> ${set.number_of_parts}</p>
                    <p><strong>Thème:</strong> ${set.theme_name}</p>
                `;
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

        // Initialisation
        updateDisplay();

        // Événements
        searchInput.addEventListener('input', () => { currentPage = 1; updateDisplay(); });
        themeFilter.addEventListener('change', () => { currentPage = 1; updateDisplay(); });
        sortSelect.addEventListener('change', () => { currentPage = 1; updateDisplay(); });
        itemsPerPageSelect.addEventListener('change', () => { currentPage = 1; updateDisplay(); });
    </script>
</body>
</html>
