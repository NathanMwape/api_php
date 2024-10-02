<?php
// Connexion à la base de données MySQL
$host = 'localhost';
$dbname = 'live_locator';
$username = 'root'; // Identifiant par défaut
$password = '';     // Mot de passe par défaut

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["error" => "Erreur de connexion à la base de données: " . $e->getMessage()]));
}



