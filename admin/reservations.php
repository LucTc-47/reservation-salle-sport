<?php
require_once "../config/db.php";
include 'auth.php'; // Vérifie que l'utilisateur est admin

// Action de validation ou d'annulation via URL
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'valider') {
        $pdo->prepare("UPDATE reservations SET status = 'valide' WHERE id = ?")->execute([$id]);
    } elseif ($action === 'annuler') {
        $pdo->prepare("UPDATE reservations SET status = 'annule' WHERE id = ?")->execute([$id]);
    }

    header("Location: reservations.php");
    exit();
}

// Récupération des réservations avec jointures
$stmt = $pdo->query("
    SELECT r.*, 
           u.nom AS utilisateur, 
           s.nom AS salle 
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN sports_halls s ON r.salle_id = s.id
    ORDER BY r.debut DESC
");
$reservations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Réservations</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
    <h1> <span style="color:#000;background:rgba(255,255,255,0.8);padding:5px 10px;border-radius:8px;">Fit</span><span style="color:#28a745">Reserve</span> - Réservations</h1>
    <nav>
        <a href="dashboard.php">⬅ Tableau de Bord</a>
    </nav>
</header>

<main>
    <h2>Liste des Réservations</h2>

    <?php if (empty($reservations)): ?>
        <p>Aucune réservation trouvée.</p>
    <?php else: ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Salle</th>
                    <th>Utilisateur</th>
                    <th>Date</th>
                    <th>Heure début</th>
                    <th>Heure fin</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $res): ?>
                    <tr>
                        <td><?= htmlspecialchars($res['salle']) ?></td>
                        <td><?= htmlspecialchars($res['utilisateur']) ?></td>
                        <td><?= date("d/m/Y", strtotime($res['debut'])) ?></td>
                        <td><?= date("H:i", strtotime($res['debut'])) ?></td>
                        <td><?= date("H:i", strtotime($res['fin'])) ?></td>
                        <td>
                            <?php
                                $statut = ucfirst($res['status']);
                                $couleur = match ($res['status']) {
                                    'valide' => '🟢',
                                    'annule' => '🔴',
                                    default => '🟡'
                                };
                                echo "$couleur $statut";
                            ?>
                        </td>
                        <td>
                            <?php if ($res['status'] === 'en attente'): ?>
                                <a href="?action=valider&id=<?= $res['id'] ?>" class="btn btn-primary">✅ Valider</a>
                                <a href="?action=annuler&id=<?= $res['id'] ?>" class="btn btn-primary" onclick="return confirm('Annuler cette réservation ?')">❌ Annuler</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

