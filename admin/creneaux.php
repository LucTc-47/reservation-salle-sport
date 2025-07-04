<?php
require_once '../config/db.php';
include 'auth.php';  // même auth pour tout l'espace admin

// Récupération de la date cible : GET ?date=YYYY-MM-DD ou aujourd'hui par défaut
$date_cible = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
// Mode d'affichage: 'daily' ou 'weekly'
$mode = isset($_GET['mode']) && $_GET['mode'] === 'weekly' ? 'weekly' : 'daily';

// Calcul des jours précédent et suivant
$timestamp = strtotime($date_cible);
$date_prec = date('Y-m-d', strtotime('-1 day', $timestamp));
$date_suiv = date('Y-m-d', strtotime('+1 day', $timestamp));

// Pour la vue hebdomadaire: calcul du début de semaine (lundi)
$monday = date('Y-m-d', strtotime('monday this week', $timestamp));
$week_dates = [];
for ($i = 0; $i < 7; $i++) {
    $week_dates[] = date('Y-m-d', strtotime("{$monday} +{$i} days"));
}

// Plage horaire de 8h à 20h
$heures = range(8, 20);

// Extrait les créneaux réservés pour une salle et une date données
function getReservationsPourSalle(PDO $pdo, int $salle_id, string $date): array {
    $sql = "
        SELECT 
            HOUR(debut) AS heure_debut,
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

// Récupère toutes les salles, triées par nom
$salles = $pdo
    ->query("SELECT id, nom FROM sports_halls ORDER BY nom ASC")
    ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Admin – Créneaux par Salle</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .btn { padding:0.5rem 1rem; margin:0.2rem; border:none; background:#1976d2; color:#fff; cursor:pointer; border-radius:4px; }
    .creneau { display:inline-block; padding:4px; margin:1px; width:40px; text-align:center; border-radius:4px; font-size:0.9rem; }
    .occupe { background:#e74c3c; }
    .libre  { background:#2ecc71; }
    table.hebdo { width:100%; border-collapse:collapse; margin-bottom:2rem; }
    table.hebdo th, table.hebdo td { border:1px solid #ddd; padding:6px; text-align:center; }
    table.hebdo th { background:#f0f0f0; }
    .date-nav { text-align:center; margin-bottom:1.5rem; }
  </style>
</head>
<body>
  <header>
    <h1>Admin – Créneaux disponibles / occupés</h1>
    <nav>
      <a href="dashboard.php">Tableau de Bord</a> |
      <a href="reservations.php">Gérer les Réservations</a> |
      <a href="ajouter_salle.php">Ajouter une Salle</a> |
      <a href="deconnexion_admin.php">Déconnexion</a>
    </nav>
  </header>

  <main class="container">
    <h1>Vue <?= $mode === 'weekly' ? 'Hebdomadaire' : 'Journalière' ?></h1>
    <div class="date-nav">
      <form method="get" action="">
        <label for="date">📅 Choisir une date :</label>
        <input type="date" id="date" name="date" value="<?= htmlspecialchars($date_cible) ?>">
        <button type="submit" name="mode" value="daily" class="btn">Voir Jour</button>
        <button type="submit" name="mode" value="weekly" class="btn">Voir Semaine</button>
      </form>
      <?php if ($mode === 'daily'): ?>
      <a href="?date=<?= $date_prec ?>&mode=daily" class="btn">⬅️ Jour précédent</a>
      <a href="?date=<?= $date_suiv ?>&mode=daily" class="btn">Jour suivant ➡️</a>
      <?php else: ?>
      <a href="?date=<?= date('Y-m-d', strtotime('-7 days', $timestamp)) ?>&mode=weekly" class="btn">⬅️ Semaine précédente</a>
      <a href="?date=<?= date('Y-m-d', strtotime('+7 days', $timestamp)) ?>&mode=weekly" class="btn">Semaine suivante ➡️</a>
      <?php endif; ?>
    </div>

    <?php foreach ($salles as $salle): ?>
      <section class="salle-creneaux">
        <h2><?= htmlspecialchars($salle['nom']) ?></h2>

        <?php if ($mode === 'daily'): ?>
          <div class="creneaux-grid">
            <?php
            $reserves = getReservationsPourSalle($pdo, (int)$salle['id'], $date_cible);
            $occ = [];
            foreach ($reserves as $r) {
                for ($i = 0; $i < $r['duree']; $i++) {
                    $occ[] = $r['heure_debut'] + $i;
                }
            }
            ?>
            <?php foreach ($heures as $h): $c = in_array($h, $occ) ? 'occupe' : 'libre'; ?>
              <span class="creneau <?= $c ?>"><?= $h ?>h</span>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <table class="hebdo">
            <thead>
              <tr>
                <th>Heure</th>
                <?php foreach ($week_dates as $d): ?>
                  <th><?= date('D d/m', strtotime($d)) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($heures as $h): ?>
                <tr>
                  <td><?= $h ?>h</td>
                  <?php foreach ($week_dates as $d): 
                    $r = getReservationsPourSalle($pdo, (int)$salle['id'], $d);
                    $occD = [];
                    foreach ($r as $rr) {
                        for ($i = 0; $i < $rr['duree']; $i++) {
                            $occD[] = $rr['heure_debut'] + $i;
                        }
                    }
                    $cls = in_array($h, $occD) ? 'occupe' : 'libre';
                  ?>
                    <td class="<?= $cls ?>">&nbsp;</td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>
    <?php endforeach; ?>

    <a href="dashboard.php" class="btn">⬅️ Retour au tableau de bord</a>
  </main>

  <?php include '../includes/footer.php'; ?>

