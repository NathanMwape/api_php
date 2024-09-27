<?php
// Afficher les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
include 'db.php';

// Définir les en-têtes pour autoriser les requêtes CORS
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Vérifier le type de requête
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Ajouter un log pour déboguer l'URL
file_put_contents('log.txt', "Requête: $requestMethod $requestUri\n", FILE_APPEND);

// Route pour récupérer les positions d'un utilisateur spécifique (GET)
if ($requestMethod === 'GET' && strpos($requestUri, '/position') !== false) {
    if (isset($_GET['utilisateur_id'])) {
        $utilisateurId = $_GET['utilisateur_id'];

        // Préparer la requête SQL pour récupérer les positions
        $stmt = $pdo->prepare("SELECT latitude, longitude FROM positions WHERE utilisateur_id = ? ORDER BY timestamp DESC");
        $stmt->execute([$utilisateurId]);
        $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Vérifier s'il y a des positions pour cet utilisateur
        if ($positions) {
            echo json_encode($positions); // Retourner les positions en JSON
        } else {
            echo json_encode([]);
        }
    } else {
        echo json_encode(["message" => "ID d'utilisateur manquant"]);
    }
}

if ($requestMethod === 'POST' && strpos($requestUri, '/positions') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['utilisateurId']) && isset($data['latitude']) && isset($data['longitude'])) {
        $utilisateurId = $data['utilisateurId'];
        $latitude = $data['latitude'];
        $longitude = $data['longitude'];

        $stmt = $pdo->prepare("INSERT INTO positions (utilisateur_id, latitude,longitude) VALUES (?, ?, ?)");
        if ($stmt->execute([$utilisateurId, $latitude, $longitude])) {
            echo json_encode(["message" => "Position enregistrée avec succès"]);
        } else {
            echo json_encode(["message" => "Échec de l'enregistrement de la position"]);
        }
    } else {
        echo json_encode(["message" => "Données incomplètes pour la position"]);
    }
}


if ($requestMethod === 'POST' && strpos($requestUri, '/registre') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['nom']) && isset($data['mot_de_passe'])) {
        $nom = $data['nom'];
        $mot_de_passe = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, mot_de_passe) VALUES (?, ?)");
        if ($stmt->execute([$nom, $mot_de_passe])) {
            echo json_encode(["message" => "Utilisateur créé avec succès"]);
        } else {
            echo json_encode(["message" => "Échec de la création de l'utilisateur"]);
        }
    } else {
        echo json_encode(["message" => "Données incomplètes pour la création de l'utilisateur"]);
    }
}


if ($requestMethod === 'POST' && strpos($requestUri, '/login') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['nom']) && isset($data['mot_de_passe'])) {
        $nom = $data['nom'];
        $mot_de_passe = $data['mot_de_passe'];

        // Vérification des identifiants
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE nom = ?");
        $stmt->execute([$nom]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($utilisateur && $utilisateur['mot_de_passe'] === $mot_de_passe) {
            echo json_encode([
                "message" => "Connexion réussie et position enregistrée",
                "utilisateurId" => $utilisateur['id'],
                "nom" => $utilisateur['nom']
            ]);
        } else {
            echo json_encode(["message" => "Nom d'utilisateur ou mot de passe incorrect"]);
        }
    } else {
        echo json_encode(["message" => "Données incomplètes pour la connexion"]);
    }
}


// Route pour gérer la création d'utilisateur, la connexion et l'enregistrement de position (POST)
// elseif ($requestMethod === 'POST') {
//     $data = json_decode(file_get_contents('php://input'), true);

//     // Route pour créer un utilisateur (register)
//     if ($requestUri === '/register') {
//         if (isset($data['nom']) && isset($data['mot_de_passe'])) {
//             $nom = $data['nom'];
//             $mot_de_passe = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT);

//             $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, mot_de_passe) VALUES (?, ?)");
//             if ($stmt->execute([$nom, $mot_de_passe])) {
//                 echo json_encode(["message" => "Utilisateur créé avec succès"]);
//             } else {
//                 echo json_encode(["message" => "Échec de la création de l'utilisateur"]);
//             }
//         } else {
//             echo json_encode(["message" => "Données incomplètes pour la création de l'utilisateur"]);
//         }
//     }

//     // Route pour connecter l'utilisateur (login)
//     elseif ($requestUri === '/login') {
//         if (isset($data['nom']) && isset($data['mot_de_passe'])) {
//             $nom = $data['nom'];
//             $mot_de_passe = $data['mot_de_passe'];

//             // Vérification des identifiants
//             $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE nom = ?");
//             $stmt->execute([$nom]);
//             $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

//             if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
//                 echo json_encode([
//                     "message" => "Connexion réussie",
//                     "utilisateurId" => $utilisateur['id'],
//                     "nom" => $utilisateur['nom']
//                 ]);
//             } else {
//                 echo json_encode(["message" => "Nom d'utilisateur ou mot de passe incorrect"]);
//             }
//         } else {
//             echo json_encode(["message" => "Données incomplètes pour la connexion"]);
//         }
//     }

//     // Route pour enregistrer la position de l'utilisateur (position)
//     elseif ($requestUri === '/position') {
//         if (isset($data['utilisateurId']) && isset($data['latitude']) && isset($data['longitude'])) {
//             $utilisateurId = $data['utilisateurId'];
//             $latitude = $data['latitude'];
//             $longitude = $data['longitude'];

//             $stmt = $pdo->prepare("INSERT INTO positions (utilisateur_id, latitude,longitude) VALUES (?, ?, ?)");
//             if ($stmt->execute([$utilisateurId, $latitude, $longitude])) {
//                 echo json_encode(["message" => "Position enregistrée avec succès"]);
//             } else {
//                 echo json_encode(["message" => "Échec de l'enregistrement de la position"]);
//             }
//         } else {
//             echo json_encode(["message" => "Données incomplètes pour la position"]);
//         }
//     }
// } else {
//     echo json_encode(["message" => "Méthode non supportée"]);
// }// Rechercher l'utilisateur dans la base de données