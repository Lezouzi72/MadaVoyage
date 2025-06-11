<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos - MadaVoyage</title>
    <link rel="icon" type="image/png" href="logo/mvv.jpg">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="about.css">
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

    <section id="a-propos">
        <h1 class="title">À propos de nous</h1>
        <div class="img-desc">
            <div class="left">
                <img src="images/sarety.jpg" alt="Paysage de Madagascar">
            </div>
            <div class="right">
                <h3>Explorez Madagascar avec MadaVoyage</h3>
                <p>MadaVoyage est votre partenaire idéal pour découvrir les merveilles de Madagascar. Que vous soyez amateur de plages paradisiaques, de forêts tropicales ou de cultures uniques, nous vous offrons des expériences sur mesure.</p>
                <p>Notre mission est de rendre votre voyage inoubliable en vous proposant des destinations authentiques, des hébergements de qualité et des activités qui respectent l'environnement et les communautés locales.</p>
                <a href="destination.php" class="btn">Découvrir nos destinations</a>
            </div>
        </div>

        <div class="img-desc reverse">
            <div class="left">
                <img src="images/heureux-divers-peuples-tenant-un-tableau-de-conception-wed.jpg" alt="Culture Malagasy">
            </div>
            <div class="right">
                <h3>Une équipe passionnée</h3>
                <p>Fondée par des amoureux de Madagascar, notre équipe est composée d'experts locaux qui connaissent chaque recoin de l'île. Nous travaillons avec des guides expérimentés et des partenaires de confiance pour garantir votre sécurité et votre satisfaction.</p>
                <p>Chez MadaVoyage, nous croyons en un tourisme responsable qui soutient l'économie locale et préserve la richesse naturelle et culturelle de Madagascar.</p>
                <a href="contact.php" class="btn">Contactez-nous</a>
            </div>
        </div>
    </section>

    <footer>
        <p>Réalisé par <span>Nantenaina José</span> | Tous les droits sont réservés.</p>
    </footer>
</body>
</html>