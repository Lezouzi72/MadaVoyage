<?php
session_start();
require 'database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['users_id'])) {
    header("Location: log.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et sécuriser les données du formulaire
    $destination = trim($_POST['destination']);
    $depart = trim($_POST['depart']);
    $retour = trim($_POST['retour']);
    $budget = filter_var($_POST['budget'], FILTER_VALIDATE_FLOAT);
    $hotel = trim($_POST['hotel']);
    $adresse = trim($_POST['adresse']);
    $reservation = trim($_POST['reservation']);
    $transport = trim($_POST['transport']);
    $activites = trim($_POST['activites']);
    $contacts = trim($_POST['contacts']);
    $bagages = trim($_POST['bagages']);

    // Vérification des champs obligatoires (ajustée pour correspondre à organisation.php)
    if (empty($destination) || empty($depart) || empty($retour) || $budget === false) {
        $_SESSION['error'] = "Les champs obligatoires (destination, départ, retour, budget) doivent être remplis correctement.";
        header("Location: organisation.php");
        exit();
    }

    // Validation des dates côté serveur
    $today = date('Y-m-d');
    if ($depart < $today) {
        $_SESSION['error'] = "La date de départ ne peut pas être antérieure à aujourd'hui.";
        header("Location: organisation.php");
        exit();
    }
    if ($retour < $depart) {
        $_SESSION['error'] = "La date de retour doit être postérieure à la date de départ.";
        header("Location: organisation.php");
        exit();
    }

    try {
        // Préparer la requête SQL avec l'ID utilisateur
        $sql = "INSERT INTO voyages (users_id, destination, depart, retour, budget, hotel, adresse, reservation, transport, activites, contacts, bagages)
                VALUES (:users_id, :destination, :depart, :retour, :budget, :hotel, :adresse, :reservation, :transport, :activites, :contacts, :bagages)";
        
        $stmt = $pdo->prepare($sql);

        // Exécuter la requête avec les valeurs (pas besoin de htmlspecialchars ici)
        $stmt->execute([
            ':users_id' => $_SESSION['users_id'],
            ':destination' => $destination,
            ':depart' => $depart,
            ':retour' => $retour,
            ':budget' => $budget,
            ':hotel' => $hotel ?: null, // Valeur vide devient NULL
            ':adresse' => $adresse ?: null,
            ':reservation' => $reservation,
            ':transport' => $transport,
            ':activites' => $activites,
            ':contacts' => $contacts ?: null,
            ':bagages' => $bagages ?: null
        ]);

        // Stocker un message de succès dans la session
        $_SESSION['success'] = "Votre voyage a été enregistré avec succès.";
        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        header("Location: organisation.php");
        exit();
    }
}
?>