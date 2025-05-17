<?php
// ===============================
// Connexion centralisée & auto-création pour NUTR'AISSYA
// ===============================
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'nutraissya';

// 1. Connexion MySQL sans base sélectionnée
$conn = new mysqli($host, $user, $password);
if ($conn->connect_error) {
    die('<div style="color:red;font-weight:bold;">Erreur de connexion MySQL : ' . htmlspecialchars($conn->connect_error) . '</div>');
}

// 2. Création de la base si elle n'existe pas (sans test préalable)
$sql = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === FALSE) {
    die('<div style="color:red;font-weight:bold;">Erreur lors de la création de la base de données : ' . htmlspecialchars($conn->error) . '</div>');
}

// 3. Sélection de la base
$conn->select_db($dbname);
$conn->set_charset('utf8mb4');

// 4. Connexion PDO parallèle
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur PDO : " . $e->getMessage());
}

// 5. Création automatique des tables principales
$tables = [
// Table utilisateurs
"CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    age INT,
    sexe VARCHAR(10),
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role ENUM('user', 'admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// Table articles
"CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_auteur INT,
    titre VARCHAR(255) NOT NULL,
    categorie VARCHAR(100) NOT NULL,
    description TEXT,
    description_complete TEXT,
    prix DECIMAL(10,2),
    image VARCHAR(255),
    contenu TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_auteur) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// Table bien_etre (suivi santé)
"CREATE TABLE IF NOT EXISTS bien_etre (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    date_enregistrement DATE NOT NULL,
    poids FLOAT,
    taille FLOAT,
    imc FLOAT,
    activite_physique INT,
    calories INT,
    humeur VARCHAR(50),
    fatigue VARCHAR(50),
    sommeil INT,
    motivation VARCHAR(50),
    objectif TEXT,
    notes TEXT,
    FOREIGN KEY (id_user) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// Table contact
"CREATE TABLE IF NOT EXISTS contact (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    sujet VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    lu BOOLEAN DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// Table panier
"CREATE TABLE IF NOT EXISTS panier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_article INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_article) REFERENCES articles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// Table favoris
"CREATE TABLE IF NOT EXISTS favoris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_article INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_article) REFERENCES articles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($tables as $sql) {
    if ($conn->query($sql) === FALSE) {
        die("Erreur lors de la création des tables : " . $conn->error);
    }
}

// 6. Création d'un compte admin par défaut
$email_admin = 'admin@nutraissya.com';
$check = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ?');
$check->execute([$email_admin]);
if (!$check->fetch()) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, age, sexe, role) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute(['Admin', 'Principal', $email_admin, $hash, 30, 'Femme', 'admin']);
}

// Insertion des produits par défaut si la table articles est vide
$check = $pdo->query('SELECT COUNT(*) FROM articles');
if ($check->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO articles (id_auteur, titre, categorie, description, description_complete, prix, image, contenu) VALUES
    (1, 'Pack Boost Immunité', 'Conseils nutritionnels', 'Boostez votre système immunitaire.', 'Des conseils pour renforcer vos défenses naturelles.', 15.00, 'produit1.jpg', 'Voici un programme complet pour renforcer vos défenses avec des aliments riches en vitamines.'),
    (1, 'Programme Minceur Express', 'Programmes de régimes', 'Perdez du poids rapidement.', 'Un régime structuré pour des résultats visibles.', 49.90, 'produit2.jpg', 'Un plan nutritionnel efficace pour brûler les graisses et retrouver la ligne.'),
    (1, 'Routine Pilates Débutant', 'Programmes d''exercices', 'Remise en forme douce.', 'Exercices adaptés aux débutants pour renforcer votre posture.', 35.00, 'produit3.jpg', 'Une série d''exercices simples à faire chez soi pour un corps tonique.'),
    (1, 'Spray anti-stress aux plantes', 'Produits contre le stress', 'Apaise les tensions.', 'Un concentré de plantes naturelles pour soulager le stress.', 19.99, 'produit4.jpg', 'Ce spray contient des extraits naturels pour vous aider à vous relaxer rapidement.'),
    (1, 'Brûleur de graisse naturel', 'Produits pour la perte de poids', 'Accélère la perte de poids.', 'Complément alimentaire pour stimuler le métabolisme.', 22.50, 'produit5.jpg', 'Prenez ce brûleur en complément de votre routine sportive pour des résultats optimaux.'),
    (1, 'Infusion bien-être digestion', 'Produits bien-être général', 'Soulage les ballonnements.', 'Un mélange d''herbes pour une digestion apaisée.', 13.90, 'produit6.jpg', 'Infusez 5 minutes et buvez après le repas pour une meilleure digestion.')
    ");
}
// ===============================
// Fin du db.php centralisé NUTR'AISSYA
// ===============================
?>


