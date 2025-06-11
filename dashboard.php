<?php
session_start();
if (!isset($_SESSION['users_id'])) {
    header("Location: log.php");
    exit();
}

require 'database.php';

// Récupérer les informations de l'utilisateur, y compris son rôle
$users_id = $_SESSION['users_id'];
$stmt = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
$stmt->execute([$users_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les voyages de l'utilisateur
$voyage_stmt = $pdo->prepare("SELECT * FROM voyages WHERE users_id = ?");
$voyage_stmt->execute([$users_id]);
$voyages = $voyage_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - MadaVoyage</title>
    <link rel="icon" type="image/png" href="logo/mvv.jpg">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <header>
        <div class="logo">
            <a href="index.php"><span>Mada</span>Voyage</a>
        </div>
        <ul class="menu">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="about.php">À propos</a></li>
            <li><a href="destination.php">Destinations</a></li>
            <li><a href="contact.php">Contact</a></li>
            <?php if ($user['role'] === 'admin'): ?>
                <li><a href="admin.php">Admin</a></li>
            <?php endif; ?>
        </ul>
        <div class="responsive-menu"></div>
    </header>

    <main>
        <section class="dashboard-container">
            <h1>Bienvenue, <?php echo htmlspecialchars($user['username']); ?> !</h1>
            <h2>Vos Voyages</h2>
            <button class="btn-ajout" onclick="window.location.href='destination.php'">➕ Ajouter un Voyage</button>
            
            <?php if (count($voyages) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Destination</th>
                                <th>Départ</th>
                                <th>Retour</th>
                                <th>Budget</th>
                                <th>Hôtel</th>
                                <th>Localisation</th>
                                <th>Réservation</th>
                                <th>Transport</th>
                                <th>Activités</th>
                                <th>Contacts</th>
                                <th>Bagages</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($voyages as $voyage): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($voyage['destination']); ?></td>
                                    <td><?php echo htmlspecialchars($voyage['depart']); ?></td>
                                    <td><?php echo htmlspecialchars($voyage['retour']); ?></td>
                                    <td><?php echo htmlspecialchars($voyage['budget']); ?> MGA</td>
                                    <td><?php echo htmlspecialchars($voyage['hotel'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($voyage['adresse'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($voyage['reservation']); ?></td>
                                    <td><?php echo htmlspecialchars($voyage['transport']); ?></td>
                                    <td><?php echo htmlspecialchars($voyage['activites'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($voyage['contacts'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($voyage['bagages'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a class="btn-modif" href="modifvoyage.php?id=<?php echo $voyage['id']; ?>">✏ Modifier</a>
                                        <a class="btn-suppr" href="suprvoyage.php?id=<?php echo $voyage['id']; ?>" onclick="return confirm('Voulez-vous supprimer ce voyage ?')">❌ Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>Aucun voyage enregistré.</p>
            <?php endif; ?>

            <!-- Bouton Admin centré pour les administrateurs -->
            <?php if ($user['role'] === 'admin'): ?>
                <a href="admin.php" class="btn-admin">Gestion Admin</a>
            <?php endif; ?>
        </section>
        
        <br>
        <a class="btn-deco" href="logout.php">🚪 Se Déconnecter</a>
    </main>
</body>
</html>