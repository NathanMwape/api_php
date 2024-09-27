/////////////////////////////////////////////////////////////////////////////////////////
CREATE TABLE utilisateurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(255) NOT NULL,
  mot_de_passe VARCHAR(255) NOT NULL
);

CREATE TABLE positions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_id INT NOT NULL,
  latitude DECIMAL(10, 8),
  longitude DECIMAL(11, 8),
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

`Endpoints :`

`GET http://localhost/api_test/index.php/?utilisateur_id=2 // pour afficher les positions d\'un utilisateur specifique
GET http://localhost/api_test/index.php // pour afficher toutes les positions des utilisateurs
POST http://localhost/api_test/index.php // pour enregistrer une position
POST http://localhost/api_test/index.php/ // Données envoyées : { "nom": "exemple_utilisateur", "mot_de_passe": "exemple_mot_de_passe" } LOGIN // pour se connecter

