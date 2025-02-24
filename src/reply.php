<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrer la session
session_start();
error_log("Méthode de requête : " . $_SERVER['REQUEST_METHOD']);
error_log("Données POST : " . print_r($_POST, true));
error_log("Données GET : " . print_r($_GET, true));

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user"]) || !isset($_SESSION["email"])) {
    error_log("Session invalide. Redirection vers index.php");
    header("Location: index.php");
    exit;
}

// Inclure la connexion à la base de données et les fonctions
require 'db.php';
require 'functions.php';

// Vérifier la connexion à la base de données
if (!$pdo) {
    error_log("La connexion à la base de données a échoué.");
    die("Erreur de connexion à la base de données.");
}

$message_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
error_log("Message ID reçu : " . $message_id);
$error = '';

// Récupérer les détails du message original
try {
    $stmt = $pdo->prepare("
        SELECT m.*, u.username as sender_username 
        FROM messages m 
        JOIN users_test u ON m.sender_id = u.id 
        WHERE m.id = ? AND m.recipient_id = ?
    ");
    $stmt->execute([$message_id, $_SESSION['user_id']]);
    $original_message = $stmt->fetch();

    if (!$original_message) {
        error_log("Message original non trouvé. Redirection vers messages.php");
        header("Location: messages.php");
        exit;
    }
} catch (PDOException $e) {
    error_log("Erreur PDO lors de la récupération du message original : " . $e->getMessage());
    die("Erreur lors de la récupération du message.");
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("Formulaire soumis dans reply.php");

    // Récupérer les données du formulaire
    $content = trim($_POST['content'] ?? '');
    $recipient_id = intval($_POST['recipient_id'] ?? 0);

    error_log("Contenu : $content, Recipient ID : $recipient_id");

    // Valider les données
    if (!empty($content) && $recipient_id > 0) {
        try {
            // Insérer la réponse dans la base de données
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, recipient_id, sender_name, content, is_new) VALUES (?, ?, ?, ?, TRUE)");
            $result = $stmt->execute([$_SESSION['user_id'], $recipient_id, $_SESSION['user'], $content]);

            if ($result) {
                error_log("Message inséré avec succès.");
                $_SESSION['message'] = "Réponse envoyée avec succès.";
                $_SESSION['message_type'] = 'success';
                createNotification($pdo, $_SESSION['user_id'], "Vous avez répondu à un message de " . $original_message['sender_username']);

                // Rediriger vers messages.php
                header("Location: messages.php");
                exit;
            } else {
                $error = "Erreur lors de l'envoi du message.";
            }
        } catch (PDOException $e) {
            error_log("Erreur PDO : " . $e->getMessage());
            $error = "Erreur lors de l'envoi du message : " . $e->getMessage();
        }
    } else {
        error_log("Validation échouée : contenu vide ou destinataire invalide.");
        $error = "Le contenu ne peut pas être vide et le destinataire doit être valide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Répondre au message</title>
</head>
<body>
    <h1>Répondre au message</h1>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div>
        <p><strong>De :</strong> <?= htmlspecialchars($original_message['sender_username']) ?></p>
        <p><strong>Message original :</strong> <?= htmlspecialchars($original_message['content']) ?></p>
    </div>

    <form method="POST" action="reply.php?id=<?= htmlspecialchars($message_id) ?>">
        <p><strong>Répondre à :</strong> <?= htmlspecialchars($original_message['sender_username']) ?></p>
        <textarea name="content" rows="4" required></textarea>
        <input type="hidden" name="recipient_id" value="<?= htmlspecialchars($original_message['sender_id']) ?>">
        <button type="submit">Envoyer la réponse</button>
    </form>

    <a href="messages.php">Retour aux messages</a>
</body>
</html>
