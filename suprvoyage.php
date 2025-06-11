<?php
session_start();
if (!isset($_SESSION['users_id'])) {
    header("Location: log.php");
    exit();
}

require 'database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM voyages WHERE id = ? AND users_id = ?");
    $stmt->execute([$id, $_SESSION['users_id']]);
}

header("Location: dashboard.php");
exit();
?>
