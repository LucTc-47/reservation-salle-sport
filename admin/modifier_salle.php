<?php
require_once "../config/db.php";
include 'auth.php';  // Vérifie que l'utilisateur est bien administrateur

$message = "";

// Vérifie qu'un ID est bien passé
if (!isset($_GET['id'])) {
    header("Location: salles.php");
    exit();
}

$id = intval($_GET['id']);

// Récupération de la salle existante
$stmt = $pdo->prepare("SELECT * FROM sports_halls WHERE id = ?");
$stmt->execute([$id]);
$salle = $stmt->fetch();

if (!$salle) {
    $message = "Salle introuvable.";
} else {
    // Traitement du formulaire de modification
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nom = trim($_POST["nom"]);
        $description = trim($_POST["description"]);

        // Si une nouvelle image est envoyée
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {
            $image = $_FILES["image"];
            $image_name = $image["name"];
            $image_tmp = $image["tmp_name"];
            // Sécurisation du nom du fichier
            $image_name = preg_replace("/[^a-zA-Z0-9_\-\.]/", "", $image_name);
            $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
            $image_new_name = uniqid() . "." . $image_ext;
            $image_path = "../uploads/" . $image_new_name;

            if (move_uploaded_file($image_tmp, $image_path)) {
                // Mise à jour avec la nouvelle image
                $stmt = $pdo->prepare("UPDATE sports_halls SET nom = ?, description = ?, image = ? WHERE id = ?");
                if ($stmt->execute([$nom, $description, $image_new_name, $id])) {
                    $message = "Salle mise à jour avec succès (image modifiée).";
                } else {
                    $message = "Erreur lors de la mise à jour en base.";
                }
            } else {
                $message = "Erreur lors du téléchargement de la nouvelle image.";
            }
        } else {
            // Mise à jour sans modifier l'image
            $stmt = $pdo->prepare("UPDATE sports_halls SET nom = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$nom, $description, $id])) {
                $message = "Salle mise à jour avec succès.";
            } else {
                $message = "Erreur lors de la mise à jour de la salle.";
            }
        }
        
        // Recharge les informations mises à jour
        $stmt = $pdo->prepare("SELECT * FROM sports_halls WHERE id = ?");
        $stmt->execute([$id]);
        $salle = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une Salle</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Réservation de Salle de Sport - Espace Admin</h1>
        <nav>
            <a href="dashboard.php">Tableau de Bord</a> |
            <a href="salles.php">Gérer les Salles</a> |
            <a href="deconnexion_admin.php">Déconnexion</a>
        </nav>
    </header>
    <main class="auth-container">
        <div class="auth-card">
            <h2>Modifier la Salle</h2>
            <?php if ($message): ?>
                <div class="alert <?php echo (strpos($message, "succès") !== false) ? "alert-success" : "alert-error"; ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($salle): ?>
                <form method="post" enctype="multipart/form-data" class="auth-form">
                    <div class="form-group">
                        <label for="nom">Nom :</label>
                        <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($salle['nom']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="description">Description :</label>
                        <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($salle['description']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <p>Image actuelle :</p>
                        <img src="../uploads/<?= htmlspecialchars($salle['image']) ?>" alt="Image de <?= htmlspecialchars($salle['nom']) ?>" width="150">
                    </div>
                    <div class="form-group">
                        <label for="image">Nouvelle image (facultatif) :</label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Enregistrer les modifications</button>
                </form>
            <?php endif; ?>
            <p><a href="salles.php">⬅️ Retour à la liste des salles</a></p>
        </div>
    </main>
    <footer>
        <p>&copy; <?= date("Y") ?> Réservation de Salle de Sport</p>
    </footer>
</body>
</html>
