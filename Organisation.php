<?php
session_start();
require 'database.php';

// Récupérer les données passées depuis destination.php via GET
$destination = isset($_GET['destination']) ? htmlspecialchars($_GET['destination']) : '';
$budget = isset($_GET['budget']) ? htmlspecialchars($_GET['budget']) : '';
$hotel = isset($_GET['hotel']) ? htmlspecialchars($_GET['hotel']) : '';
$adresse = isset($_GET['adresse']) ? htmlspecialchars($_GET['adresse']) : '';
$transport = isset($_GET['transport']) ? htmlspecialchars($_GET['transport']) : '';
$activites = isset($_GET['activites']) ? htmlspecialchars($_GET['activites']) : '';

// Stocker les données dans la session pour les réutiliser après une erreur, si nécessaire
$_SESSION['destination_data'] = [
    'destination' => $destination,
    'budget' => $budget,
    'hotel' => $hotel,
    'adresse' => $adresse,
    'transport' => $transport,
    'activites' => $activites
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservations - MadaVoyage</title>
    <link rel="icon" type="image/png" href="logo/mvv.jpg">
    <link rel="stylesheet" href="formulaire.css">
</head>
<body>
    <header>
        <div class="logo">
            <a href="index"><span>Mada</span>Voyage</a>
        </div>
        <ul class="menu">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="about.php">À propos</a></li>
            <li><a href="destination.php">Destinations</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="inscription.php">Inscription</a></li>
            <li><a href="dashboard.php">Mon compte</a></li>
        </ul>
        <div class="responsive-menu"></div>
    </header>

    <div class="container1">
        <?php if (isset($_SESSION['error'])): ?>
            <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <h1>Planification de Voyage</h1>
        <form action="save.php" method="POST">
            <label>Destination :</label>
            <input type="text" name="destination" required value="<?php echo htmlspecialchars($destination); ?>" readonly>

            <label for="depart">Départ :</label>
            <input type="date" id="depart" name="depart" required>

            <label for="retour">Retour :</label>
            <input type="date" id="retour" name="retour" required>

            <label>Budget estimé :</label>
            <input type="number" name="budget" placeholder="MGA" required value="<?php echo htmlspecialchars($budget); ?>" readonly>

            <label>Nom de l’hôtel / logement :</label>
            <input type="text" name="hotel" value="<?php echo htmlspecialchars($hotel); ?>" readonly>

            <label>Adresse :</label>
            <input type="text" name="adresse" value="<?php echo htmlspecialchars($adresse); ?>" readonly>

            <label>Réservation confirmée :</label>
            <select name="reservation">
                <option value="Oui">Oui</option>
                <option value="Non" selected>Non</option>
            </select>

            <label>Transport :</label>
            <input type="text" name="transport" value="<?php echo htmlspecialchars($transport); ?>" readonly>

            <label>Contacts d’urgence :</label>
            <input type="text" name="contacts" placeholder="Numéro/Email">

            <label>Activités prévues :</label>
            <textarea name="activites" rows="4" readonly><?php echo htmlspecialchars($activites); ?></textarea>

            <label>Liste de bagages :</label>
            <textarea name="bagages" rows="4"></textarea>

            <button type="submit">Enregistrer</button>
        </form>
        <a href="dashboard" class="return-btn">Retour au tableau de bord</a>
    </div>

    <script>
        // Validation des dates
        let today = new Date().toISOString().split("T")[0];
        document.getElementById("depart").min = today;

        document.getElementById("depart").addEventListener("change", function() {
            document.getElementById("retour").min = this.value;
        });
    </script>
</body>
</html>