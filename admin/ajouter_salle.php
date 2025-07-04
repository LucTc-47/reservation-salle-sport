<?php
// Démarrage et contrôle de la session admin
include 'auth.php';
include '../config/db.php';

$message = "";

// Traitement du formulaire à la soumission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupération des données du formulaire
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);

    // Vérification du fichier image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];
        // On sécurise le nom de fichier
        $image = preg_replace("/[^a-zA-Z0-9_\-\.]/", "", $image);
        $dest = "../uploads/" . basename($image);

        // Déplacement de l'image et insertion en base si succès
        if (move_uploaded_file($tmp, $dest)) {
            $stmt = $pdo->prepare("INSERT INTO sports_halls (nom, description, image) VALUES (?, ?, ?)");
            if ($stmt->execute([$nom, $description, $image])) {
                $message = "Salle ajoutée avec succès !";
            } else {
                $message = "Erreur lors de l'enregistrement en base.";
            }
        } else {
            $message = "Erreur lors du téléchargement de l’image.";
        }
    } else {
        $message = "Veuillez sélectionner une image valide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Ajouter une Salle</title>
    <!-- On référence la feuille de style principale -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Navigation et en-tête pour l'espace admin -->
    <header>
        <h1> <span style="color:#000;background:rgba(255,255,255,0.8);padding:5px 10px;border-radius:8px;">Fit</span><span style="color:#28a745">Reserve</span> - Espace Admin</h1>
        <nav>
            <a href="dashboard.php">Tableau de Bord</a> |
            <a href="salles.php">Gérer les Salles</a> |
            <a href="deconnexion_admin.php">Déconnexion</a>
        </nav>
    </header>

    <main class="auth-container">
        <div class="auth-card">
            <h2>Ajouter une Salle de Sport</h2>

            <?php if ($message): ?>
                <div class="alert <?php echo (strpos($message, "succès") !== false) ? "alert-success" : "alert-error"; ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="auth-form">
                <div class="form-group">
                    <label for="nom">Nom :</label>
                    <input type="text" id="nom" name="nom" required placeholder="Nom de la salle">
                </div>

                <div class="form-group">
                    <label for="description">Description :</label>
                    <textarea id="description" name="description" required placeholder="Description de la salle" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label for="image">Image :</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Ajouter</button>
            </form>

            <p><a href="dashboard.php">⬅️ Retour au Tableau de Bord</a></p>
        </div>
    </main>

<?php include '../includes/footer.php'; ?>
