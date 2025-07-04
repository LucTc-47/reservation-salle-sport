<?php
session_start();
require_once "config/db.php";

$message = "";

// Paramètres de sécurité pour prévenir la force brute
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_login_attempt'] = 0;
}
$lock_time = 300; // 5 minutes en secondes
$max_attempts = 5;

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_SESSION['login_attempts'] < $max_attempts || time() - $_SESSION['last_login_attempt'] > $lock_time)) {
    // Réinitialiser les tentatives si le temps d'attente est écoulé
    if (time() - $_SESSION['last_login_attempt'] > $lock_time) {
        $_SESSION['login_attempts'] = 0;
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Vérification de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email invalide.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Réinitialisation des tentatives et régénération de l'ID de session
                $_SESSION['login_attempts'] = 0;
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_login'] = time();
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

                // Redirection selon le rôle de l'utilisateur
                $redirect = $user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php';
                if (!empty($_GET['redirect'])) {
                    $redirect = filter_var($_GET['redirect'], FILTER_SANITIZE_URL);
                }
                header("Location: " . $redirect);
                exit();
            } else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_login_attempt'] = time();
                $message = "Identifiants incorrects. Tentatives restantes : " . ($max_attempts - $_SESSION['login_attempts']);
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la connexion : " . $e->getMessage());
            $message = "Une erreur est survenue. Veuillez réessayer plus tard.";
        }
    }
} elseif ($_SESSION['login_attempts'] >= $max_attempts) {
    $remaining_time = $lock_time - (time() - $_SESSION['last_login_attempt']);
    $display_time = $remaining_time > 0 ? ceil($remaining_time / 60) : 0;
    $message = "Trop de tentatives échouées. Veuillez réessayer dans $display_time minutes.";
}
?>

<?php include 'includes/header.php'; ?>

<main class="auth-container">
    <div class="auth-card">
        <h2 class="auth-title">Connexion</h2>
        
        <?php if ($message): ?>
            <div class="alert <?php echo (strpos($message, "Trop de tentatives") !== false) ? "alert-warning" : "alert-error"; ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($_SESSION['login_attempts'] < $max_attempts || time() - $_SESSION['last_login_attempt'] > $lock_time): ?>
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email :</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                           autocomplete="email" autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe :</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                    <small class="text-right">
                        <a href="forgot_password.php">Mot de passe oublié ?</a>
                    </small>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
            </form>
        <?php endif; ?>
        
        <div class="auth-links">
            <p>Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>
            <p><a href="index.php">Retour à l'accueil</a></p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
