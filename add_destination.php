<?php
session_start();
require 'database.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['users_id'])) {
    header("Location: log.php");
    exit();
}

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['users_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Créer le dossier images/ s'il n'existe pas
$upload_dir = './images/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
    chmod($upload_dir, 0755);
}

// Traitement du formulaire
$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $destination = filter_var($_POST['destination'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $budget = filter_var($_POST['budget'], FILTER_VALIDATE_FLOAT);
    $hotel = filter_var($_POST['hotel'], FILTER_SANITIZE_STRING);
    $adresse = filter_var($_POST['adresse'], FILTER_SANITIZE_STRING);
    $transport = filter_var($_POST['transport'], FILTER_SANITIZE_STRING);
    $activites = filter_var($_POST['activites'], FILTER_SANITIZE_STRING);
    $hotel_description = filter_var($_POST['hotel_description'] ?? '', FILTER_SANITIZE_STRING);

    // Gestion de l'image principale
    $main_image = '';
    if (!empty($_FILES['main_image']['name'])) {
        $file = $_FILES['main_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed_types) && $file['size'] <= 5 * 1024 * 1024) {
            $filename = uniqid('main_') . '.' . $ext;
            $dest_path = $upload_dir . $filename;
            if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                $main_image = './images/' . $filename;
            } else {
                $errors[] = "Erreur lors du téléchargement de l'image principale.";
            }
        } else {
            $errors[] = "Image principale invalide (format : jpg, jpeg, png, gif ; taille max : 5MB).";
        }
    }

    // Gestion des images de la destination
    $destination_images = [];
    if (!empty($_FILES['destination_images']['name'][0])) {
        foreach ($_FILES['destination_images']['name'] as $key => $name) {
            if ($_FILES['destination_images']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) && $_FILES['destination_images']['size'][$key] <= 5 * 1024 * 1024) {
                    $filename = uniqid('dest_') . '.' . $ext;
                    $dest_path = $upload_dir . $filename;
                    if (move_uploaded_file($_FILES['destination_images']['tmp_name'][$key], $dest_path)) {
                        $destination_images[] = './images/' . $filename;
                    } else {
                        $errors[] = "Erreur lors du téléchargement de l'image de destination : $name.";
                    }
                } else {
                    $errors[] = "Image de destination invalide : $name (format ou taille).";
                }
            }
        }
    }
    $destination_images_str = implode(',', $destination_images);

    // Gestion des images de l'hôtel
    $hotel_images = [];
    if (!empty($_FILES['hotel_images']['name'][0])) {
        foreach ($_FILES['hotel_images']['name'] as $key => $name) {
            if ($_FILES['hotel_images']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) && $_FILES['hotel_images']['size'][$key] <= 5 * 1024 * 1024) {
                    $filename = uniqid('hotel_') . '.' . $ext;
                    $dest_path = $upload_dir . $filename;
                    if (move_uploaded_file($_FILES['hotel_images']['tmp_name'][$key], $dest_path)) {
                        $hotel_images[] = './images/' . $filename;
                    } else {
                        $errors[] = "Erreur lors du téléchargement de l'image de l'hôtel : $name.";
                    }
                } else {
                    $errors[] = "Image de l'hôtel invalide : $name (format ou taille).";
                }
            }
        }
    }
    $hotel_images_str = implode(',', $hotel_images);

    // Insérer dans la base de données si aucune erreur critique
    if (empty($errors) && $destination && $budget !== false) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO destinations (destination, description, budget, hotel, adresse, transport, activites, image, destination_images, hotel_images, hotel_description, users_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $destination,
                $description,
                $budget,
                $hotel,
                $adresse,
                $transport,
                $activites,
                $main_image,
                $destination_images_str,
                $hotel_images_str,
                $hotel_description,
                $_SESSION['users_id']
            ]);

            $destination_id = $pdo->lastInsertId();

            $template = <<<EOD
<?php
header("Location: destination_details.php?id=$destination_id");
exit();
?>
EOD;
            file_put_contents("destination_details_$destination_id.php", $template);

            header("Location: admin.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Destination - MadaVoyage</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
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
            <li><a href="#contact">Contact</a></li>
            <li><a href="inscription.php">Inscription</a></li>
            <li><a href="dashboard.php">Mon compte</a></li>
        </ul>
        <?php if (isset($_SESSION['users_id'])): ?>
            <a href="dashboard.php" class="btn-reservation">Mon compte</a>
        <?php else: ?>
            <a href="log.php" class="btn-reservation">Connexion</a>
        <?php endif; ?>
        <div class="responsive-menu"></div>
    </header>

    <div class="container">
        <h1>Ajouter une nouvelle destination</h1>
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form action="add_destination.php" method="POST" class="edit-form" enctype="multipart/form-data">
            <label>Destination :</label>
            <input type="text" name="destination" required>
            <label>Description :</label>
            <textarea name="description" rows="5"></textarea>
            <label>Budget (MGA) :</label>
            <input type="number" name="budget" required>
            <label>Hôtel :</label>
            <input type="text" name="hotel">
            <label>Description de l'Hôtel :</label>
            <textarea name="hotel_description" rows="5"></textarea>
            <label>Localisation :</label>
            <input type="text" name="adresse">
            <label>Transport :</label>
            <textarea name="transport" rows="5"></textarea>
            <label>Activités :</label>
            <textarea name="activites" rows="5"></textarea>
            <label>Image Principale (jpg, jpeg, png, gif ; max 5MB) :</label>
            <input type="file" name="main_image" accept="image/jpeg,image/png,image/gif">
            <label>Images de la Destination (plusieurs fichiers possibles) :</label>
            <input type="file" name="destination_images[]" multiple accept="image/jpeg,image/png,image/gif">
            <label>Images de l'Hôtel (plusieurs fichiers possibles) :</label>
            <input type="file" name="hotel_images[]" multiple accept="image/jpeg,image/png,image/gif">
            <button type="submit">Ajouter</button>
        </form>
        <a href="admin.php">Retour à la gestion des destinations</a>
    </div>

    <footer>
        <p>Réalisé par <span>Nantenaina José</span> | Tous les droits sont réservés.</p>
    </footer>
</body>
</html>