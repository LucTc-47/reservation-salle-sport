<?php
session_start();
require_once "config/db.php";

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$message = "";
$user_id = $_SESSION['user_id'];

// Vérifie qu'un ID de salle est passé dans l'URL
if (!isset($_GET['salle_id'])) {
    die("Salle non spécifiée.");
}

$salle_id = intval($_GET['salle_id']);

// Récupérer les informations de la salle
$stmt = $pdo->prepare("SELECT * FROM sports_halls WHERE id = ?");
$stmt->execute([$salle_id]);
$salle = $stmt->fetch();

if (!$salle) {
    die("Salle introuvable.");
}

// Traitement du formulaire de réservation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération et nettoyage des données
    $date = $_POST['date'];
    $heure = $_POST['heure'];
    $duree = intval($_POST['duree']);

    // Vérification que l'heure se trouve entre 08h et 20h
    $heureInt = intval(substr($heure, 0, 2));
    if ($heureInt < 8 || $heureInt >= 20) {
        $message = "Veuillez choisir une heure entre 08h et 20h.";
    } else {
        // On construit le créneau de réservation
        $debut = $date . " " . $heure . ":00";
        $fin = date("Y-m-d H:i:s", strtotime("$debut +$duree hour"));

        // Vérifier la disponibilité du créneau
        // Ici on vérifie qu'il n'existe pas déjà un enregistrement se chevauchant pour cette salle
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE salle_id = ? AND ((debut < ? AND fin > ?) OR (debut < ? AND fin > ?))");
        $stmt->execute([$salle_id, $fin, $fin, $debut, $debut]);
        if ($stmt->rowCount() > 0) {
            $message = "Ce créneau est déjà réservé.";
        } else {
            // Insertion de la réservation avec le statut 'en attente'
            $stmt = $pdo->prepare("INSERT INTO reservations (user_id, salle_id, debut, fin, status) VALUES (?, ?, ?, ?, 'en attente')");
            if ($stmt->execute([$user_id, $salle_id, $debut, $fin])) {
                $message = "Réservation envoyée avec succès ! En attente de validation.";
            } else {
                $message = "Erreur lors de la réservation.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réserver une salle</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <h1>Réserver : <?= htmlspecialchars($salle['nom']) ?></h1>
    <nav>
        <a href="index.php">Accueil</a> |
        <a href="mes_reservations.php">Mes réservations</a> |
        <a href="deconnexion.php">Déconnexion</a>
    </nav>
</header>
<main>
    <?php
    // Affichage d'un message d'alerte
    if ($message) {
        $class = (strpos($message, "succès") !== false) ? "alert-success" : "alert-error";
        echo "<div class='alert $class'><strong>$message</strong></div>";
    }
    ?>
    <form method="POST" class="auth-form">
        <label for="date">Date :</label>
        <input type="date" id="date" name="date" required min="<?= date('Y-m-d') ?>">

        <label for="heure">Heure (entre 08h et 20h) :</label>
        <input type="time" id="heure" name="heure" required min="08:00" max="20:00">

        <label for="duree">Durée (1 à 5 heures) :</label>
        <select id="duree" name="duree" required>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?> heure(s)</option>
            <?php endfor; ?>
        </select>

        <button type="submit" class="btn btn-primary btn-block">Valider la réservation</button>
    </form>
</main>
<?php include 'includes/footer.php'; ?>
