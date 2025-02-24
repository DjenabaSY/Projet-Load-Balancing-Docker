<?php
session_start();
require 'db.php';
require 'functions.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$message = '';
$error = '';

// Récupérer les informations actuelles de l'utilisateur
$stmt = $pdo->prepare("SELECT email, username FROM users_test WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les préférences actuelles de notification
$stmt = $pdo->prepare("SELECT email_notifications, message_notifications FROM user_preferences WHERE user_id = ?");
$stmt->execute([$user_id]);
$preferences = $stmt->fetch(PDO::FETCH_ASSOC);
$email_notifications = $preferences['email_notifications'] ?? false;
$message_notifications = $preferences['message_notifications'] ?? true;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Vérifier le mot de passe actuel
        $stmt = $pdo->prepare("SELECT password FROM users_test WHERE id = ?");
        $stmt->execute([$user_id]);
        $stored_password = $stmt->fetchColumn();

        if (password_verify($current_password, $stored_password)) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users_test SET password = ? WHERE id = ?");
                if ($stmt->execute([$hashed_password, $user_id])) {
                    $message = "Mot de passe mis à jour avec succès.";
                    createNotification($pdo, $user_id, "Votre mot de passe a été changé.");
                } else {
                    $error = "Erreur lors de la mise à jour du mot de passe.";
                }
            } else {
                $error = "Les nouveaux mots de passe ne correspondent pas.";
            }
        } else {
            $error = "Mot de passe actuel incorrect.";
        }
    } elseif (isset($_POST['update_email'])) {
        $new_email = $_POST['new_email'];

        if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $pdo->prepare("UPDATE users_test SET email = ? WHERE id = ?");
            if ($stmt->execute([$new_email, $user_id])) {
                $message = "Adresse e-mail mise à jour avec succès.";
                $_SESSION['email'] = $new_email;
                createNotification($pdo, $user_id, "Votre adresse e-mail a été mise à jour.");
            } else {
                $error = "Erreur lors de la mise à jour de l'adresse e-mail.";
            }
        } else {
            $error = "Adresse e-mail invalide.";
        }
    } elseif (isset($_POST['update_notifications'])) {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $message_notifications = isset($_POST['message_notifications']) ? 1 : 0;

        $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id, email_notifications, message_notifications) 
                               VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE email_notifications = ?, message_notifications = ?");
        if ($stmt->execute([$user_id, $email_notifications, $message_notifications, $email_notifications, $message_notifications])) {
            $message = "Préférences de notification mises à jour.";
        } else {
            $error = "Erreur lors de la mise à jour des préférences de notification.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Paramètres</h1>
    </header>

    <nav>
        <a href="dashboard.php">Tableau de bord</a>
        <a href="profile.php">Profil</a>
        <a href="messages.php">Messages</a>
        <a href="logout.php">Déconnexion</a>
    </nav>

    <main>
        <?php if ($message): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <section>
            <h2>Changer le mot de passe</h2>
            <form method="POST">
                <input type="password" name="current_password" placeholder="Mot de passe actuel" required>
                <input type="password" name="new_password" placeholder="Nouveau mot de passe" required>
                <input type="password" name="confirm_password" placeholder="Confirmer le nouveau mot de passe" required>
                <button type="submit" name="change_password">Changer le mot de passe</button>
            </form>
        </section>

        <section>
            <h2>Mettre à jour l'adresse e-mail</h2>
            <form method="POST">
                <input type="email" name="new_email" placeholder="Nouvelle adresse e-mail" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <button type="submit" name="update_email">Mettre à jour l'e-mail</button>
            </form>
        </section>

        <section>
            <h2>Préférences de notification</h2>
            <form method="POST">
                <label>
                    <input type="checkbox" name="message_notifications" value="1" <?php echo $message_notifications ? 'checked' : ''; ?>> Recevoir des notifications par message sur le site
                </label>
                <br>
                <label>
                    <input type="checkbox" name="email_notifications" value="1" <?php echo $email_notifications ? 'checked' : ''; ?>> Recevoir des notifications par e-mail
                </label>
                <button type="submit" name="update_notifications">Mettre à jour les préférences</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Votre Application. Tous droits réservés.</p>
    </footer>
</body>
</html>
