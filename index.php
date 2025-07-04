<?php
session_start();
require_once "config/db.php";

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
$currentUser = $isLoggedIn ? htmlspecialchars($_SESSION['nom']) : '';

// Récupération des salles disponibles
try {
    $stmt = $pdo->query("SELECT * FROM sports_halls ORDER BY nom ASC");
    $salles = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur de chargement des salles.");
}
?>

<?php include 'includes/header.php'; ?>

<main>
    <?php if ($isLoggedIn): ?>
        <section class="welcome-message">
            <h2>Bonjour <?= $currentUser ?> !</h2>
            <p>Bienvenue sur votre espace de réservation de salles de sport.</p>
        </section>
    <?php endif; ?>

    <h2>Salles Disponibles</h2>
    <div class="salles-container">
        <?php if (empty($salles)): ?>
            <p>Aucune salle disponible pour le moment.</p>
        <?php else: ?>
            <div class="salles-grid">
                <?php foreach ($salles as $salle): ?>
                    <div class="salle-card">
                        <div class="salle-image-container">
                            <img src="uploads/<?= htmlspecialchars($salle['image'] ?? 'default.jpg') ?>" 
                                 alt="<?= htmlspecialchars($salle['nom']) ?>">
                        </div>
                        <h3><?= htmlspecialchars($salle['nom']) ?></h3>
                        <p><?= nl2br(htmlspecialchars($salle['description'])) ?></p>
                        <div class="salle-badge">Disponible</div>
                        <?php if ($isLoggedIn): ?>
                            <a href="reserver.php?salle_id=<?= (int)$salle['id'] ?>" class="btn btn-primary">Réserver</a>
                        <?php else: ?>
                            <p><a href="connexion.php">Connectez-vous pour réserver</a></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
