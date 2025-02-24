<?php
require_once 'config.php';

// Définition des constantes pour les informations de connexion
define('DB_HOST', 'db');
define('DB_USER', 'user');
define('DB_PASS', 'user_password');
define('DB_NAME', 'app_db');

try {
    // Création de la connexion PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    global $logger;
    $logger->info('Connexion à la base de données réussie');
} catch (PDOException $e) {
    global $logger;
    $logger->error('Erreur de connexion à la base de données : ' . $e->getMessage());
    die("Une erreur est survenue lors de la connexion à la base de données. Veuillez réessayer plus tard.");
}

// Fonction pour obtenir une instance de PDO (utile si vous avez besoin de multiples connexions)
function getPDO() {
    global $pdo;
    return $pdo;
}
