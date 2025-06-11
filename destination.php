<?php
session_start();
require 'database.php';

// Récupérer toutes les destinations depuis la base de données
try {
    $stmt = $pdo->query("SELECT * FROM destinations");
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des destinations : " . $e->getMessage());
}

// Diviser les destinations en groupes de 6 pour le carrousel
$slides = array_chunk($destinations, 6);

// Fonction pour tronquer la description
function truncateDescription($text, $maxLength = 80) {
    if (strlen($text) > $maxLength) {
        return substr($text, 0, $maxLength) . '...';
    }
    return $text;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinations - MadaVoyage</title>
    <link rel="icon" type="image/png" href="logo/mvv.jpg">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="destination.css">
    <style>
        .search-container {
            max-width: 1000px;
            margin: 20px auto;
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        .search-container input[type="text"],
        .search-container input[type="number"] {
            padding: 10px;
            border: 1px solid #29d9d5;
            border-radius: 5px;
            background: #1B263B;
            color: #fff;
            font-size: 14px;
            width: 100%;
            max-width: 300px;
            transition: border-color 0.3s ease;
        }

        .search-container input:focus {
            outline: none;
            border-color: #26c1be;
        }

        .search-container button {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .search-container button:hover {
            background-color: #218838;
        }

        .search-container select {
            padding: 10px;
            border: 1px solid #29d9d5;
            border-radius: 5px;
            background: #1B263B;
            color: #fff;
            font-size: 14px;
            cursor: pointer;
        }

        .search-results {
            max-width: 1000px;
            margin: 20px auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .search-results .no-results {
            text-align: center;
            color: #999;
            font-size: 16px;
        }

        @media (max-width: 750px) {
            .search-container {
                flex-direction: column;
                gap: 15px;
            }

            .search-container input[type="text"],
            .search-container input[type="number"],
            .search-container select {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php"><span>Mada</span>Voyage</a>
        </div>
        <ul class="menu">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="about.php">À propos</a></li>
            <li><a href="destination.php">Destinations</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
        <?php if (isset($_SESSION['users_id'])): ?>
            <a href="dashboard.php" class="btn-reservation">Mon compte</a>
        <?php else: ?>
            <a href="log.php" class="btn-reservation">Connexion</a>
        <?php endif; ?>
        <div class="responsive-menu"></div>
    </header>

    <section id="destinations">
        <div class="search-container">
            <select id="searchType">
                <option value="name">Nom de la destination</option>
                <option value="budget">Budget</option>
            </select>
            <input type="text" id="searchInput" placeholder="Rechercher par nom..." style="display: block;">
            <input type="number" id="budgetInput" placeholder="Budget max (MGA)" style="display: none;">
            <button onclick="searchDestinations()">Rechercher</button>
        </div>
        <div class="search-results" id="searchResults"></div>
        <div class="carousel-container">
            <div class="carousel" id="carousel">
                <?php if (empty($destinations)): ?>
                    <div class="carousel-slide">
                        <p style="text-align: center; width: 100%;">Aucune destination disponible pour le moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($slides as $slide): ?>
                        <div class="carousel-slide">
                            <?php foreach ($slide as $dest): ?>
                                <a href="destination_details.php?id=<?php echo $dest['id']; ?>" class="destination-link">
                                    <div class="destination-box" data-name="<?php echo htmlspecialchars(strtolower($dest['destination'])); ?>" data-budget="<?php echo $dest['budget']; ?>">
                                        <img src="<?php echo htmlspecialchars($dest['image'] && file_exists($dest['image']) ? $dest['image'] : './images/default.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($dest['destination'] ?? 'Destination sans nom'); ?>">
                                        <div class="destination-details">
                                            <div>
                                                <h2><?php echo htmlspecialchars($dest['destination'] ?? 'Nom non spécifié'); ?></h2>
                                                <p><strong>Description :</strong> <?php echo htmlspecialchars(truncateDescription($dest['description'] ?? 'Aucune description')); ?></p>
                                                <p><strong>Budget estimé :</strong> <?php echo htmlspecialchars($dest['budget'] ?? 'Non spécifié'); ?> MGA</p>
                                                <p><strong>Suggestions de logements :</strong> <?php echo htmlspecialchars($dest['hotel'] ?? 'Non spécifié'); ?></p>
                                                <p><strong>Localisation :</strong> <?php echo htmlspecialchars($dest['adresse'] ?? 'Non spécifiée'); ?></p>
                                                <p><strong>Transport :</strong> <?php echo htmlspecialchars($dest['transport'] ?? 'Non spécifié'); ?></p>
                                            </div>
                                            <form action="organisation.php" method="GET" class="destination-form">
                                                <input type="hidden" name="destination" value="<?php echo htmlspecialchars($dest['destination'] ?? ''); ?>">
                                                <input type="hidden" name="budget" value="<?php echo htmlspecialchars($dest['budget'] ?? ''); ?>">
                                                <input type="hidden" name="hotel" value="<?php echo htmlspecialchars($dest['hotel'] ?? ''); ?>">
                                                <input type="hidden" name="adresse" value="<?php echo htmlspecialchars($dest['adresse'] ?? ''); ?>">
                                                <input type="hidden" name="transport" value="<?php echo htmlspecialchars($dest['transport'] ?? ''); ?>">
                                                <input type="hidden" name="activites" value="<?php echo htmlspecialchars($dest['activites'] ?? ''); ?>">
                                                <button type="submit">Valider</button>
                                            </form>
                                        </div>
                                    </div>
                                </a>
                                <hr>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="carousel-buttons">
                <button class="carousel-btn" id="prevBtn">← Précédent</button>
                <button class="carousel-btn" id="nextBtn">Suivant →</button>
            </div>
        </div>
    </section>

    <footer>
        <p>Réalisé par <span>Nantenaina José</span> | Tous les droits sont réservés.</p>
    </footer>

    <script>
        const carousel = document.getElementById('carousel');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const slides = document.querySelectorAll('.carousel-slide');
        let currentIndex = 0;

        function updateCarousel() {
            const slideWidth = carousel.offsetWidth;
            carousel.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
            prevBtn.disabled = currentIndex === 0;
            nextBtn.disabled = currentIndex === slides.length - 1 || slides.length <= 1;
        }

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                updateCarousel();
                scrollToTop();
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentIndex < slides.length - 1) {
                currentIndex++;
                updateCarousel();
                scrollToTop();
            }
        });

        window.addEventListener('resize', updateCarousel);
        updateCarousel();

        // Search functionality
        const searchType = document.getElementById('searchType');
        const searchInput = document.getElementById('searchInput');
        const budgetInput = document.getElementById('budgetInput');
        const searchResults = document.getElementById('searchResults');
        const destinationBoxes = document.querySelectorAll('.destination-box');

        searchType.addEventListener('change', () => {
            if (searchType.value === 'name') {
                searchInput.style.display = 'block';
                budgetInput.style.display = 'none';
                searchInput.value = '';
                budgetInput.value = '';
            } else {
                searchInput.style.display = 'none';
                budgetInput.style.display = 'block';
                searchInput.value = '';
                budgetInput.value = '';
            }
            searchDestinations();
        });

        function searchDestinations() {
            const searchValue = searchInput.value.toLowerCase().trim();
            const budgetValue = parseFloat(budgetInput.value);
            let resultsHTML = '';

            destinationBoxes.forEach(box => {
                const name = box.getAttribute('data-name');
                const budget = parseFloat(box.getAttribute('data-budget'));
                let show = false;

                if (searchType.value === 'name') {
                    show = searchValue ? name.includes(searchValue) : true;
                } else {
                    show = budgetValue ? budget <= budgetValue : true;
                }

                if (show) {
                    resultsHTML += box.parentElement.outerHTML + '<hr>';
                }
            });

            if (resultsHTML === '') {
                resultsHTML = '<p class="no-results">Aucune destination ne correspond à votre recherche.</p>';
            }

            searchResults.innerHTML = resultsHTML;

            // Reattach event listeners to the new forms in search results
            document.querySelectorAll('.search-results .destination-form').forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const destination = this.querySelector('input[name="destination"]').value;
                    const budget = this.querySelector('input[name="budget"]').value;
                    const hotel = this.querySelector('input[name="hotel"]').value;
                    const adresse = this.querySelector('input[name="adresse"]').value;
                    const transport = this.querySelector('input[name="transport"]').value;
                    const activites = this.querySelector('input[name="activites"]').value;

                    const confirmationMessage = `Confirmez-vous la sélection de cette destination ?\n\n` +
                        `Destination : ${destination}\n` +
                        `Budget estimé : ${budget} MGA\n` +
                        `Hôtel/Logement : ${hotel}\n` +
                        `Adresse : ${adresse}\n` +
                        `Transport : ${transport}\n` +
                        `Activités prévues : ${activites}`;

                    if (confirm(confirmationMessage)) {
                        this.submit();
                    }
                });
            });
        }

        searchInput.addEventListener('input', searchDestinations);
        budgetInput.addEventListener('input', searchDestinations);

        // Initial display of all destinations in search results
        searchDestinations();

        document.querySelectorAll('.carousel .destination-form').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const destination = this.querySelector('input[name="destination"]').value;
                const budget = this.querySelector('input[name="budget"]').value;
                const hotel = this.querySelector('input[name="hotel"]').value;
                const adresse = this.querySelector('input[name="adresse"]').value;
                const transport = this.querySelector('input[name="transport"]').value;
                const activites = this.querySelector('input[name="activites"]').value;

                const confirmationMessage = `Confirmez-vous la sélection de cette destination ?\n\n` +
                    `Destination : ${destination}\n` +
                    `Budget estimé : ${budget} MGA\n` +
                    `Hôtel/Logement : ${hotel}\n` +
                    `Adresse : ${adresse}\n` +
                    `Transport : ${transport}\n` +
                    `Activités prévues : ${activites}`;

                if (confirm(confirmationMessage)) {
                    this.submit();
                }
            });
        });
    </script>
</body>
</html>