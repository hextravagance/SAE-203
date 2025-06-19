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

<?php require_once '../includes/header.php'; ?>

<main class="container">
    <h1>Catalogue LEGO</h1>

    <div class="user-actions-bar">
        <div class="user-info">
            <?php if ($is_connected): ?>
                <p>Connecté en tant que : <strong><?= htmlspecialchars($username) ?></strong></p>
            <?php else: ?>
                <p><em>Vous n'êtes pas connecté.</em></p>
            <?php endif; ?>
        </div>
        <div class="user-links">
            <?php if ($is_connected): ?>
                <a href="./wishlist.php" class="button">Ma Wishlist</a>
                <a href="./owned.php" class="button">Mes Sets Possédés</a>
            <?php else: ?>
                <a href="../authentification.php" class="button">Se connecter</a>
            <?php endif; ?>
            <a href="../index.php" class="button">← Retour à l'accueil</a>
        </div>
    </div>

    <div class="controls-bar">
        <input type="text" id="searchInput" placeholder="Rechercher par nom, matricule, année...">
        <select id="themeFilter">
            <option value="">-- Filtrer par thème --</option>
            <?php foreach ($themes as $theme): ?>
                <option value="<?= htmlspecialchars($theme) ?>"><?= htmlspecialchars($theme) ?></option>
            <?php endforeach; ?>
        </select>
        <select id="sortSelect">
            <option value="name-asc">Nom (A-Z)</option>
            <option value="name-desc">Nom (Z-A)</option>
            <option value="id-asc">Matricule (Croissant)</option>
            <option value="id-desc">Matricule (Décroissant)</option>
            <option value="year-desc">Année (Plus Récent)</option>
            <option value="year-asc">Année (Plus Ancien)</option>
            <option value="parts-desc">Pièces (Plus)</option>
            <option value="parts-asc">Pièces (Moins)</option>
        </select>
        <select id="itemsPerPage">
            <option value="24">24 par page</option>
            <option value="48" selected>48 par page</option>
            <option value="72">72 par page</option>
            <option value="96">96 par page</option>
        </select>
    </div>

    <div class="set-grid" id="setsGrid"></div>
    <div class="pagination" id="paginationControls"></div> <!-- Changed ID -->

    <script>
        const allSets = <?php echo json_encode($sets); ?>;

        const grid = document.getElementById('setsGrid');
        const paginationControls = document.getElementById('paginationControls'); // Changed ID
        const searchInput = document.getElementById('searchInput'); // Keep this
        const themeFilter = document.getElementById('themeFilter'); // Keep this
        const sortSelect = document.getElementById('sortSelect'); // Keep this
        const itemsPerPageSelect = document.getElementById('itemsPerPage'); // Keep this

        let currentPage = 1;
        let filteredAndSortedSets = []; // Renamed for clarity

        function renderGrid() {
            const itemsPerPage = parseInt(itemsPerPageSelect.value);
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedSets = filteredAndSortedSets.slice(start, end);

            grid.innerHTML = ''; // Clear existing grid
            paginatedSets.forEach(set => {
                const card = document.createElement('div');
                card.className = 'set-card';
                // The link now wraps the content inside the card
                card.innerHTML = `
                    <a href="detail_set.php?id=${set.id_set_number}" class="set-card-link">
                        <img src="${set.image_url ? set.image_url : '../assets/images/default_lego.png'}" alt="${set.set_name}" onerror="this.onerror=null;this.src='../assets/images/default_lego.png';">
                        <h4>${set.set_name}</h4>
                        <p><strong>Matricule:</strong> ${set.id_set_number}</p>
                        <p><strong>Année:</strong> ${set.year_released}</p>
                        <p><strong>Pièces:</strong> ${set.number_of_parts}</p>
                        <p><strong>Thème:</strong> ${set.theme_name}</p>
                    </a>
                `;
                grid.appendChild(card);
            });
        }

        function renderPaginationControls() {
            const itemsPerPage = parseInt(itemsPerPageSelect.value);
            const totalPages = Math.ceil(filteredAndSortedSets.length / itemsPerPage);
            paginationControls.innerHTML = ''; // Clear existing controls

            if (totalPages <= 1) return; // No controls if only one page

            // Previous Button
            const prevButton = document.createElement('button');
            prevButton.textContent = 'Précédent';
            prevButton.disabled = currentPage === 1;
            prevButton.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    updateDisplay();
                }
            });
            paginationControls.appendChild(prevButton);

            // Current Page Info / Input
            const pageInfo = document.createElement('label');
            pageInfo.textContent = ` Page ${currentPage} sur ${totalPages} `;
            paginationControls.appendChild(pageInfo);

            // Input for specific page
            const pageInput = document.createElement('input');
            pageInput.type = 'number';
            pageInput.value = currentPage;
            pageInput.min = 1;
            pageInput.max = totalPages;
            pageInput.addEventListener('change', (e) => {
                let newPage = parseInt(e.target.value);
                if (newPage >= 1 && newPage <= totalPages) {
                    currentPage = newPage;
                    updateDisplay();
                } else { // Reset to current if invalid
                    e.target.value = currentPage;
                }
            });
            // paginationControls.appendChild(pageInput);


            // Next Button
            const nextButton = document.createElement('button');
            nextButton.textContent = 'Suivant';
            nextButton.disabled = currentPage === totalPages;
            nextButton.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    updateDisplay();
                }
            });
            paginationControls.appendChild(nextButton);
        }

        function applyFiltersAndSort() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedTheme = themeFilter.value.toLowerCase(); // Ensure this matches option values

            filteredAndSortedSets = allSets.filter(set => {
                const setSearchableText = `${set.set_name} ${set.id_set_number} ${set.year_released}`.toLowerCase();
                return setSearchableText.includes(searchTerm) &&
                       (selectedTheme === '' || set.theme_name.toLowerCase() === selectedTheme);
            });

            const sortValue = sortSelect.value;
            filteredAndSortedSets.sort((a, b) => {
                switch (sortValue) {
                    case 'name-asc': return a.set_name.localeCompare(b.set_name);
                    case 'name-desc': return b.set_name.localeCompare(a.set_name);
                    case 'id-asc': return a.id_set_number.localeCompare(b.id_set_number);
                    case 'id-desc': return b.id_set_number.localeCompare(a.id_set_number);
                    case 'year-asc': return parseInt(a.year_released) - parseInt(b.year_released);
                    case 'year-desc': return parseInt(b.year_released) - parseInt(a.year_released);
                    case 'parts-asc': return parseInt(a.number_of_parts) - parseInt(b.number_of_parts);
                    case 'parts-desc': return parseInt(b.number_of_parts) - parseInt(a.number_of_parts);
                    default: return 0;
                }
            });
        }

        function updateDisplay() {
            applyFiltersAndSort(); // Apply filters and sort first
            const totalPages = Math.ceil(filteredAndSortedSets.length / parseInt(itemsPerPageSelect.value));
            if (currentPage > totalPages && totalPages > 0) { // Adjust current page if it's out of bounds
                currentPage = totalPages;
            } else if (totalPages === 0) {
                currentPage = 1; // Reset to 1 if no results
            }
            renderGrid();         // Then render the grid
            renderPaginationControls(); // And finally, render pagination
        }

        // Event Listeners
        searchInput.addEventListener('input', () => { currentPage = 1; updateDisplay(); });
        themeFilter.addEventListener('change', () => { currentPage = 1; updateDisplay(); });
        sortSelect.addEventListener('change', () => { currentPage = 1; updateDisplay(); }); // Reset to page 1 on sort change
        itemsPerPageSelect.addEventListener('change', () => { currentPage = 1; updateDisplay(); });

        // Initial display
        updateDisplay();
    </script>
</main>
<?php require_once '../includes/footer.php'; ?>
