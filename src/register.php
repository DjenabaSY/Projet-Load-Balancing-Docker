<?php
require_once 'config.php';
require 'db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    $logger->info("Tentative d'inscription", ['username' => $username, 'email' => $email]);

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "Tous les champs sont requis.";
        $logger->warning("Tentative d'inscription avec des champs vides");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format d'email invalide.";
        $logger->warning("Tentative d'inscription avec un email invalide", ['email' => $email]);
    } elseif ($password !== $confirm_password) {
        $message = "Les mots de passe ne correspondent pas.";
        $logger->warning("Tentative d'inscription avec des mots de passe non correspondants");
    } elseif (strlen($password) < 8) {
        $message = "Le mot de passe doit contenir au moins 8 caractères.";
        $logger->warning("Tentative d'inscription avec un mot de passe trop court");
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users_test (username, email, password) VALUES (?, ?, ?)");
        
        try {
            $stmt->execute([$username, $email, $hashed_password]);
            $message = "Inscription réussie ! ";
            $logger->info("Inscription réussie", ['username' => $username, 'email' => $email]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $message = "Ce nom d'utilisateur ou cet email est déjà utilisé.";
                $logger->warning("Tentative d'inscription avec un nom d'utilisateur ou email déjà utilisé", ['username' => $username, 'email' => $email]);
            } else {
                $message = "Erreur lors de l'inscription : " . $e->getMessage();
                $logger->error("Erreur lors de l'inscription", ['error' => $e->getMessage(), 'username' => $username, 'email' => $email]);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <form method="POST" autocomplete="off">
            <p>Réponse du serveur : <?php echo htmlspecialchars(gethostname()); ?></p>
            <h2>Inscription</h2>
            <?php if ($message): ?>
                <p class="message"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <input type="text" name="username" placeholder="Nom d'utilisateur" required autocomplete="off">
            <input type="email" name="email" placeholder="Email" required autocomplete="off">
            <input type="password" name="password" placeholder="Mot de passe" required autocomplete="new-password" minlength="8">
            <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required autocomplete="new-password" minlength="8">
            <button type="submit">S'inscrire</button>
            <p>Déjà un compte ? <a href="index.php">Se connecter</a></p>
        </form>
    </div>

    <footer>
        <p>Mon application 2025</p>
    </footer>
</body>
</html>
