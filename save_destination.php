<?php
session_start();
if (!isset($_SESSION['users_id'])) {
    header("Location: log.php");
    exit();
}

require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['users_id']; // Assurez-vous que cette variable correspond à votre session
    $destination = filter_var($_POST['destination'], FILTER_SANITIZE_STRING);
    $budget = filter_var($_POST['budget'], FILTER_VALIDATE_FLOAT);
    $hotel = filter_var($_POST['hotel'], FILTER_SANITIZE_STRING);
    $adresse = filter_var($_POST['adresse'], FILTER_SANITIZE_STRING);
    $transport = filter_var($_POST['transport'], FILTER_SANITIZE_STRING);
    $activites = filter_var($_POST['activites'], FILTER_SANITIZE_STRING);

    // Validation de base
    if (!$destination || $budget === false) {
        die("Données invalides");
    }

    // Utiliser 'users_id' si c'est le nom dans votre table, sinon 'user_id'
    $stmt = $pdo->prepare("INSERT INTO destinations (destination, budget, hotel, adresse, transport, activites) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$destination, $budget, $hotel, $adresse, $transport, $activites]);

    // Stocker les données dans la session pour organisation.php
    $_SESSION['destination_data'] = [
        'destination' => $destination,
        'budget' => $budget,
        'hotel' => $hotel,
        'adresse' => $adresse,
        'transport' => $transport,
        'activites' => $activites
    ];

    header("Location: organisation.php");
    exit();
}
?>