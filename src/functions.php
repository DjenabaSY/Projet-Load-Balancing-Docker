<?php
function createNotification($pdo, $user_id, $content) {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, content, is_read) VALUES (?, ?, FALSE)");
    $result = $stmt->execute([$user_id, $content]);
    error_log("Création de notification pour l'utilisateur $user_id : " . ($result ? "Réussie" : "Échouée"));
    return $result;
}

function getUserProfile($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("Récupération du profil pour l'utilisateur $user_id : " . ($result ? "Réussie" : "Échouée"));
    return $result;
}

function updateUserProfile($pdo, $user_id, $bio, $avatar_url) {
    $stmt = $pdo->prepare("INSERT INTO user_profiles (user_id, bio, avatar_url) 
                           VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE bio = ?, avatar_url = ?");
    $result = $stmt->execute([$user_id, $bio, $avatar_url, $bio, $avatar_url]);
    error_log("Mise à jour du profil pour l'utilisateur $user_id : " . ($result ? "Réussie" : "Échouée"));
    return $result;
}

function getUnreadNotificationsCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function getUnreadMessagesCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND is_new = TRUE");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function getRecentActivities($pdo, $user_id, $limit = 5) {
    $query = "
    (SELECT 'message_sent' as type, created_at, CONCAT('Vous avez envoyé un message à ', (SELECT username FROM users_test WHERE id = messages.recipient_id)) as description FROM messages WHERE sender_id = ? AND is_deleted = FALSE)
    UNION
    (SELECT 'profile_update' as type, updated_at as created_at, 'Vous avez mis à jour votre profil' as description FROM user_profiles WHERE user_id = ?)
    UNION
    (SELECT 'notification' as type, created_at, content as description FROM notifications WHERE user_id = ?)
    ORDER BY created_at DESC
    LIMIT ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $user_id, $user_id, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


?>
