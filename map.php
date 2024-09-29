<?php
// Inclusion du fichier de connexion
try {
    $pdo = new PDO('mysql:host=localhost;dbname=live_locator', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Erreur de connexion : ' . $e->getMessage();
}

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
    <!-- Inclusion de Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- Inclusion de Bootstrap CSS pour la mise en page -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
</head>
<body>
    <div class="container mt-2">
        <h2>Localisation de <?= htmlspecialchars($userName) ?></h2>
        <!-- Conteneur pour la carte -->
        <div id="map" style="height: 600px; width: 100%;"></div>
        <a href="localisation.php" class="btn btn-secondary mt-3">Retour à la liste</a>
    </div>

    <!-- Inclusion de Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Récupération des positions PHP dans un tableau JavaScript
        var positions = <?= json_encode($positions) ?>;

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

        // Ajouter des marqueurs pour chaque position
        positions.forEach(function(pos) {
            L.marker([pos.latitude, pos.longitude]).addTo(map)
                .bindPopup("Position de <?= htmlspecialchars($userName) ?>")
                .openPopup();
        });
    </script>
</body>
</html>
