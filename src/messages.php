<?php
session_start();
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user"]) || !isset($_SESSION["email"])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}
require 'db.php';

error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'non défini'));

function getMessages($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT m.*, 
                CASE WHEN m.sender_id = ? THEN 'Envoyé' ELSE 'Reçu' END as message_type,
                CASE WHEN m.sender_id = ? THEN u_recipient.username ELSE u_sender.username END as other_user,
                u_sender.username as sender_username,
                u_recipient.username as recipient_username
            FROM messages m
            LEFT JOIN users_test u_sender ON m.sender_id = u_sender.id
            LEFT JOIN users_test u_recipient ON m.recipient_id = u_recipient.id
            WHERE m.sender_id = ? OR m.recipient_id = ?
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Messages récupérés pour l'utilisateur " . $user_id . ": " . print_r($messages, true));
        return $messages;
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des messages : " . $e->getMessage());
        return [];
    }
}

$messages = getMessages($pdo, $_SESSION["user_id"]);
// Marquer tous les messages reçus comme lus
$stmt = $pdo->prepare("UPDATE messages SET is_new = FALSE WHERE recipient_id = ?");
$stmt->execute([$_SESSION['user_id']]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Messages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <header>
        <h1>Mes Messages</h1>
    </header>

    <nav class="navbar">
        <a href="dashboard.php">Tableau de bord</a>
        <a href="profile.php">Mon Profil</a>
        <a href="messages.php">Messages</a>
        <a href="settings.php">Paramètres</a>
        <a href="logout.php">Déconnexion</a>
    </nav>

    <div class="content">
        <?php
        if (isset($_SESSION['message'])) {
            echo '<p class="' . ($_SESSION['message_type'] ?? 'success') . '">' . htmlspecialchars($_SESSION['message']) . '</p>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
        
        <a href="new_message.php" class="btn-new-message">Nouveau message</a>
        <a href="trash.php" class="btn-trash">Corbeille</a>

        
        <div class="messages-section">
            <?php if (empty($messages)): ?>
                <p>Vous n'avez pas de messages.</p>
            <?php else: ?>
                <ul class="message-list">
                    <?php foreach ($messages as $message): ?>
                        <li class="message-item <?= $message['message_type'] == 'Envoyé' ? 'sent' : 'received' ?>">
                            <div class="message-header">
                                <span class="sender"><?= $message['message_type'] == 'Envoyé' ? 'À: ' : 'De: ' ?><?= htmlspecialchars($message['other_user']) ?></span>
                                <span class="date"><?= htmlspecialchars($message['created_at']) ?></span>
                            </div>
                            <div class="message-content">
                                <?= htmlspecialchars($message['content']) ?>
                            </div>
                            <div class="message-actions">
                                <?php if ($message['message_type'] == 'Reçu'): ?>
                                    <a href="reply.php?id=<?= $message['id'] ?>" class="btn-reply">
                                        <i class="fas fa-reply"></i> Répondre
                                    </a>
                                <?php endif; ?>
                                <a href="delete_message.php?id=<?= $message['id'] ?>" class="btn-delete">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Mon Application. Tous droits réservés.</p>
    </footer>
</body>
</html>
