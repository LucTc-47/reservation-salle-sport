
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réservation Salles de Sport</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<header>
  <h1>
    <span>Fit</span><span>Reserve</span>
  </h1>
  <p>Réservez facilement votre salle de sport en ligne</p>
  <nav>
    <?php if (isset($_SESSION['user_id'])) : ?>
      Bonjour <?= htmlspecialchars($_SESSION['nom']) ?> |
      <a href="index.php"><i class="fas fa-home"></i> Accueil</a> |
      <a href="salles.php"><i class="fas fa-building"></i> Salles</a> |
      <a href="mes_reservations.php"><i class="fas fa-calendar-alt"></i> Mes Réservations</a>
      <?php if ($_SESSION['role'] === 'admin') : ?> |
        <a href="admin/dashboard.php"><i class="fas fa-cog"></i> Administration</a>
      <?php endif; ?> |
      <a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    <?php else : ?>
      <a href="index.php"><i class="fas fa-home"></i> Accueil</a> |
      <a href="inscription.php"><i class="fas fa-user-plus"></i> Inscription</a> |
      <a href="connexion.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
    <?php endif; ?>
  </nav>
</header>

<main class="container">