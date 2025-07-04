<?php
session_start();
require_once "config/db.php";

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php?redirect=salles.php");
    exit();
}

try {
    $stmt = $pdo->query("SELECT * FROM sports_halls ORDER BY nom ASC");
    $salles = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur de chargement des salles : " . $e->getMessage());
}
?>

<?php include 'includes/header.php'; ?>

<main>
    <h2>Toutes les Salles de Sport</h2>

    <?php if (empty($salles)): ?>
        <p>Aucune salle disponible pour le moment.</p>
    <?php else: ?>
        <div class="salles-grid">
            <?php foreach ($salles as $salle): ?>
                <div class="salle-card">
                    <div class="salle-image-container">
                        <img src="uploads/<?= htmlspecialchars($salle['image']) ?>" 
                             alt="<?= htmlspecialchars($salle['nom']) ?>">
                    </div>
                    <h3><?= htmlspecialchars($salle['nom']) ?></h3>
                    <p><?= nl2br(htmlspecialchars($salle['description'])) ?></p>
                    <a href="reserver.php?salle_id=<?= $salle['id'] ?>" class="btn btn-primary">Réserver</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
