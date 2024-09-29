<?php
// Inclusion du fichier de connexion
try {
  $pdo = new PDO('mysql:host=localhost;dbname=live_locator', 'root', '');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo 'Erreur de connexion : ' . $e->getMessage();
}

// Récupération des utilisateurs et d'une seule position (la plus récente ou la première) pour chaque utilisateur
$sql = "SELECT u.id, u.nom, p.latitude, p.longitude 
        FROM utilisateurs u
        LEFT JOIN (
            SELECT utilisateur_id, latitude, longitude 
            FROM positions
            GROUP BY utilisateur_id
        ) p ON u.id = p.utilisateur_id";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Localisation des Utilisateurs</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
</head>
<body>
    <div class="container mt-5">
        <h2>Liste des utilisateurs</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['nom']) ?></td>
                        <td>
                            <?php if ($user['latitude'] && $user['longitude']): ?>
                                <a href="map.php?id=<?= $user['id'] ?>" class="btn btn-primary">Voir la carte</a>
                            <?php else: ?>
                                <span class="text-danger">Pas de position</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
