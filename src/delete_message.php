<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$message_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($message_id > 0) {
    try {
        $stmt = $pdo->prepare("UPDATE messages SET is_deleted = TRUE WHERE id = ? AND (sender_id = ? OR recipient_id = ?)");
        $result = $stmt->execute([$message_id, $_SESSION['user_id'], $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['message'] = "Le message a été déplacé dans la corbeille.";
        } else {
            $_SESSION['message'] = "Aucun message n'a été trouvé ou vous n'êtes pas autorisé à le supprimer.";
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Erreur lors de la suppression du message : " . $e->getMessage();
    }
} else {
    $_SESSION['message'] = "ID de message invalide.";
}

header("Location: messages.php");
exit;
