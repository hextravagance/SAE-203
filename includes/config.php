<?php
$host = 'localhost';
$dbname = 'nicolas.rapuzzi';
$charset = 'utf8mb4';
$user = 'nicolas.rapuzzi';
$password = 'D]6LYvSr7hT40fF[';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Active les exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Mode de récupération par défaut
    PDO::ATTR_EMULATE_PREPARES => false, // Prépare les requêtes côté serveur
];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
