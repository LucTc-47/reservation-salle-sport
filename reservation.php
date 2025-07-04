<?php
include 'includes/header.php';
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Vous devez être connecté pour réserver. <a href='connexion.php'>Connexion</a>";
    include 'includes/footer.php';
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $salle_id = $_POST['salle_id'];
    $date = $_POST['date_reservation'];
    $heure_debut = $_POST['heure_debut'];
    $duree = $_POST['duree'];

    // Vérifie les conflits
    $stmt = $pdo->prepare("SELECT * FROM reservations 
        WHERE salle_id = ? AND date_reservation = ? 
        AND (heure_debut < ADDTIME(?, SEC_TO_TIME(?*3600)) AND ADDTIME(heure_debut, SEC_TO_TIME(duree*3600)) > ?)");
    $stmt->execute([$salle_id, $date, $heure_debut, $duree, $heure_debut]);

    if ($stmt->rowCount() > 0) {
        echo "<p>Conflit d’horaire. Veuillez choisir un autre créneau.</p>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO reservations (user_id, salle_id, date_reservation, heure_debut, duree)
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $salle_id, $date, $heure_debut, $duree]);
        echo "<p>Réservation réussie !</p>";
    }
}
?>

<h2>Réserver une salle</h2>

<form method="POST">
    <label for="salle">Salle :</label>
    <select name="salle_id" required>
        <?php
        $salles = $pdo->query("SELECT * FROM sports_halls");
        foreach ($salles as $salle) {
            echo "<option value='{$salle['id']}'>" . htmlspecialchars($salle['nom']) . "</option>";
        }
        ?>
    </select><br><br>

    Date : <input type="date" name="date_reservation" required><br><br>
    Heure de début : <input type="time" name="heure_debut" required><br><br>
    Durée (1 à 5 heures) :
    <select name="duree" required>
        <?php for ($i = 1; $i <= 5; $i++) echo "<option value='$i'>$i heure(s)</option>"; ?>
    </select><br><br>

    <button type="submit">Réserver</button>
</form>

<?php include 'includes/footer.php'; ?>
