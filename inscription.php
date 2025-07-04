<?php
session_start();
require_once "config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération et nettoyage des données
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $nom = htmlspecialchars($nom);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email invalide.";
    } else {
        // Vérification si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $message = "Cet e-mail est déjà utilisé.";
        } else {
            // Hash du mot de passe et insertion dans la base avec rôle par défaut 'user'
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nom, email, password, role) VALUES (?, ?, ?, 'user')");
            if ($stmt->execute([$nom, $email, $hashedPassword])) {
                header("Location: connexion.php?inscription=success");
                exit();
            } else {
                $message = "Erreur lors de l'inscription.";
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<main class="auth-container">
    <div class="auth-card">
        <h2 class="auth-title">Inscription</h2>

        <?php if ($message): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" required 
                       value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required 
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
        </form>
        
        <div class="auth-links">
            <p>Déjà un compte ? <a href="connexion.php">Connectez-vous ici</a></p>
            <p><a href="index.php">Retour à l'accueil</a></p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
