<?php
session_start();
session_regenerate_id(true);

require_once 'config.php';
require 'db.php';

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $stmt = $pdo->prepare("SELECT * FROM users_test WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user"] = $user["username"];
        $_SESSION["email"] = $user["email"];
        $logger->info('Connexion réussie pour l\'utilisateur: ' . $username);
        header("Location: dashboard.php");
        exit;
    } else {
        $error_message = "Identifiants incorrects.";
        $logger->warning('Tentative de connexion échouée pour l\'utilisateur: ' . $username);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <form method="POST" autocomplete="off">
            <p>Réponse du serveur : <?php echo htmlspecialchars(gethostname()); ?></p>
            <h2>Connexion</h2>
            <?php
            if (isset($error_message)) {
                echo "<p class='error'>$error_message</p>";
            }
            ?>
            <input type="text" name="username" placeholder="Nom d'utilisateur" required autocomplete="off">
            <input type="password" name="password" placeholder="Mot de passe" required autocomplete="new-password">
            <button type="submit">Se connecter</button>
            <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
        </form>
    </div>

    <footer>
        <p>Mon application 2025</p>
        
    </footer>
</body>
</html>
