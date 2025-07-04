<?php
session_start();
include 'includes/header.php';
include 'config/db.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-error'>Connectez-vous pour voir vos réservations.</div>";
    include 'includes/footer.php';
    exit;
}

// Récupération des réservations de l'utilisateur
$stmt = $pdo->prepare("SELECT r.*, s.nom AS salle_nom, r.debut, r.fin, r.status 
                       FROM reservations r 
                       JOIN sports_halls s ON r.salle_id = s.id 
                       WHERE r.user_id = ? 
                       ORDER BY r.debut DESC");
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll();
?>
<?php if (isset($_GET['annule']) && $_GET['annule'] == 1): ?>
    <div class="alert alert-success">Réservation annulée avec succès.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?php
            switch ($_GET['error']) {
                case 'notfound':
                    echo "Réservation introuvable ou non autorisée.";
                    break;
                case 'notcancellable':
                    echo "Cette réservation ne peut plus être annulée.";
                    break;
            }
        ?>
    </div>
<?php endif; ?>

<main>
    <h2>Mes Réservations</h2>

    <?php if (!$reservations): ?>
        <p>Vous n'avez aucune réservation.</p>
    <?php else: ?>
        <table class="reservation-table" border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>Salle</th>
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
                        <td><?= htmlspecialchars($res['salle_nom']) ?></td>
                        <td><?= date("d/m/Y", strtotime($res['debut'])) ?></td>
                        <td><?= date("H:i", strtotime($res['debut'])) ?></td>
                        <td><?= date("H:i", strtotime($res['fin'])) ?></td>
                        <td><?= htmlspecialchars(ucfirst($res['status'])) ?></td>
                        <td>
                            <?php if ($res['status'] === 'en attente' || $res['status'] === 'valide'): ?>
                                <a href="annuler.php?id=<?= $res['id'] ?>" onclick="return confirm('Annuler cette réservation ?')">Annuler</a>
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

<?php include 'includes/footer.php'; ?>
