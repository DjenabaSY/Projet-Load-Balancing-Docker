<?php
session_start();
require 'db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Marquer toutes les notifications comme lues
$stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);


// Récupérer les notifications de l'utilisateur
function getNotifications($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$notifications = getNotifications($pdo, $_SESSION['user_id']);
error_log("Notifications récupérées : " . print_r($notifications, true));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Notifications</h1>
    </header>

    <nav>
        <a href="dashboard.php">Tableau de bord</a>
        <a href="messages.php">Messages</a>
        <a href="profile.php">Profil</a>
        <a href="logout.php">Déconnexion</a>
    </nav>

    <main>
        <?php if (empty($notifications)): ?>
            <p>Vous n'avez pas de nouvelles notifications.</p>
        <?php else: ?>
            <ul class="notifications-list">
                <?php foreach ($notifications as $notification): ?>
                    <li class="notification-item">
                        <p><?php echo htmlspecialchars($notification['content']); ?></p>
                        <small>
                            <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?>
                        </small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 Votre Application. Tous droits réservés.</p>
    </footer>
</body>
</html>
