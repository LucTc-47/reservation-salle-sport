<?php
session_start();
require_once "config/db.php";

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php?redirect=planning.php");
    exit();
}

// Récupère l'ID de la salle depuis GET
if (!isset($_GET['salle_id'])) {
    die('Salle non spécifiée.');
}
$salle_id = intval($_GET['salle_id']);

// Date sélectionnée ou aujourd'hui
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Plage horaire de 8h à 20h
$heures = range(8, 20);

// Fonction pour récupérer les réservations validées
function getReservations(PDO $pdo, int $salle_id, string $date): array {
    $sql = "
        SELECT HOUR(debut) AS h, 
               TIMESTAMPDIFF(HOUR, debut, fin) AS duree
        FROM reservations
        WHERE salle_id = ?
          AND DATE(debut) = ?
          AND status = 'valide'
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$salle_id, $date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$reservations = getReservations($pdo, $salle_id, $date);
$occ = [];
foreach ($reservations as $r) {
    for ($i = 0; $i < $r['duree']; $i++) {
        $occ[] = $r['h'] + $i;
    }
}

// Récupère le nom de la salle
$stmt = $pdo->prepare("SELECT nom FROM sports_halls WHERE id = ?");
$stmt->execute([$salle_id]);
$salle = $stmt->fetch();
if (!$salle) die('Salle introuvable.');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Planning - <?= htmlspecialchars($salle['nom']) ?></title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .creneau { display:inline-block; padding:8px; margin:4px; width:60px; text-align:center; cursor:pointer; border-radius:4px; }
    .libre { background-color:#2ecc71; color:#fff; }
    .occupe { background-color:#e74c3c; color:#fff; cursor:not-allowed; }
    .tooltip { position: relative; }
    .tooltip:hover::after {
      content: attr(data-tip);
      position: absolute;
      bottom: 125%; left: 50%; transform: translateX(-50%);
      background: rgba(0,0,0,0.75); color: #fff;
      padding:4px 8px; border-radius:4px; white-space: nowrap;
      z-index:10;
    }
    .date-select { margin-bottom:1rem; }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<main class="container">
  <h2>Planning de la salle "<?= htmlspecialchars($salle['nom']) ?>"</h2>
  <form method="get" class="date-select">
    <input type="hidden" name="salle_id" value="<?= $salle_id ?>">
    <label for="date">Date :</label>
    <input type="date" id="date" name="date" value="<?= htmlspecialchars($date) ?>">
    <button type="submit" class="btn btn-primary">Voir</button>
  </form>
  <div class="creneaux">
    <?php foreach ($heures as $h): 
      $isOcc = in_array($h, $occ);
      $cls = $isOcc ? 'occupe' : 'libre';
      $tip = $isOcc
        ? 'Salle occupée à cette heure.'
        : 'Salle libre à cette heure, vous pouvez cliquer.';
      $href = $isOcc ? '#' : "reserver.php?salle_id={$salle_id}&date={$date}&heure={$h}:00";
    ?>
      <span 
        class="creneau <?= $cls ?> tooltip" 
        data-tip="<?= $tip ?>"
        <?= $isOcc ? '' : "onclick=\"window.location='{$href}'\"" ?>
      >
        <?= $h ?>h
      </span>
    <?php endforeach; ?>
  </div>
</main>
<?php include 'includes/footer.php'; ?>
</body>
</html>
