<?php
session_start();
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user"]) || !isset($_SESSION["email"])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

require 'db.php';
require 'functions.php';
$userProfile = getUserProfile($pdo, $_SESSION['user_id']);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?= htmlspecialchars($_SESSION["user"]); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <header>
        <h1>Profil Utilisateur</h1>
    </header>

    <nav class="navbar">
        <a href="dashboard.php">Tableau de bord</a>
        <a href="profile.php">Mon Profil</a>
        <a href="messages.php">Messages</a>
        <a href="settings.php">Paramètres</a>
        <a href="logout.php">Déconnexion</a>
    </nav>

    <div class="content">
        <div class="profile-section">
            <div class="card">
                <h2>Profil de <?= htmlspecialchars($_SESSION["user"]); ?></h2>
                <div class="profile-info">
                    <p><strong>Nom d'utilisateur :</strong> <?= htmlspecialchars($_SESSION["user"]); ?></p>
                    <p><strong>Adresse e-mail :</strong> <?= htmlspecialchars($_SESSION["email"]); ?></p>
                    <p><strong>Bio :</strong> <?= htmlspecialchars($userProfile['bio'] ?? 'Pas de biographie') ?></p>
                    <?php if (!empty($userProfile['avatar_url'])): ?>
                        <img src="<?= htmlspecialchars($userProfile['avatar_url']) ?>" alt="Avatar">
                    <?php endif; ?>

                </div>
            </div>

            <div class="card">
                <h3>Actions du profil</h3>
                <ul class="profile-actions">
                    <li><a href="edit_profile.php"><i class="fas fa-user-edit"></i> Modifier le profil</a></li>
                    <li><a href="change_password.php"><i class="fas fa-key"></i> Changer le mot de passe</a></li>
          
                </ul>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Mon Application. Tous droits réservés.</p>
    </footer>
</body>
</html>
