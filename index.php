<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MadaVoyage</title>
    <link rel="icon" type="image/png" href="logo/mvv.jpg">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php"><span>Mada</span>Voyage</a>
        </div>
        <ul class="menu">
            <li><a href="#">Accueil</a></li>
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

    <section id="home">
        <h2>Nous suivre</h2>
        <h4>Voyagez en toute sérénité</h4>
        <p>Avec notre équipe locale passionnée et des services personnalisés !</p>
        <p>Contactez-nous dès aujourd’hui et vivez une expérience inoubliable !</p>
        <?php if (isset($_SESSION['users_id'])): ?>
            <a href="dashboard.php" class="btn-reservation">Mon compte</a>
        <?php else: ?>
            <a href="log.php" class="btn-reservation home-btn">Connexion</a>
        <?php endif; ?>
    </section>

    <section id="a-propos">
        <h1 class="title">À propos</h1>
        <div class="img-desc">
            <div class="left">
                <video src="images/video.mp4" autoplay loop muted></video>
            </div>
            <div class="right">
                <h3>Qui sommes-nous ?</h3>
                <p>MadaVoyage est une agence de voyage passionnée par l'exploration et la découverte. Depuis notre création, nous nous engageons à offrir des expériences de voyage uniques, personnalisées et mémorables à nos clients. Que vous rêviez d'une escapade romantique à Paris, d'une aventure en pleine nature ou d'une immersion culturelle à Tokyo, nous sommes là pour transformer vos rêves en réalité...</p>
                <a href="about.php">Lire Plus</a>
            </div>
        </div>
    </section>

    <section id="Monuments">
        <h1 class="title">Monuments</h1>
        <div class="carousel-container">
            <div class="carousel" id="carousel">
                <div class="carousel-slide">
                    <div class="box">
                        <img src="images/dest_68087a616cbf7.jpg" alt="Palais de la Reine">
                        <div class="content">
                            <div class="paragraph-color">
                                <h4>Palais de la Reine</h4>
                                <p>Ce palais symbolique, situé sur la colline d’Analamanga, était la résidence des souverains Merina.</p>
                                <p>Il incarne la monarchie malgache et l’histoire politique du pays. Le Rova a été reconstruit après l’incendie de 1995.</p>
                            </div>
                        </div>
                    </div>
                    <div class="box">
                        <img src="images/fort manda.jpg" alt="Fort Manda">
                        <div class="content">
                            <div>
                                <h4>Fort Manda</h4>
                                <p>Ancienne forteresse militaire construite au XIXe siècle par les Sakalava pour se défendre contre les envahisseurs.</p>
                                <p>Fait de corail, de sable et d’huile, c’est un exemple rare d’architecture militaire traditionnelle.</p>
                            </div>
                        </div>
                    </div>
                    <div class="box">
                        <img src="images/tembeau.jfif" alt="Tombeau de Rainiharo">
                        <div class="content">
                            <div>
                                <h4>Tombeau de Rainiharo</h4>
                                <p>Ce mausolée de style colonial a été érigé en l'honneur de Rainiharo, Premier ministre de la reine Ranavalona I.</p>
                                <p>Mélange d’architecture occidentale et malgache, il est riche en symboles royaux.</p>
                            </div>
                        </div>
                    </div>
                    <div class="box">
                        <img src="images/Soarano_train_station_(6736335119).jpg" alt="La Gare Soarano">
                        <div class="content">
                            <div>
                                <h4>La Gare Soarano</h4>
                                <p>Ancienne gare ferroviaire construite en 1913 durant la colonisation française.</p>
                                <p>Elle symbolise l’arrivée du chemin de fer à Madagascar. Aujourd’hui réhabilitée, elle abrite des bureaux et des commerces.</p>
                            </div>
                        </div>
                    </div>
                    <div class="box">
                        <img src="images/ange.jpg" alt="Monument aux Morts">
                        <div class="content">
                            <div>
                                <h4>Monument aux Morts</h4>
                                <p>Érigé sur l’île du lac Anosy en mémoire des soldats malgaches morts pendant la Première Guerre mondiale.</p>
                                <p>Ce monument est un symbole de sacrifice et de patriotisme.</p>
                            </div>
                        </div>
                    </div>
                    <div class="box">
                        <img src="images/cathedrale-vue-generale.jpg" alt="Cathédrale d’Andohalo">
                        <div class="content">
                            <div>
                                <h4>Cathédrale d’Andohalo</h4>
                                <p>Lieu de culte catholique historique situé sur la haute ville.</p>
                                <p>Elle domine la ville basse et reflète l’architecture gothique coloniale.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-slide">
                    <div class="box">
                        <img src="Images/Ambohimanga-Rova-celebre-son-inscription-sur-la-liste-de-l-Unesco-700x525.jpg" alt="Rova Ambohimanga">
                        <div class="content">
                            <div>
                                <h4>Rova Ambohimanga</h4>
                                <p>Site sacré classé au patrimoine mondial de l’UNESCO, ancienne capitale royale.</p>
                                <p>Entouré de murs en pierre sèche et d’un portail de pierre légendaire.</p>
                            </div>
                        </div>
                    </div>
                    <div class="box">
                        <img src="Images/Ambatomanga.jpg" alt="Temple protestant Ambatonakanga">
                        <div class="content">
                            <div>
                                <h4>Temple protestant Ambatonakanga</h4>
                                <p>Premier temple protestant de Madagascar, construit en 1863.</p>
                                <p>Important pour l’histoire de la christianisation du pays.</p>
                            </div>
                        </div>
                    </div>
                    <div class="box">
                        <img src="Images/Memorial.jpg" alt="Le Mémorial Indépendance">
                        <div class="content">
                            <div>
                                <h4>Le Mémorial Indépendance</h4>
                                <p>Monument moderne commémorant l’indépendance acquise le 26 juin 1960.</p>
                                <p>Il surplombe la ville avec des plaques historiques.</p>
                            </div>
                        </div>
                    </div>
                    <div class="box">
                        <img src="Images/Tombeau.jpg" alt="Mausolée d’Andrianampoinimerina">
                        <div class="content">
                            <div>
                                <h4>Mausolée Andrianampoinimerina</h4>
                                <p>Tombe du roi qui a unifié les hautes terres.</p>
                                <p>Le bâtiment en bois et chaume reflète l’architecture Merina traditionnelle.</p>
                            </div>
                        </div>
                    </div>
                    <div class="box">
                        <img src="Images/Mosque.jpg" alt="Mosquée Zoma (Mosquée d’Analakely)">
                        <div class="content">
                            <div>
                                <h4>Mosquée Zoma (Mosquée d’Analakely)</h4>
                                <p>Une des plus anciennes mosquées de la capitale, située dans le quartier d’Analakely.</p>
                                <p>Elle témoigne de la diversité religieuse malgache.</p>
                            </div>
                        </div>
                    </div>
                    <div class="box">
                        <img src="Images/Antsirabe.jpg" alt="Le Colonne de la Place de l’Indépendance (Antsirabe)">
                        <div class="content">
                            <div>
                                <h4>Le Colonne de la Place de l’Indépendance (Antsirabe)</h4>
                                <p>Grande colonne blanche érigée au cœur de la ville thermale,</p>
                                <p>symbolisant la souveraineté et la fierté de la région Vakinankaratra.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-buttons">
                <button class="carousel-btn" id="prevBtn">← Précédent</button>
                <button class="carousel-btn" id="nextBtn">Suivant →</button>
            </div>
        </div>
    </section>

    <section id="contact">
        <h1 class="title">Contact</h1>
        <div class="social-media">
            <a href="https://facebook.com" target="_blank" title="Suivez-nous sur Facebook">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://twitter.com" target="_blank" title="Suivez-nous sur Twitter">
                <i class="fab fa-twitter"></i>
            </a>
            <a href="https://instagram.com" target="_blank" title="Suivez-nous sur Instagram">
                <i class="fab fa-instagram"></i>
            </a>
            <a href="https://linkedin.com" target="_blank" title="Suivez-nous sur LinkedIn">
                <i class="fab fa-linkedin-in"></i>
            </a>
        </div>
        <p class="site-description">Découvrez les meilleures destinations avec MadaVoyage, votre partenaire pour des voyages inoubliables !</p>
        <div class="contact-texts">
        <hr class="separator-line" />
    <div class="left-texts">
      <p>Notre agence est spécialisée dans les voyages ferroviaires à travers Madagascar.</p>
      <p>Nous vous garantissons des expériences authentiques et inoubliables.</p>
      <p>Notre priorité est votre confort et votre sécurité durant le voyage.</p>
      <p>Réservez facilement et rapidement via notre plateforme intuitive.</p>
    </div>
    <div class="right-texts">
      <p>Des offres personnalisées sont disponibles pour tous les budgets.</p>
      <p>Une équipe à l’écoute et disponible 7j/7 pour vous accompagner.</p>
      <p>Voyager avec nous, c’est redécouvrir Madagascar autrement.</p>
    </div>
  </div>
  <div class="founders">
    <p>Fondateurs : <strong>RABEHARIMANANTSOA Nantenaina José</strong> &amp; <strong>AINANAROVANIAVO Narojo Fitiavana Natacha</strong></p>
  </div>
       
</section>

    <footer>
        <p>Réalisé par <span>Nantenaina José</span> | Tous les droits sont réservés.</p>
    </footer>

    <script>
        const toggleMenu = document.querySelector('.responsive-menu');
        const menu = document.querySelector('.menu');
        toggleMenu.onclick = function() {
            toggleMenu.classList.toggle('active');
            menu.classList.toggle('responsive');
        };

        const carousel = document.getElementById('carousel');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const slides = document.querySelectorAll('.carousel-slide');
        let currentIndex = 0;

        function updateCarousel() {
            const slideWidth = slides[0].offsetWidth;
            carousel.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
            prevBtn.disabled = currentIndex === 0;
            nextBtn.disabled = currentIndex === slides.length - 1;
        }

        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                updateCarousel();
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentIndex < slides.length - 1) {
                currentIndex++;
                updateCarousel();
            }
        });

        window.addEventListener('resize', updateCarousel);
        updateCarousel();
    </script>
</body>
</html>