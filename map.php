<?php
// Inclusion du fichier de connexion
require_once 'db.php';

// Récupération de l'ID de l'utilisateur passé dans l'URL
if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    // Récupérer toutes les positions de l'utilisateur
    $sql = "SELECT u.nom, p.latitude, p.longitude
            FROM utilisateurs u
            LEFT JOIN positions p ON u.id = p.utilisateur_id
            WHERE u.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$positions || empty($positions[0]['latitude']) || empty($positions[0]['longitude'])) {
        die("Position non trouvée pour cet utilisateur.");
    }
    $userName = $positions[0]['nom'];
} else {
    die("ID d'utilisateur manquant.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carte de <?= htmlspecialchars($userName) ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            width: 100%;
            margin-top: 10px;
        }
        h2 {
            text-align: center;
            font-size: 2rem;
            color: #007bff;
            margin-bottom: 20px;
        }
        #map {
            height: 550px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }
        .btn-custom {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .fa-map-marker-alt, .fa-arrow-left {
            margin-right: 5px;
        }
        .back-button {
            background-color: #6c757d;
            color: white;
        }
        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

    <div class="container shadow p-4 bg-white rounded">
        <h2><i class="fa-solid fa-map-marker-alt"></i> Localisation de <?= htmlspecialchars($userName) ?></h2>

        <!-- Conteneur pour la carte -->
        <div id="map"></div>

        <!-- Bouton Retour -->
        <a href="localisation.php" class="btn btn-custom btn-secondary back-button mt-3">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <!-- Inclusion de Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Récupération des positions PHP dans un tableau JavaScript
        var positions = <?= json_encode($positions) ?>;

        // Vérification de l'existence des positions
        if (positions.length > 0) {
            // Initialisation de la carte Leaflet avec la première position de l'utilisateur
            var map = L.map('map').setView([positions[0].latitude, positions[0].longitude], 13);

            // Chargement de la couche de tuiles OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Création d'un tableau pour les coordonnées du polyligne
            var latlngs = positions.map(function(pos) {
                return [pos.latitude, pos.longitude];
            });

            // Ajout du polyligne à la carte
            var polyline = L.polyline(latlngs, {color: 'blue'}).addTo(map);

            // Adapter la vue de la carte pour inclure toutes les positions
            map.fitBounds(polyline.getBounds());

            // Ajouter un marqueur sur la dernière position (position actuelle)
            var lastPosition = positions[positions.length - 1];
            L.marker([lastPosition.latitude, lastPosition.longitude]).addTo(map)
                .bindPopup("Position actuelle de <?= htmlspecialchars($userName) ?>")
                .openPopup();

            // Si plus d'une position existe, ajouter un marqueur sur l'avant-dernière position
            if (positions.length > 1) {
                var secondLastPosition = positions[positions.length - 2];
                L.marker([secondLastPosition.latitude, secondLastPosition.longitude]).addTo(map)
                    .bindPopup("Dernière position de <?= htmlspecialchars($userName) ?>");
            }
        } else {
            alert("Aucune position disponible pour cet utilisateur.");
        }
    </script>

</body>
</html>
