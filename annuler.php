<?php
session_start();
require_once "config/db.php";

// Vérification que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// Vérification de la présence d'un ID de réservation
if (!isset($_GET['id'])) {
    header("Location: mes_reservations.php");
    exit();
}

$reservation_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Vérification que la réservation appartient bien à l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? AND user_id = ?");
$stmt->execute([$reservation_id, $user_id]);
$reservation = $stmt->fetch();

if (!$reservation) {
    // Tentative d'accès non autorisée
    header("Location: mes_reservations.php?error=notfound");
    exit();
}

// Vérifie que la réservation est annulable
if (!in_array($reservation['status'], ['en attente', 'valide'])) {
    header("Location: mes_reservations.php?error=notcancellable");
    exit();
}

// Mise à jour du statut à "annule"
$stmt = $pdo->prepare("UPDATE reservations SET status = 'annule' WHERE id = ?");
$stmt->execute([$reservation_id]);

header("Location: mes_reservations.php?annule=1");
exit();
