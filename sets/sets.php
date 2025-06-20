<?php
session_start();
include '../includes/config.php';

$is_connected = isset($_SESSION['username']);
$username = $is_connected ? htmlspecialchars($_SESSION['username']) : null;
$id_user = null;

if ($is_connected) {
    $stmt = $db->prepare("SELECT id FROM SAE203_user WHERE username = :username");
    $stmt->execute(['username' => $_SESSION['username']]);
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
    <title>Catalogue LEGO - Brickothèque</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Animate.css (optionnel) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            background: url('../image/josiel-schoeffel-ezgif-com-gif-maker.gif') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            padding-top: 70px;
            color: #fff;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(255, 255, 255, 0.21);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #fff;
        }

        .navbar-brand span {
            font-weight: bold;
            color: #dc3545;
        }

        /* Controls styling */
        .controls input, .controls select {
            padding: 8px;
            margin-right: 10px;
            border-radius: 5px;
            border: none;
            min-width: 150px;
        }
        .controls input {
            max-width: 250px;
        }

        /* Grid of sets */
        .sets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill,minmax(180px,1fr));
            gap: 20px;
        }

        /* Individual card */
        .set-card {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease;
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            user-select: none;
        }
        .set-card:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255,255,255,0.2);
        }
        .set-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .set-card h4 {
            margin: 10px 0 5px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .set-card p {
            margin: 3px 0;
            font-size: 0.9rem;
        }

        /* Pagination */
        .pagination {
            text-align: center;
            justify-content: center;
        }
        .pagination button {
            padding: 8px 12px;
            margin: 0 3px;
            border: none;
            background-color: rgba(255,255,255,0.15);
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .pagination button:disabled {
            background-color: rgba(255,255,255,0.35);
            cursor: default;
        }
        .pagination button:hover:not(:disabled) {
            background-color: rgba(255,255,255,0.35);
        }
        .pagination input {
            width: 50px;
            text-align: center;
            padding: 6px;
            border-radius: 5px;
            border: none;
            margin-left: 5px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-50 fixed-top shadow-sm glass-card">
    <div class="container">
        <a class="navbar-brand" href="../index.php">
            Brickothèque<?= $username ? " - Bonjour <span>$username</span>" : "" ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavSets">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNavSets">
            <ul class="navbar-nav">
                <?php if ($is_connected): ?>
                    <li class="nav-item"><a class="nav-link" href="../modifier_compte.php">Compte</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../deconnexion.php">Déconnexion</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="../authentification.php">Se connecter</a></li>
                    <li class="nav-item"><a class="nav-link" href="../inscription.php">S'inscrire</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container mt-5">

    <h1 class="mb-4">Catalogue LEGO</h1>

    <?php if ($is_connected): ?>
        <div class="mb-3">
            <a href="./wishlist.php" class="btn btn-outline-light btn-sm me-2">Ma Wishlist</a>
            <a href="./owned.php" class="btn btn-outline-light btn-sm">Mes Sets Possédés</a>
        </div>
        <p>Connecté en tant que : <strong><?= $username ?></strong></p>
    <?php else: ?>
        <p><em>Vous n'êtes pas connecté. Connectez-vous pour ajouter des sets à vos listes.</em></p>
        <a href="../authentification.php" class="btn btn-outline-light btn-sm mb-4">Se connecter</a>
    <?php endif; ?>

    <div class="glass-card p-4">

        <div class="controls mb-3 d-flex flex-wrap align-items-center gap-2">
            <input type="text" id="searchInput" placeholder="Rechercher un set..." class="form-control" style="max-width:250px;">
            <select id="themeFilter" class="form-select" style="max-width:200px;">
                <option value="">-- Filtrer par thème --</option>
                <?php foreach ($themes as $theme): ?>
                    <option value="<?= htmlspecialchars($theme) ?>"><?= htmlspecialchars($theme) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="sortSelect" class="form-select" style="max-width:200px;">
                <option value="name-asc">Nom A-Z</option>
                <option value="name-desc">Nom Z-A</option>
                <option value="id-asc">Matricule croissant</option>
                <option value="id-desc">Matricule décroissant</option>
                <option value="year-desc">Année (récent - ancien)</option>
                <option value="year-asc">Année (ancien - récent)</option>
            </select>
            <select id="itemsPerPage" class="form-select" style="max-width:150px;">
                <option value="25">25 par page</option>
                <option value="50" selected>50 par page</option>
                <option value="75">75 par page</option>
                <option value="100">100 par page</option>
            </select>
        </div>

        <div class="sets-grid" id="setsGrid"></div>
        <div class="pagination mt-4" id="pagination"></div>
    </div>

</main>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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
            div.className = 'set-card';
            div.innerHTML = `
                <a href="detail_set.php?id=${set.id_set_number}" target="_blank" style="text-decoration: none; color: inherit;">
                    <img src="${set.image_url}" alt="${set.set_name}">
                    <h4>${set.set_name}</h4>
                    <p><strong>Matricule:</strong> ${set.id_set_number}</p>
                    <p><strong>Année:</strong> ${set.year_released}</p>
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
        const sort = sortSelect.value;

        filteredSets = allSets.filter(set => {
            return set.set_name.toLowerCase().includes(search) && (theme === '' || set.theme_name.toLowerCase() === theme);
        });

        switch (sort) {
            case 'name-asc':
                filteredSets.sort((a, b) => a.set_name.localeCompare(b.set_name));
                break;
            case 'name-desc':
                filteredSets.sort((a, b) => b.set_name.localeCompare(a.set_name));
                break;
            case 'id-asc':
                filteredSets.sort((a, b) => parseInt(a.id_set_number) - parseInt(b.id_set_number));
                break;
            case 'id-desc':
                filteredSets.sort((a, b) => parseInt(b.id_set_number) - parseInt(a.id_set_number));
                break;
            case 'year-asc':
                filteredSets.sort((a, b) => parseInt(a.year_released) - parseInt(b.year_released));
                break;
            case 'year-desc':
                filteredSets.sort((a, b) => parseInt(b.year_released) - parseInt(a.year_released));
                break;
        }
        currentPage = 1;
        displaySets(filteredSets);
    }

    searchInput.addEventListener('input', updateDisplay);
    themeFilter.addEventListener('change', updateDisplay);
    sortSelect.addEventListener('change', updateDisplay);
    itemsPerPageSelect.addEventListener('change', () => {
        currentPage = 1;
        updateDisplay();
    });

    // Initial display
    updateDisplay();
</script>

</body>
</html>
