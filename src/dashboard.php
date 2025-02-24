<?php
// error_log("dashboard.php a été appelé - Méthode : " . $_SERVER['REQUEST_METHOD']); 
require_once 'config.php';
session_start();
// error_log("Session actuelle : " . print_r($_SESSION, true));

$logger->info("dashboard.php a été appelé - Méthode : " . $_SERVER['REQUEST_METHOD']);
$logger->debug("Session actuelle : " . print_r($_SESSION, true));


if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user"]) || !isset($_SESSION["email"])) {
    $logger->warning("Tentative d'accès au dashboard sans session valide");
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

require 'db.php';
require 'functions.php';

$unreadCount = getUnreadNotificationsCount($pdo, $_SESSION['user_id']);
$unreadMessagesCount = getUnreadMessagesCount($pdo, $_SESSION['user_id']);

// Récupérer les activités récentes
$recentActivities = getRecentActivities($pdo, $_SESSION['user_id']);

$logger->info("Dashboard chargé pour l'utilisateur : " . $_SESSION['user'], [
    'unread_notifications' => $unreadCount,
    'unread_messages' => $unreadMessagesCount,
    'recent_activities_count' => count($recentActivities)
]);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>

<body>
    <header>
        <h1>Tableau de bord</h1>
    </header>

    <nav class="navbar">
        <a href="dashboard.php">Accueil</a>
        <a href="profile.php">Mon Profil</a>
        <a href="messages.php" class="messages-link">
            Messages
            <?php if ($unreadMessagesCount > 0): ?>
                <span class="message-badge"><?= $unreadMessagesCount ?></span>
            <?php endif; ?>
        </a>
        <a href="settings.php">Paramètres</a>
        <a href="notifications.php" class="notifications-link">
            Notifications
            <?php if ($unreadCount > 0): ?>
                <span class="notification-badge"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
    </nav>

    <div class="content">
        <div class="dashboard-section">
            <h2>Bienvenue, <?= htmlspecialchars($_SESSION["user"]); ?> !</h2>

            <div class="card">
                <h3>Informations de l'utilisateur</h3>
                <p><strong>Nom d'utilisateur :</strong> <?= htmlspecialchars($_SESSION["user"]); ?></p>
                <p><strong>Adresse e-mail :</strong> <?= htmlspecialchars($_SESSION["email"]); ?></p>
            </div>

            <div class="card action-card">
                <h3>Vos actions disponibles</h3>
                <div class="action-grid">
                    <a href="profile.php" class="action-item">
                        <i class="fas fa-user"></i>
                        <span>Voir mon profil</span>
                    </a>
                    <a href="settings.php" class="action-item">
                        <i class="fas fa-cog"></i>
                        <span>Modifier mes paramètres</span>
                    </a>
                    <a href="messages.php" class="action-item">
                        <i class="fas fa-envelope"></i>
                        <span>Voir mes messages</span>
                    </a>
                    <a href="notifications.php" class="action-item">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                </div>
            </div>

            <div class="card">
                <h3>Activités récentes</h3>
                <ul class="activities-list">
                    <?php if (empty($recentActivities)): ?>
                        <li>Aucune activité récente.</li>
                    <?php else: ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <li><?= htmlspecialchars($activity['description']) ?> le <?= date('d/m/Y à H:i', strtotime($activity['created_at'])) ?>.</li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="card">
                <a href="logout.php" class="logout-link">Déconnexion</a>
            </div>
        </div>
    </div>
</body>

</html>
