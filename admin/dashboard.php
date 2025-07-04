<?php
require_once '../config/db.php';
include 'auth.php'; // Vérifie que l'admin est connecté
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Tableau de Bord</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1> <span style="color:#000;background:rgba(255,255,255,0.8);padding:5px 10px;border-radius:8px;">Fit</span><span style="color:#28a745">Reserve</span> - Espace Administrateur</h1>
        <nav> 
            <a href="salles.php">Gérer les Salles</a> |
            <a href="reservations.php">Gérer les Réservations</a> |
            <a href="creneaux.php">Voir les Créneaux</a> |
            <a href="deconnexion_admin.php">Déconnexion</a>
        </nav>
    </header>

    <main class="auth-container">
        <div class="auth-card">
            <h2>Bienvenue, <?= htmlspecialchars($_SESSION['nom']) ?> 👋</h2>
            <p>Voici les options disponibles pour la gestion du système :</p>
            <ul style="list-style-type: none; padding-left: 0;">
                <li><a href="salles.php" class="btn btn-primary btn-block">📁 Gérer les Salles</a></li>
                <li><a href="reservations.php" class="btn btn-primary btn-block">📋 Gérer les Réservations</a></li>
                <li><a href="creneaux.php" class="btn btn-primary btn-block">🕒 Voir les Créneaux par Salle</a></li>
                <li><a href="deconnexion_admin.php" class="btn btn-primary btn-block">🚪 Déconnexion</a></li>
            </ul>
        </div>
    </main>

<?php include '../includes/footer.php'; ?>

