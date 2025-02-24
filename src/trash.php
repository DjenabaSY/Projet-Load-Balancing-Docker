<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

function getDeletedMessages($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT m.*, 
            CASE WHEN m.sender_id = ? THEN 'Envoyé' ELSE 'Reçu' END as message_type,
            CASE WHEN m.sender_id = ? THEN u_recipient.username ELSE u_sender.username END as other_user
        FROM messages m
        LEFT JOIN users_test u_sender ON m.sender_id = u_sender.id
        LEFT JOIN users_test u_recipient ON m.recipient_id = u_recipient.id
        WHERE (m.sender_id = ? OR m.recipient_id = ?) AND m.is_deleted = TRUE
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$deletedMessages = getDeletedMessages($pdo, $_SESSION["user_id"]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Corbeille</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Corbeille</h1>
    <nav>
        <a href="messages.php">Retour aux messages</a>
    </nav>
    <?php if (empty($deletedMessages)): ?>
        <p>La corbeille est vide.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($deletedMessages as $message): ?>
                <li>
                    <p><?= htmlspecialchars($message['message_type']) ?> à/de <?= htmlspecialchars($message['other_user']) ?></p>
                    <p>Reçu le : <?= date('d/m/Y H:i', strtotime($message['created_at'])) ?></p>
                    <p><?= htmlspecialchars($message['content']) ?></p>
                    <a href="restore_message.php?id=<?= $message['id'] ?>">Restaurer</a>
                    <a href="delete_permanently.php?id=<?= $message['id'] ?>">Supprimer définitivement</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
