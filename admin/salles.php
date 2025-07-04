<?php
require_once "../config/db.php";
include 'auth.php';

// Suppression d’une salle (et de ses réservations liées)
if (isset($_GET['supprimer'])) {
    $id = (int) $_GET['supprimer'];
    $pdo->prepare("DELETE FROM reservations WHERE salle_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM sports_halls WHERE id = ?")->execute([$id]);
    header("Location: salles.php?deleted=1");
    exit();
}

// Récupère toutes les salles, triées par nom
$stmt = $pdo->query("SELECT * FROM sports_halls ORDER BY nom ASC");
$salles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin – Gestion des Salles</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1> <span style="color:#000;background:rgba(255,255,255,0.8);padding:5px 10px;border-radius:8px;">Fit</span><span style="color:#28a745">Reserve</span> - Gestion des Salles</h1>
        <nav>
            <a href="dashboard.php">Tableau de Bord</a> |
            <a href="ajouter_salle.php">Ajouter une Salle</a> |
            <a href="deconnexion_admin.php">Déconnexion</a>
        </nav>
    </header>
    <main>
        <h2>Liste des Salles</h2>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Salle supprimée avec succès.</div>
        <?php endif; ?>

        <?php if (empty($salles)): ?>
            <p>Aucune salle enregistrée.</p>
        <?php else: ?>
            <div class="salles-grid">
                <?php foreach ($salles as $salle): ?>
                    <div class="salle-card">
                        <div class="salle-image-container">
                            <img src="../uploads/<?= htmlspecialchars($salle['image']) ?>" alt="">
                        </div>
                        <h3><?= htmlspecialchars($salle['nom']) ?></h3>
                        <p><?= nl2br(htmlspecialchars($salle['description'])) ?></p>
                        <div style="margin-top:10px;">
                            <a href="modifier_salle.php?id=<?= $salle['id'] ?>" class="btn btn-primary">Modifier</a>
                            <a href="?supprimer=<?= $salle['id'] ?>" class="btn btn-danger" onclick="return confirm('Vraiment supprimer ?')">Supprimer</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <?php include '../includes/footer.php'; ?>

