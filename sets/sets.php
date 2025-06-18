<?php
include 'config.php';

// Récupération des sets depuis la base de données
$sql = "SELECT id_set_number, set_name, REPLACE(year_released, '.0', '') as year_released, number_of_parts, image_url, theme_name FROM lego_sets";
$stmt = $db->query($sql);
$sets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Sets LEGO</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .controls { margin-bottom: 20px; }
        .controls input, .controls select { padding: 8px; margin-right: 10px; }
        .grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; }
        .card { border: 1px solid #ccc; padding: 10px; text-align: center; }
        .card img { width: 100%; height: 150px; object-fit: cover; }
        .card h4 { margin: 10px 0 5px; }
        .card p { margin: 5px 0; font-size: 14px; }
    </style>
</head>
<body>
    <h1>Catalogue LEGO</h1>

    <div class="controls">
        <input type="text" id="searchInput" placeholder="Rechercher un set...">
        <input type="text" id="themeFilter" placeholder="Filtrer par thème...">

        <select id="sortSelect">
            <option value="name-asc">Nom A-Z</option>
            <option value="name-desc">Nom Z-A</option>
            <option value="id-asc">Matricule croissant</option>
            <option value="id-desc">Matricule décroissant</option>
            <option value="year-desc">Année (récent - ancien)</option>
            <option value="year-asc">Année (ancien - récent)</option>
        </select>
    </div>

    <div class="grid" id="setsGrid"></div>

    <script>
        const allSets = <?php echo json_encode($sets); ?>;

        const grid = document.getElementById('setsGrid');
        const searchInput = document.getElementById('searchInput');
        const themeFilter = document.getElementById('themeFilter');
        const sortSelect = document.getElementById('sortSelect');

        function displaySets(data) {
            grid.innerHTML = '';
            data.forEach(set => {
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
        }

        function updateDisplay() {
            const search = searchInput.value.toLowerCase();
            const theme = themeFilter.value.toLowerCase();
            let filtered = allSets.filter(set =>
                set.set_name.toLowerCase().includes(search) &&
                set.theme_name.toLowerCase().includes(theme)
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

            displaySets(filtered);
        }

        // Initialisation
        displaySets(allSets);

        // Événements
        searchInput.addEventListener('input', updateDisplay);
        themeFilter.addEventListener('input', updateDisplay);
        sortSelect.addEventListener('change', updateDisplay);
    </script>
</body>
</html>
<?php include 'footer.php'; ?>