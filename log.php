<?php
session_start();
require 'database.php';

$errors = [];
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Tous les champs sont obligatoires.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['users_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - MadaVoyage</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="log.css">
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
        </ul>
        <?php if (isset($_SESSION['users_id'])): ?>
            <a href="dashboard.php" class="btn-reservation">Mon compte</a>
        <?php else: ?>
            <a href="log.php" class="btn-reservation">Connexion</a>
        <?php endif; ?>
        <div class="responsive-menu"></div>
    </header>

    <div class="form-container">
        <h1>Connexion</h1>
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form action="log.php" method="POST">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Se connecter</button>
            <a href="inscription.php">Pas de compte ? Inscrivez-vous</a>
        </form>
    </div>

    <footer>
        <p>Réalisé par <span>Nantenaina José</span> | Tous les droits sont réservés.</p>
    </footer>
</body>
</html>