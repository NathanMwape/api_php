<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'clients';
$username = 'root';  // Par défaut pour XAMPP
$password = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die(json_encode(["error" => "Erreur de connexion à la base de données: " . $e->getMessage()]));
}

// Définir les en-têtes pour autoriser les requêtes CORS
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Vérifier le type de requête
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'GET') {
  // Si un ID est fourni dans l'URL, récupère un client spécifique, sinon tous les clients
  if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM client WHERE id = ?");
    $stmt->execute([$id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($client) {
      echo json_encode($client);
    } else {
      echo json_encode(["message" => "Client non trouvé"]);
    }
  } else {
    // Récupère tous les clients
    $stmt = $pdo->prepare("SELECT * FROM client");
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($clients);
  }
} elseif ($requestMethod === 'POST') {
  // Récupérer les données de la requête POST
  $data = json_decode(file_get_contents('php://input'), true);

  // Validation des données
  if (isset($data['nom']) && isset($data['prenom']) && isset($data['age'])) {
    $nom = $data['nom'];
    $prenom = $data['prenom'];
    $age = $data['age'];

    $stmt = $pdo->prepare("INSERT INTO client (nom, prenom, age) VALUES (?, ?, ?)");
    if ($stmt->execute([$nom, $prenom, $age])) {
      echo json_encode(["message" => "Client créé avec succès"]);
    } else {
      echo json_encode(["message" => "Échec de la création du client"]);
    }
  } else {
    echo json_encode(["message" => "Données incomplètes"]);
  }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $stmt = $pdo->prepare("SELECT * FROM client");
  $stmt->execute();
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Renvoyer les résultats sous forme de JSON
  echo json_encode($users);
} else {
  // Si la méthode de requête n'est ni GET ni POST
  echo json_encode(["message" => "Méthode de requête non supportée"]);
}



if ($requestMethod === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  // Validation des données
  if (isset($data['client_id']) && isset($data['longitude']) && isset($data['latitude'])) {
      $client_id = $data['client_id'];
      $longitude = $data['longitude'];
      $latitude = $data['latitude'];

      $stmt = $pdo->prepare("INSERT INTO locations (client_id, longitude, latitude) VALUES (?, ?, ?)");
      if ($stmt->execute([$client_id, $longitude, $latitude])) {
          echo json_encode(["message" => "Localisation enregistrée avec succès"]);
      } else {
          echo json_encode(["message" => "Échec de l'enregistrement de la localisation"]);
      }
  } else {
      echo json_encode(["message" => "Données incomplètes"]);
  }
} else {
  echo json_encode(["message" => "Méthode de requête non supportée"]);
}
