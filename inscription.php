<?php
require 'database.php'; // Fichier de connexion à la base de données

$error_message = ''; // Variable pour stocker le message d'erreur

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les valeurs du formulaire et les nettoyer
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Vérifier si les champs sont bien remplis
    if (empty($username) || empty($email) || empty($password)) {
        $error_message = "Erreur : Tous les champs sont obligatoires !";
    }
    // Vérifier si l'email est valide
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Erreur : Adresse email invalide !";
    }
    else {
        // Vérifier si l'utilisateur existe déjà
        $checkUser = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $checkUser->execute(['email' => $email]);

        if ($checkUser->rowCount() > 0) {
            $error_message = "Erreur : Cet email est déjà utilisé !";
        }
        else {
            // Hacher le mot de passe
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insérer dans la base de données
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);

            // Rediriger après inscription réussie
            header("Location: log.php?success=1");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - MadaVoyage</title>
    <link rel="stylesheet" href="inscription.css">
</head>
<body>
<header>
        <div class="logo">
            <a href="index.php"> <span>Mada</span>Voyage</a>
        </div>
        <ul class="menu">
            <li><a href="index.php">Acceuil</a></li>
            <li><a href="about.php">à propos</a></li>
            <li><a href="destination.php">destinations</a></li>
            <li><a href="#contact">contact</a></li>
        </ul>
        <a href="log.php" class="btn-reservation">Connexion</a>

        <div class="responsive-menu"></div>
    </header>

    <form action="inscription.php" method="post">
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" required>

        <label for="email">Email :</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">S'inscrire</button>
    </form>
</body>
</html>