<?php
session_start();
require 'db.php';
require 'functions.php';

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user"]) || !isset($_SESSION["email"])) {
    header("Location: index.php");
    exit;
}

$message = '';
$userProfile = getUserProfile($pdo, $_SESSION['user_id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = trim($_POST['username']);
    $newEmail = trim($_POST['email']);
    $newBio = trim($_POST['bio']);
    $newAvatarUrl = trim($_POST['avatar_url']);
    
    if (empty($newUsername) || empty($newEmail)) {
        $message = "Le nom d'utilisateur et l'email sont requis.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Format d'email invalide.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE users_test SET username = ?, email = ? WHERE id = ?");
            $result = $stmt->execute([$newUsername, $newEmail, $_SESSION['user_id']]);

            if ($result) {
                $profileUpdated = updateUserProfile($pdo, $_SESSION['user_id'], $newBio, $newAvatarUrl);

                if ($profileUpdated) {
                    $pdo->commit();
                    $_SESSION['user'] = $newUsername;
                    $_SESSION['email'] = $newEmail;
                    $message = "Profil mis à jour avec succès.";
                    $messageClass = 'success';
                    createNotification($pdo, $_SESSION['user_id'], "Votre profil a été mis à jour avec succès.");
                    
                    
                } else {
                    $pdo->rollBack();
                    $message = "Erreur lors de la mise à jour du profil étendu.";
                    $messageClass = 'error';
                }
            } else {
                $pdo->rollBack();
                $message = "Erreur lors de la mise à jour du profil de base.";
                $messageClass = 'error';
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Erreur PDO : " . $e->getMessage());
            $message = "Erreur lors de la mise à jour du profil : " . $e->getMessage();
            $messageClass = 'error';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le profil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Modifier le profil</h1>
    </header>

    <nav class="navbar">
        <a href="dashboard.php">Tableau de bord</a>
        <a href="profile.php">Mon Profil</a>
        <a href="messages.php">Messages</a>
        <a href="settings.php">Paramètres</a>
        <a href="logout.php">Déconnexion</a>
    </nav>

    <div class="content">
        <div class="card">
            <?php if ($message): ?>
                <p class="message <?= $messageClass ?>"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <form method="POST">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($_SESSION['user']) ?>" required>

                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_SESSION['email']) ?>" required>

                <label for="bio">Biographie :</label>
                <textarea id="bio" name="bio"><?= htmlspecialchars($userProfile['bio'] ?? '') ?></textarea>

                <label for="avatar_url">URL de l'avatar :</label>
                <input type="text" id="avatar_url" name="avatar_url" value="<?= htmlspecialchars($userProfile['avatar_url'] ?? '') ?>">

                <button type="submit">Mettre à jour le profil</button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Mon Application. Tous droits réservés.</p>
    </footer>
</body>
</html>
