<?php
session_start();
if (!isset($_SESSION['users_id'])) {
    header("Location: log.php");
    exit();
}

require 'database.php';

// Vérifier si un ID de voyage est bien fourni
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];
$users_id = $_SESSION['users_id'];

// Récupérer les données existantes du voyage
$stmt = $pdo->prepare("SELECT * FROM voyages WHERE id = ? AND users_id = ?");
$stmt->execute([$id, $users_id]);
$voyage = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$voyage) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les nouvelles données du formulaire avec valeurs par défaut si non remplies
    $destination = !empty($_POST['destination']) ? trim($_POST['destination']) : $voyage['destination'];
    $depart = !empty($_POST['depart']) ? trim($_POST['depart']) : $voyage['depart'];
    $retour = !empty($_POST['retour']) ? trim($_POST['retour']) : $voyage['retour'];
    $budget = !empty($_POST['budget']) ? trim($_POST['budget']) : $voyage['budget'];
    $hotel = !empty($_POST['hotel']) ? trim($_POST['hotel']) : $voyage['hotel'];
    $adresse = !empty($_POST['adresse']) ? trim($_POST['adresse']) : $voyage['adresse'];
    $reservation = !empty($_POST['reservation']) ? trim($_POST['reservation']) : $voyage['reservation'];
    $transport = !empty($_POST['transport']) ? trim($_POST['transport']) : $voyage['transport'];
    $activites = !empty($_POST['activites']) ? trim($_POST['activites']) : $voyage['activites'];
    $documents = !empty($_POST['documents']) ? trim($_POST['documents']) : $voyage['documents'];
    $contacts = !empty($_POST['contacts']) ? trim($_POST['contacts']) : $voyage['contacts'];
    $bagages = !empty($_POST['bagages']) ? trim($_POST['bagages']) : $voyage['bagages'];

    // Vérification des champs obligatoires
    if (!empty($destination) && !empty($depart) && !empty($retour) && !empty($budget) && 
        !empty($hotel) && !empty($adresse) && !empty($reservation) && !empty($transport) && 
        !empty($activites) && !empty($bagages)) {
        // Mise à jour des données
        $update_stmt = $pdo->prepare("
            UPDATE voyages 
            SET destination = ?, depart = ?, retour = ?, budget = ?, hotel = ?, adresse = ?, reservation = ?, 
                transport = ?, activites = ?, documents = ?, contacts = ?, bagages = ? 
            WHERE id = ? AND users_id = ?
        ");
        $update_stmt->execute([$destination, $depart, $retour, $budget, $hotel, $adresse, $reservation, 
                               $transport, $activites, $documents, $contacts, $bagages, $id, $users_id]);

        // Redirection avec message de succès
        header("Location: dashboard.php?success=1");
        exit();
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Voyage - MadaVoyage</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="formulaire.css">
    <style>
        /* Style inspiré de ajoutvoyage.php */
        body {
            background-color: #0D1B2A;
            color: #fff;
            padding: 70px 0;
            font-family: 'Raleway', sans-serif;
        }

        header {
            background-color: #1B263B;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 50px;
            padding: 0 5%;
        }

        header .logo a {
            font-size: 25px;
            color: #29d9d5;
            text-decoration: none;
        }

        header .logo a span {
            color: #fff;
        }

        .menu {
            display: flex;
            align-items: center;
        }

        .menu li {
            margin: 0 15px;
            list-style-type: none;
        }

        .menu li a {
            color: #fff;
            font-size: 14px;
            text-decoration: none;
        }

        .container1 {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #1B263B;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        }

        h1 {
            text-align: center;
            color: #29d9d5;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 14px;
            color: #fff;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            background: #0D1B2A;
            border: 1px solid #2a3752;
            border-radius: 5px;
            color: #fff;
            font-size: 14px;
        }

        input:focus, textarea:focus, select:focus {
            border-color: #29d9d5;
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 200px;
            align-self: center;
        }

        button:hover {
            background-color: #218838;
        }

        .return-btn {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #29d9d5;
            text-decoration: none;
            font-size: 16px;
        }

        .error {
            color: #dc3545;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php"><span>Travels</span> Agency</a>
        </div>
        <ul class="menu">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="#a-propos">À propos</a></li>
            <li><a href="destination.php">Destinations</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
    </header>

    <div class="container1">
        <h1>Modifier votre Voyage</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Destination :</label>
            <input type="text" name="destination" value="<?php echo htmlspecialchars($voyage['destination']); ?>" required>

            <label for="depart">Départ :</label>
            <input type="date" id="depart" name="depart" value="<?php echo htmlspecialchars($voyage['depart']); ?>" required>

            <label for="retour">Retour :</label>
            <input type="date" id="retour" name="retour" value="<?php echo htmlspecialchars($voyage['retour']); ?>" required>

            <label>Budget :</label>
            <input type="number" step="0.01" name="budget" value="<?php echo htmlspecialchars($voyage['budget']); ?>" placeholder="€" required>

            <label>Nom de l’hôtel :</label>
            <input type="text" name="hotel" value="<?php echo htmlspecialchars($voyage['hotel']); ?>" required>

            <label>Adresse :</label>
            <input type="text" name="adresse" value="<?php echo htmlspecialchars($voyage['adresse']); ?>" required>

            <label>Réservation confirmée :</label>
            <select name="reservation" required>
                <option value="Oui" <?php if ($voyage['reservation'] == "Oui") echo "selected"; ?>>Oui</option>
                <option value="Non" <?php if ($voyage['reservation'] == "Non") echo "selected"; ?>>Non</option>
            </select>

            <label>Transport :</label>
            <input type="text" name="transport" value="<?php echo htmlspecialchars($voyage['transport']); ?>" required>

            <label>Activités prévues :</label>
            <textarea name="activites" rows="4" required><?php echo htmlspecialchars($voyage['activites']); ?></textarea>


            <label>Contacts d’urgence :</label>
            <input type="text" name="contacts" value="<?php echo htmlspecialchars($voyage['contacts'] ?? ''); ?>" placeholder="Numéro/Email">

            <label>Liste de bagages :</label>
            <textarea name="bagages" rows="4" required><?php echo htmlspecialchars($voyage['bagages']); ?></textarea>

            <button type="submit">Mettre à jour</button>
        </form>
    </div>
    <a href="dashboard.php" class="return-btn">⬅ Retour</a>

    <script>
        let today = new Date().toISOString().split("T")[0];
        document.getElementById("depart").min = today;
        document.getElementById("depart").addEventListener("change", function() {
            document.getElementById("retour").min = this.value;
        });
    </script>
</body>
</html>