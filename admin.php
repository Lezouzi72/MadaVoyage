<?php
session_start();
require 'database.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
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

// Récupérer toutes les destinations
$stmt = $pdo->query("SELECT * FROM destinations ORDER BY id");
$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les contacts et messages
$stmt = $pdo->query("
    SELECT c.id, c.name, c.email, c.subject, c.message as contact_message, c.created_at, c.is_read, m.id as message_id, m.message, m.sender, m.created_at as message_created_at, m.is_read as message_is_read
    FROM contacts c
    LEFT JOIN messages m ON c.id = m.contact_id
    ORDER BY c.created_at DESC, m.created_at DESC
");
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle admin message response
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message_submit'])) {
    $message = htmlspecialchars($_POST['new_message']);
    $contact_id = filter_var($_POST['contact_id'], FILTER_VALIDATE_INT);

    if ($message && $contact_id) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (contact_id, user_id, message, sender) VALUES (?, ?, ?, 'admin')");
            $stmt->execute([$contact_id, null, $message]);
            $message_success = true;
        } catch (PDOException $e) {
            $message_error = "Erreur lors de l'envoi du message : " . $e->getMessage();
        }
    }
}

// Créer le dossier images/ s'il n'existe pas
$upload_dir = './images/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
    chmod($upload_dir, 0755);
}

// Définir les types de fichiers autorisés
$allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

// Traitement des actions pour les destinations
$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $destination = filter_var($_POST['destination'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $budget = filter_var($_POST['budget'], FILTER_VALIDATE_FLOAT);
    $hotel = filter_var($_POST['hotel'], FILTER_SANITIZE_STRING);
    $adresse = filter_var($_POST['adresse'], FILTER_SANITIZE_STRING);
    $transport = filter_var($_POST['transport'], FILTER_SANITIZE_STRING);
    $activites = filter_var($_POST['activites'], FILTER_SANITIZE_STRING);
    $hotel_description = filter_var($_POST['hotel_description'], FILTER_SANITIZE_STRING);

    // Gestion des images lors de la mise à jour
    $main_image = $_POST['existing_image'] ?? '';
    $existing_destination_images = !empty($_POST['existing_destination_images']) ? explode(',', $_POST['existing_destination_images']) : [];
    $existing_hotel_images = !empty($_POST['existing_hotel_images']) ? explode(',', $_POST['existing_hotel_images']) : [];

    // Upload nouvelle image principale
    if (!empty($_FILES['main_image']['name'])) {
        $file = $_FILES['main_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
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

    // Upload nouvelles images de destination
    $new_destination_images = [];
    if (!empty($_FILES['destination_images']['name'][0])) {
        foreach ($_FILES['destination_images']['name'] as $key => $name) {
            if ($_FILES['destination_images']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed_types) && $_FILES['destination_images']['size'][$key] <= 5 * 1024 * 1024) {
                    $filename = uniqid('dest_') . '.' . $ext;
                    $dest_path = $upload_dir . $filename;
                    if (move_uploaded_file($_FILES['destination_images']['tmp_name'][$key], $dest_path)) {
                        $new_destination_images[] = './images/' . $filename;
                    } else {
                        $errors[] = "Erreur lors du téléchargement de l'image de destination : $name.";
                    }
                } else {
                    $errors[] = "Image de destination invalide : $name (format ou taille).";
                }
            }
        }
    }
    // Fusionner les images existantes et nouvelles
    $destination_images = array_merge($existing_destination_images, $new_destination_images);
    $destination_images_str = implode(',', $destination_images);

    // Upload nouvelles images d'hôtel
    $new_hotel_images = [];
    if (!empty($_FILES['hotel_images']['name'][0])) {
        foreach ($_FILES['hotel_images']['name'] as $key => $name) {
            if ($_FILES['hotel_images']['error'][$key] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed_types) && $_FILES['hotel_images']['size'][$key] <= 5 * 1024 * 1024) {
                    $filename = uniqid('hotel_') . '.' . $ext;
                    $dest_path = $upload_dir . $filename;
                    if (move_uploaded_file($_FILES['hotel_images']['tmp_name'][$key], $dest_path)) {
                        $new_hotel_images[] = './images/' . $filename;
                    } else {
                        $errors[] = "Erreur lors du téléchargement de l'image de l'hôtel : $name.";
                    }
                } else {
                    $errors[] = "Image de l'hôtel invalide : $name (format ou taille).";
                }
            }
        }
    }
    // Fusionner les images existantes et nouvelles
    $hotel_images = array_merge($existing_hotel_images, $new_hotel_images);
    $hotel_images_str = implode(',', $hotel_images);

    if ($id && $destination && $budget !== false && empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE destinations 
                SET destination = ?, description = ?, budget = ?, hotel = ?, adresse = ?, transport = ?, activites = ?, image = ?, 
                    destination_images = ?, hotel_images = ?, hotel_description = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $destination, $description, $budget, $hotel, $adresse, $transport, $activites, $main_image,
                $destination_images_str, $hotel_images_str, $hotel_description, $id
            ]);
            header("Location: admin.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM destinations WHERE id = ?");
        $stmt->execute([$id]);
        if (file_exists("destination_details_$id.php")) {
            unlink("destination_details_$id.php");
        }
        header("Location: admin.php");
        exit();
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_destination'])) {
    $destination = filter_var($_POST['destination'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $budget = filter_var($_POST['budget'], FILTER_VALIDATE_FLOAT);
    $hotel = filter_var($_POST['hotel'], FILTER_SANITIZE_STRING);
    $adresse = filter_var($_POST['adresse'], FILTER_SANITIZE_STRING);
    $transport = filter_var($_POST['transport'], FILTER_SANITIZE_STRING);
    $activites = filter_var($_POST['activites'], FILTER_SANITIZE_STRING);
    $hotel_description = filter_var($_POST['hotel_description'], FILTER_SANITIZE_STRING);

    // Gestion de l'image principale
    $main_image = '';
    if (!empty($_FILES['main_image']['name'])) {
        $file = $_FILES['main_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
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
                if (in_array($ext, $allowed_types) && $_FILES['destination_images']['size'][$key] <= 5 * 1024 * 1024) {
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
                if (in_array($ext, $allowed_types) && $_FILES['hotel_images']['size'][$key] <= 5 * 1024 * 1024) {
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

    if ($destination && $budget !== false && empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO destinations (destination, description, budget, hotel, adresse, transport, activites, image, 
                    destination_images, hotel_images, hotel_description, users_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $destination, $description, $budget, $hotel, $adresse, $transport, $activites, $main_image,
                $destination_images_str, $hotel_images_str, $hotel_description, $_SESSION['users_id']
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
    <title>Admin - Gestion des Destinations</title>
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
            <li><a href="contact.php">Contact</a></li>
        </ul>
        <?php if (isset($_SESSION['users_id'])): ?>
            <a href="dashboard.php" class="btn-reservation">Mon compte</a>
        <?php else: ?>
            <a href="log.php" class="btn-reservation">Connexion</a>
        <?php endif; ?>
        <div class="responsive-menu"></div>
    </header>

    <div class="container">
        <h1>Gestion des Destinations</h1>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <button class="add-btn" onclick="toggleAddForm()">➕ Ajouter une nouvelle destination</button>

        <form action="admin.php" method="POST" class="edit-form" id="add-destination-form" style="display: none;" enctype="multipart/form-data">
            <label>Destination :</label>
            <input type="text" name="destination" required>
            <label>Description :</label>
            <textarea name="description"></textarea>
            <label>Budget (MGA) :</label>
            <input type="number" step="0.01" name="budget" required>
            <label>Hôtel :</label>
            <input type="text" name="hotel">
            <label>Description de l'Hôtel :</label>
            <textarea name="hotel_description"></textarea>
            <label>Localisation :</label>
            <input type="text" name="adresse">
            <label>Transport :</label>
            <input type="text" name="transport">
            <label>Activités :</label>
            <textarea name="activites"></textarea>
            <label>Image Principale (jpg, jpeg, png, gif ; max 5MB) :</label>
            <input type="file" name="main_image" accept="image/jpeg,image/png,image/gif">
            <label>Images de la Destination (plusieurs fichiers possibles, max 5MB chacun) :</label>
            <input type="file" name="destination_images[]" multiple accept="image/jpeg,image/png,image/gif">
            <label>Images de l'Hôtel (plusieurs fichiers possibles, max 5MB chacun) :</label>
            <input type="file" name="hotel_images[]" multiple accept="image/jpeg,image/png,image/gif">
            <button type="submit" name="add_destination">Ajouter</button>
        </form>

        <div class="destination-list">
            <?php if (empty($destinations)): ?>
                <p>Aucune destination enregistrée.</p>
            <?php else: ?>
                <?php foreach ($destinations as $dest): ?>
                    <div class="destination-item">
                        <h3><?php echo htmlspecialchars($dest['destination']); ?></h3>
                        <p><strong>Description :</strong> <?php echo htmlspecialchars($dest['description'] ?? 'N/A'); ?></p>
                        <p><strong>Budget :</strong> <?php echo htmlspecialchars($dest['budget']); ?> MGA</p>
                        <p><strong>Hôtel :</strong> <?php echo htmlspecialchars($dest['hotel'] ?? 'N/A'); ?></p>
                        <p><strong>Description de l'Hôtel :</strong> <?php echo htmlspecialchars($dest['hotel_description'] ?? 'N/A'); ?></p>
                        <p><strong>Localisation :</strong> <?php echo htmlspecialchars($dest['adresse'] ?? 'N/A'); ?></p>
                        <p><strong>Transport :</strong> <?php echo htmlspecialchars($dest['transport'] ?? 'N/A'); ?></p>
                        <p><strong>Activités :</strong> <?php echo htmlspecialchars($dest['activites'] ?? 'N/A'); ?></p>
                        <p><strong>Image :</strong> <?php echo htmlspecialchars($dest['image'] ?? 'N/A'); ?></p>
                        <p><strong>Images de la Destination :</strong> <?php echo htmlspecialchars($dest['destination_images'] ?? 'N/A'); ?></p>
                        <p><strong>Images de l'Hôtel :</strong> <?php echo htmlspecialchars($dest['hotel_images'] ?? 'N/A'); ?></p>

                        <button class="edit-btn" onclick="toggleEditForm('edit-<?php echo $dest['id']; ?>')">Modifier</button>
                        <form action="admin.php" method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $dest['id']; ?>">
                            <button type="submit" name="delete" class="delete-btn">Supprimer</button>
                        </form>

                        <form action="admin.php" method="POST" class="edit-form" id="edit-<?php echo $dest['id']; ?>" enctype="multipart/form-data" style="display: none;">
                            <input type="hidden" name="id" value="<?php echo $dest['id']; ?>">
                            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($dest['image'] ?? ''); ?>">
                            <input type="hidden" name="existing_destination_images" value="<?php echo htmlspecialchars($dest['destination_images'] ?? ''); ?>">
                            <input type="hidden" name="existing_hotel_images" value="<?php echo htmlspecialchars($dest['hotel_images'] ?? ''); ?>">
                            <label>Destination :</label>
                            <input type="text" name="destination" value="<?php echo htmlspecialchars($dest['destination']); ?>" required>
                            <label>Description :</label>
                            <textarea name="description"><?php echo htmlspecialchars($dest['description'] ?? ''); ?></textarea>
                            <label>Budget (MGA) :</label>
                            <input type="number" step="0.01" name="budget" value="<?php echo htmlspecialchars($dest['budget']); ?>" required>
                            <label>Hôtel :</label>
                            <input type="text" name="hotel" value="<?php echo htmlspecialchars($dest['hotel'] ?? ''); ?>">
                            <label>Description de l'Hôtel :</label>
                            <textarea name="hotel_description"><?php echo htmlspecialchars($dest['hotel_description'] ?? ''); ?></textarea>
                            <label>Localisation :</label>
                            <input type="text" name="adresse" value="<?php echo htmlspecialchars($dest['adresse'] ?? ''); ?>">
                            <label>Transport :</label>
                            <input type="text" name="transport" value="<?php echo htmlspecialchars($dest['transport'] ?? ''); ?>">
                            <label>Activités :</label>
                            <textarea name="activites"><?php echo htmlspecialchars($dest['activites'] ?? ''); ?></textarea>
                            <label>Image Principale (actuelle : <?php echo htmlspecialchars($dest['image'] ?? 'aucune'); ?>) :</label>
                            <input type="file" name="main_image" accept="image/jpeg,image/png,image/gif">
                            <label>Images de la Destination (actuelles : <?php echo htmlspecialchars($dest['destination_images'] ?? 'aucune'); ?>) :</label>
                            <input type="file" name="destination_images[]" multiple accept="image/jpeg,image/png,image/gif">
                            <label>Images de l'Hôtel (actuelles : <?php echo htmlspecialchars($dest['hotel_images'] ?? 'aucune'); ?>) :</label>
                            <input type="file" name="hotel_images[]" multiple accept="image/jpeg,image/png,image/gif">
                            <button type="submit" name="update">Mettre à jour</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h1>Gestion des Contacts</h1>
        <div class="contact-list">
            <?php if (empty($contacts)): ?>
                <p>Aucun contact enregistré.</p>
            <?php else: ?>
                <?php
                $current_contact_id = null;
                foreach ($contacts as $contact):
                    if ($contact['id'] !== $current_contact_id):
                        $current_contact_id = $contact['id'];
                ?>
                    <div class="contact-item">
                        <h3><?php echo htmlspecialchars($contact['subject']); ?> (de <?php echo htmlspecialchars($contact['name']); ?>)</h3>
                        <p><strong>Email :</strong> <?php echo htmlspecialchars($contact['email']); ?></p>
                        <p><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></p>
                        <p><strong>Message initial :</strong> <?php echo htmlspecialchars($contact['contact_message']); ?></p>
                        <?php if (!$contact['is_read']): ?>
                            <p><em>Non lu</em></p>
                        <?php endif; ?>
                        <h4>Conversation :</h4>
                        <?php
                        // Afficher tous les messages pour ce contact
                        foreach ($contacts as $msg):
                            if ($msg['id'] == $current_contact_id && !empty($msg['message'])):
                        ?>
                            <div class="message-item">
                                <p><strong><?php echo $msg['sender'] == 'user' ? 'Utilisateur' : 'Administrateur'; ?> :</strong> <?php echo htmlspecialchars($msg['message']); ?></p>
                                <p><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($msg['message_created_at'])); ?></p>
                                <?php if ($msg['sender'] == 'user' && !$msg['message_is_read']): ?>
                                    <p><em>Non lu</em></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; endforeach; ?>
                        <form action="admin.php" method="POST" class="message-form">
                            <input type="hidden" name="message_submit" value="1">
                            <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                            <label for="new_message_<?php echo $contact['id']; ?>">Répondre :</label>
                            <textarea id="new_message_<?php echo $contact['id']; ?>" name="new_message" rows="3" placeholder="Votre réponse..." required></textarea>
                            <button type="submit">Envoyer la réponse</button>
                        </form>
                    </div>
                <?php endif; endforeach; ?>
                <?php if (isset($message_success) && $message_success): ?>
                    <p class="alert success">Votre réponse a été envoyée !</p>
                <?php elseif (isset($message_error)): ?>
                    <p class="alert error"><?php echo htmlspecialchars($message_error); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="founders">
            <p>Réalisé par <span>Nantenaina José</span> | Tous les droits sont réservés.</p>
        </div>
    </footer>

    <script>
        function toggleEditForm(formId) {
            const form = document.getElementById(formId);
            form.style.display = form.style.display === "block" ? "none" : "block";
        }

        function toggleAddForm() {
            const form = document.getElementById('add-destination-form');
            form.style.display = form.style.display === "block" ? "none" : "block";
        }
    </script>
</body>
</html>