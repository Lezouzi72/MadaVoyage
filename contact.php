<?php
session_start();
require 'database.php';

// Handle contact form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_submit'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);
    $user_id = isset($_SESSION['users_id']) ? $_SESSION['users_id'] : null;

    try {
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message, $user_id]);
        $contact_id = $pdo->lastInsertId();

        // Insert the message into the messages table
        $stmt = $pdo->prepare("INSERT INTO messages (contact_id, user_id, message, sender) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$contact_id, $user_id, $message]);
        $success = true;
    } catch (PDOException $e) {
        $error = "Erreur lors de l'envoi du message : " . $e->getMessage();
    }
}

// Handle user message submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message_submit']) && isset($_SESSION['users_id'])) {
    $message = htmlspecialchars($_POST['new_message']);
    $contact_id = filter_var($_POST['contact_id'], FILTER_VALIDATE_INT);

    if ($message && $contact_id) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (contact_id, user_id, message, sender) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$contact_id, $_SESSION['users_id'], $message]);
            $message_success = true;
        } catch (PDOException $e) {
            $message_error = "Erreur lors de l'envoi du message : " . $e->getMessage();
        }
    }
}

// Fetch user's contact submissions and messages
$messages = [];
if (isset($_SESSION['users_id'])) {
    $stmt = $pdo->prepare("
        SELECT c.id, c.subject, c.name, m.message, m.message, m.sender, m.created_at, m.is_read
        FROM contacts c
        LEFT JOIN messages m ON c.id = m.contact_id
        WHERE c.user_id = ? OR m.user_id = ?
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$_SESSION['users_id'], $_SESSION['users_id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - MadaVoyage</title>
    <link rel="icon" type="image/png" href="logo/mvv.jpg">
    <link rel="stylesheet" href="formulaire.css">
    <link rel="stylesheet" href="contact.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php"><span>Mada</span>Voyages</a>
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

    <div class="container1">
        <h1>Nous Contacter</h1>
        
        <?php if (isset($success) && $success): ?>
            <p class="alert success">Votre message a été envoyé avec succès ! Nous vous répondrons sous peu.</p>
        <?php elseif (isset($error)): ?>
            <p class="alert error">Votre message n'a pas été envoyé ! Veuillez réessayer.</p>
        <?php endif; ?>
        
        <form action="contact.php" method="POST" class="contact-form">
            <input type="hidden" name="contact_submit" value="1">
            <label for="name">Nom Complet</label>
            <input type="text" id="name" name="name" placeholder="Votre nom" required>

            <label for="email">Adresse Email</label>
            <input type="email" id="email" name="email" placeholder="votre@email.com" required>

            <label for="subject">Sujet</label>
            <input type="text" id="subject" name="subject" placeholder="Objet de votre message" required>

            <label for="message">Votre Message</label>
            <textarea id="message" name="message" rows="5" placeholder="Écrivez votre message ici..." required></textarea>

            <button type="submit">Envoyer</button>
        </form>

        <?php if (isset($_SESSION['users_id'])): ?>
            <div class="messages-section">
                <h3>Vos Messages</h3>
                <?php if (empty($messages)): ?>
                    <p>Aucun message pour le moment.</p>
                <?php else: ?>
                    <?php 
                    $current_contact_id = null;
                    foreach ($messages as $msg): 
                        if ($msg['id'] !== $current_contact_id):
                            $current_contact_id = $msg['id'];
                    ?>
                        <div class="message-item">
                            <p><strong>Sujet :</strong> <?php echo htmlspecialchars($msg['subject']); ?></p>
                            <p><strong>De :</strong> <?php echo htmlspecialchars($msg['name']); ?></p>
                            <p><strong>Message :</strong> <?php echo htmlspecialchars($msg['message']); ?></p>
                            <p><strong>Envoyé par :</strong> <?php echo $msg['sender'] == 'user' ? 'Vous' : 'Administrateur'; ?></p>
                            <p><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></p>
                            <?php if ($msg['sender'] == 'admin' && !$msg['is_read']): ?>
                                <p><em>Non lu</em></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; endforeach; ?>
                    <form action="contact.php" method="POST" class="message-form">
                        <input type="hidden" name="message_submit" value="1">
                        <input type="hidden" name="contact_id" value="<?php echo $current_contact_id ?? ''; ?>">
                        <label for="new_message">Répondre :</label>
                        <textarea id="new_message" name="new_message" rows="3" placeholder="Votre réponse..." required></textarea>
                        <button type="submit">Envoyer la réponse</button>
                    </form>
                    <?php if (isset($message_success) && $message_success): ?>
                        <p class="alert success">Votre réponse a été envoyée !</p>
                    <?php elseif (isset($message_error)): ?>
                        <p class="alert error">Votre réponse n'a pas été envoyée ! Veuillez réessayer.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="contact-info">
            <h3>Autres Moyens de Contact</h3>
            <p><i class="fas fa-envelope"></i> contact@madavoyage.mg</p>
            <p><i class="fas fa-phone"></i> +261 34 12 345 67</p>
            <p><i class="fas fa-map-marker-alt"></i> Antananarivo, Madagascar</p>
        </div>
    </div>

    <footer>
        <div class="founders">
            <p>Réalisé par <span>Nantenaina José</span> | Tous les droits sont réservés.</p>
        </div>
    </footer>

    <script>
        const toggleMenu = document.querySelector('.responsive-menu');
        const menu = document.querySelector('.menu');
        toggleMenu.onclick = () => {
            toggleMenu.classList.toggle('active');
            menu.classList.toggle('responsive');
        };
    </script>
</body>
</html>