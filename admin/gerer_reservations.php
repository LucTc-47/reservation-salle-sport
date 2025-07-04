<?php
include 'auth.php';
include '../config/db.php';

$stmt = $pdo->query("SELECT r.*, u.nom AS utilisateur, s.nom AS salle
                     FROM reservations r
                     JOIN users u ON r.user_id = u.id
                     JOIN sports_halls s ON r.salle_id = s.id
                     ORDER BY r.date_reservation ASC");
$reservations = $stmt->fetchAll();
?>

<h2>Gérer les réservations</h2>

<table border="1" cellpadding="5">
    <tr>
        <th>Utilisateur</th>
        <th>Salle</th>
        <th>Date</th>
        <th>Heure</th>
        <th>Durée</th>
        <th>Action</th>
    </tr>
    <?php foreach ($reservations as $r): ?>
    <tr>
        <td><?= htmlspecialchars($r['utilisateur']) ?></td>
        <td><?= htmlspecialchars($r['salle']) ?></td>
        <td><?= $r['date_reservation'] ?></td>
        <td><?= $r['heure_debut'] ?></td>
        <td><?= $r['duree'] ?> h</td>
        <td>
            <a href="supprimer_reservation.php?id=<?= $r['id'] ?>" onclick="return confirm('Supprimer ?')">Supprimer</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<a href="dashboard.php">⬅ Retour</a>
