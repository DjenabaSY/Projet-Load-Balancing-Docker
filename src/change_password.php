<?php
session_start();
require 'db.php';
require 'functions.php'; 

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$message = '';
$messageClass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Vérification du mot de passe actuel
    $stmt = $pdo->prepare("SELECT password FROM users_test WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (password_verify($currentPassword, $user['password'])) {
        if ($newPassword === $confirmPassword) {
            if (strlen($newPassword) >= 8) { // Ajout d'une vérification de longueur minimale
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users_test SET password = ? WHERE id = ?");
                if ($updateStmt->execute([$hashedPassword, $_SESSION['user_id']])) {
                    $message = "Mot de passe changé avec succès.";
                    $messageClass = 'success';
                    // Créer une notification
                    createNotification($pdo, $_SESSION['user_id'], "Votre mot de passe a été modifié avec succès.");
                } else {
                    $message = "Erreur lors du changement de mot de passe.";
                    $messageClass = 'error';
                }
            } else {
                $message = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
                $messageClass = 'error';
            }
        } else {
            $message = "Le nouveau mot de passe et sa confirmation ne correspondent pas.";
            $messageClass = 'error';
        }
    } else {
        $message = "Mot de passe actuel incorrect.";
        $messageClass = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Changer le mot de passe</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Changer le mot de passe</h1>
    </header>

    <nav class="navbar">
        <a href="dashboard.php">Tableau de bord</a>
        <a href="profile.php">Mon Profil</a>
        <a href="settings.php">Paramètres</a>
        <a href="notifications.php">Notifications</a>
    </nav>

    <div class="content">
        <div class="card">
            <?php if ($message): ?>
                <p class="message <?= $messageClass ?>"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <form method="POST">
                <label for="current_password">Mot de passe actuel :</label>
                <input type="password" id="current_password" name="current_password" required>

                <label for="new_password">Nouveau mot de passe :</label>
                <input type="password" id="new_password" name="new_password" required minlength="8">

                <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">

                <button type="submit">Changer le mot de passe</button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Mon Application. Tous droits réservés.</p>
    </footer>
</body>
</html>
