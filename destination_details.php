<?php
session_start();
require 'database.php';

// Vérifier si l'ID de la destination est fourni
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: destination.php");
    exit();
}

$destination_id = $_GET['id'];

// Récupérer les détails de la destination
try {
    $stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
    $stmt->execute([$destination_id]);
    $dest = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dest) {
        header("Location: destination.php");
        exit();
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération des détails : " . $e->getMessage());
}

// Préparer les images
$destination_images = !empty($dest['destination_images']) ? explode(',', $dest['destination_images']) : [];
$hotel_images = !empty($dest['hotel_images']) ? explode(',', $dest['hotel_images']) : [];

// Filtrer les images valides
$destination_images = array_filter($destination_images, function($img) {
    return !empty($img) && file_exists($img);
});
$hotel_images = array_filter($hotel_images, function($img) {
    return !empty($img) && file_exists($img);
});

// Image par défaut si aucune image n'est disponible
$default_image = './images/default.jpg';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($dest['destination'] ?? 'Détails de la Destination'); ?> - MadaVoyage</title>
    <link rel="icon" type="image/png" href="logo/mvv.jpg">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="destination.css">
    <style>
        /* Section principale */
        #destination-details {
            margin-top: 100px;
            margin-bottom: 50px;
            padding: 0 10%;
            width: 100%;
        }

        /* Titre centré et agrandi */
        .title {
            font-size: 40px; /* Agrandi */
            color: #29d9d5;
            text-align: center;
            margin: 70px 0;
            font-weight: bold;
            text-transform: capitalize;
            position: relative;
        }
        .title::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -10px;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background-color: #29d9d5;
        }

        /* Conteneur pour chaque section (destination et hôtel) */
        .section-container {
            display: flex;
            gap: 40px;
            margin-bottom: 50px;
            align-items: flex-start;
        }

        /* Collage d'images carré */
        .collage-wrapper {
            width: 55%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            padding: 10px;
            background: #1B263B;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        }

        .collage-wrapper img {
            width: 100%;
            height: 150px; /* Fixé pour carré */
            aspect-ratio: 1/1; /* Force un ratio carré */
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .collage-wrapper img:hover {
            transform: scale(1.05);
        }

        /* Conteneur pour la description avec scrollbar */
        .description-wrapper {
            width: 40%;
            background: rgba(27, 38, 59, 0.8);
            border-radius: 10px;
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
            color: #ccc;
            font-size: 15px;
            line-height: 1.7;
        }

        /* Scrollbar stylisée */
        .description-wrapper::-webkit-scrollbar {
            width: 8px;
        }
        .description-wrapper::-webkit-scrollbar-thumb {
            background-color: #29d9d5;
            border-radius: 4px;
        }
        .description-wrapper::-webkit-scrollbar-track {
            background: #1B263B;
        }

        /* Section des informations agrandie */
        .info-container {
            max-width: 1000px;
            margin: 0 auto 50px;
            background: #1B263B;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.15);
        }

        .info-container h2 {
            font-size: 28px;
            color: #29d9d5;
            margin-bottom: 20px;
            text-align: center;
        }

        .info-container .info-list {
            font-size: 18px;
            color: #ccc;
        }

        .info-container .info-list li {
            margin-bottom: 15px;
            padding-left: 25px;
        }

        .info-container .info-list li::before {
            font-size: 20px;
        }

        .info-container .info-list strong {
            color: #fff;
            font-size: 18px;
        }

        /* Boutons centrés */
        .button-group {
            text-align: center;
            margin-top: 40px;
        }

        .validate-btn, .return-btn {
            padding: 15px 30px;
            font-size: 16px;
        }

        /* Modal pour afficher les images */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(41, 217, 213, 0.5);
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            color: #fff;
            font-size: 30px;
            cursor: pointer;
            transition: color 0.3s;
        }

        .modal-close:hover {
            color: #29d9d5;
        }

        /* Responsive */
        @media (max-width: 750px) {
            .title {
                font-size: 28px;
            }
            .section-container {
                flex-direction: column;
                gap: 25px;
            }
            .collage-wrapper, .description-wrapper {
                width: 100%;
            }
            .collage-wrapper img {
                height: 120px; /* Carré sur mobile */
            }
            .description-wrapper {
                max-height: 200px;
            }
            .info-container {
                padding: 20px;
            }
            .info-container .info-list {
                font-size: 16px;
            }
            .info-container .info-list strong {
                font-size: 16px;
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
            <li><a href="index.php#contact">Contact</a></li>
            <li><a href="inscription.php">Inscription</a></li>
            <li><a href="dashboard.php">Mon compte</a></li>
        </ul>
        <a href="log.php" class="btn-reservation">Connexion</a>
        <div class="responsive-menu"></div>
    </header>

    <section id="destination-details">
        <h1 class="title"><?php echo htmlspecialchars($dest['destination'] ?? 'Destination'); ?></h1>

        <!-- Section Destination -->
        <div class="section-container">
            <!-- Collage Destination -->
            <div class="collage-wrapper">
                <?php if (!empty($destination_images)): ?>
                    <?php foreach ($destination_images as $img): ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" alt="Image de la destination" onclick="openModal(this.src)">
                    <?php endforeach; ?>
                <?php else: ?>
                    <img src="<?php echo $default_image; ?>" alt="Image par défaut" onclick="openModal(this.src)">
                <?php endif; ?>
            </div>
            <!-- Description Destination -->
            <div class="description-wrapper">
                <h3>Description</h3>
                <p><?php echo htmlspecialchars($dest['description'] ?? 'Aucune description disponible.'); ?></p>
            </div>
        </div>

        <!-- Section Hôtel -->
        <div class="section-container">
            <!-- Collage Hôtel -->
            <div class="collage-wrapper">
                <?php if (!empty($hotel_images)): ?>
                    <?php foreach ($hotel_images as $img): ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" alt="Image de l'hôtel" onclick="openModal(this.src)">
                    <?php endforeach; ?>
                <?php else: ?>
                    <img src="<?php echo $default_image; ?>" alt="Image par défaut" onclick="openModal(this.src)">
                <?php endif; ?>
            </div>
            <!-- Description Hôtel -->
            <div class="description-wrapper">
                <h3>Hôtel : <?php echo htmlspecialchars($dest['hotel'] ?? 'Non spécifié'); ?></h3>
                <p><?php echo htmlspecialchars($dest['hotel_description'] ?? 'Aucune description disponible.'); ?></p>
            </div>
        </div>

        <!-- Section Informations -->
        <div class="info-container">
            <h2>Informations</h2>
            <ul class="info-list">
                <li><strong>Budget estimé :</strong> <?php echo htmlspecialchars($dest['budget'] ?? 'Non spécifié'); ?> MGA</li>
                <li><strong>Localisation :</strong> <?php echo htmlspecialchars($dest['adresse'] ?? 'Non spécifiée'); ?></li>
                <li><strong>Transport :</strong> <?php echo htmlspecialchars($dest['transport'] ?? 'Non spécifié'); ?></li>
                <li><strong>Activités proposées :</strong>
                    <?php
                    $activites = explode(', ', $dest['activites'] ?? '');
                    if (empty($activites[0])) {
                        echo 'Aucune activité spécifiée';
                    } else {
                        echo '<ul>';
                        foreach ($activites as $activite) {
                            if (!empty($activite)) {
                                echo '<li>' . htmlspecialchars($activite) . '</li>';
                            }
                        }
                        echo '</ul>';
                    }
                    ?>
                </li>
            </ul>
        </div>

        <!-- Boutons -->
        <div class="button-group">
            <form action="organisation.php" method="GET" class="destination-form">
                <input type="hidden" name="destination" value="<?php echo htmlspecialchars($dest['destination'] ?? ''); ?>">
                <input type="hidden" name="budget" value="<?php echo htmlspecialchars($dest['budget'] ?? ''); ?>">
                <input type="hidden" name="hotel" value="<?php echo htmlspecialchars($dest['hotel'] ?? ''); ?>">
                <input type="hidden" name="adresse" value="<?php echo htmlspecialchars($dest['adresse'] ?? ''); ?>">
                <input type="hidden" name="transport" value="<?php echo htmlspecialchars($dest['transport'] ?? ''); ?>">
                <input type="hidden" name="activites" value="<?php echo htmlspecialchars($dest['activites'] ?? ''); ?>">
                <button type="submit" class="validate-btn">Valider cette destination</button>
            </form>
            <a href="destination.php" class="return-btn">Retour aux destinations</a>
        </div>
    </section>

    <!-- Modal pour afficher les images -->
    <div class="modal" id="imageModal">
        <span class="modal-close" onclick="closeModal()">×</span>
        <img id="modalImage" src="" alt="Image agrandie">
    </div>

    <footer>
        <p>Réalisé par <span>Nantenaina José</span> | Tous les droits sont réservés.</p>
    </footer>

    <script>
        // Gestion du menu responsive
        const toggleMenu = document.querySelector('.responsive-menu');
        const menu = document.querySelector('.menu');
        toggleMenu.onclick = function() {
            toggleMenu.classList.toggle('active');
            menu.classList.toggle('responsive');
        };

        // Gestion du modal pour les images
        function openModal(src) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modalImage.src = src;
            modal.style.display = 'flex';
        }

        function closeModal() {
            const modal = document.getElementById('imageModal');
            modal.style.display = 'none';
        }

        // Fermer le modal en cliquant à l'extérieur de l'image
        document.getElementById('imageModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });

        // Confirmation de soumission du formulaire
        document.querySelector('.destination-form').addEventListener('submit', function(event) {
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
    </script>
</body>
</html>