<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}
require 'db.php';
require 'functions.php';


error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'non défini'));

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipient_username = trim($_POST['recipient']);
    $content = trim($_POST['content']);

    if (empty($recipient_username) || empty($content)) {
        $error = "Tous les champs sont requis.";
    } else {
        // Vérifier si le destinataire existe
        $stmt = $pdo->prepare("SELECT id FROM users_test WHERE username = ?");
        $stmt->execute([$recipient_username]);
        $recipient = $stmt->fetch();

        if ($recipient) {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, recipient_id, sender_name, content, is_new) VALUES (?, ?, ?, ?, TRUE)");
            if ($stmt->execute([$_SESSION['user_id'], $recipient['id'], $_SESSION['user'], $content])) {
                error_log("Message inséré avec succès. sender_id: " . $_SESSION['user_id'] . ", recipient_id: " . $recipient['id']);
                $success = "Message envoyé avec succès.";
                createNotification($pdo, $_SESSION['user_id'], "Vous avez envoyé un message à " . $recipient_username);
            } else {
                error_log("Échec de l'insertion du message. sender_id: " . $_SESSION['user_id'] . ", recipient_id: " . $recipient['id']);
                $error = "Erreur lors de l'envoi du message.";
            }
        } else {
            $error = "Destinataire non trouvé.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Message</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Nouveau Message</h2>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="recipient" placeholder="Nom d'utilisateur du destinataire" required>
            <textarea name="content" placeholder="Votre message" required></textarea>
            <button type="submit">Envoyer</button>
        </form>
        <a href="messages.php">Retour aux messages</a>
    </div>
</body>
</html>
