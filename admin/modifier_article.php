<?php
$current_path = $_SERVER['PHP_SELF'];
$path_parts = explode('/', $current_path);
$root_path = (in_array('admin', $path_parts) || in_array('pages', $path_parts)) ? '../' : '';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . $root_path . 'pages/articles.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . $root_path . 'admin/liste_articles.php');
    exit;
}

$id = intval($_GET['id']);
$message = '';

// Récupérer l'article
$stmt = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: ' . $root_path . 'admin/liste_articles.php');
    exit;
}

// Traitement modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $categorie = $_POST['categorie'];
    $description = trim($_POST['description']);
    $description_complete = trim($_POST['description_complete']);
    $prix = trim($_POST['prix']);
    $contenu = trim($_POST['contenu']);
    $image_name = $article['image'];

    if (!empty($titre) && !empty($categorie) && !empty($description) && !empty($description_complete) && !empty($prix) && !empty($contenu)) {
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== 4) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $file = $_FILES['image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed) && $file['error'] === 0) {
                $nom_image = uniqid('img_', true) . '.' . $ext;
                $chemin_dossier = __DIR__ . '/../medias';
                $chemin_destination = $chemin_dossier . '/' . $nom_image;

                if (!is_dir($chemin_dossier)) {
                    mkdir($chemin_dossier, 0755, true);
                }

                if (move_uploaded_file($file['tmp_name'], $chemin_destination)) {
                    chmod($chemin_destination, 0644);

                    // Supprimer l'ancienne image si différente
                    if (!empty($article['image']) && file_exists($chemin_dossier . '/' . $article['image'])) {
                        unlink($chemin_dossier . '/' . $article['image']);
                    }

                    $image_name = $nom_image;
                }
            }
        }

        $stmt = $pdo->prepare('UPDATE articles SET titre=?, categorie=?, description=?, description_complete=?, prix=?, image=?, contenu=? WHERE id=?');
        $stmt->execute([$titre, $categorie, $description, $description_complete, $prix, $image_name, $contenu, $id]);
        $message = "✅ Article modifié avec succès !";

        $article = array_merge($article, compact('titre', 'categorie', 'description', 'description_complete', 'prix', 'image_name', 'contenu'));
    } else {
        $message = "❌ Veuillez remplir tous les champs.";
    }
}

$categories = [
    'Conseils nutritionnels',
    'Recettes santé',
    'Programmes de régimes',
    'Programmes d\'exercices',
    'Produits contre le stress',
    'Produits pour la perte de poids',
    'Produits bien-être général',
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un article</title>
    <link rel="stylesheet" href="<?= $root_path ?>admin/style.css">
    <link rel="stylesheet" href="<?= $root_path ?>css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="admin-main">
    <h1>Modifier un article</h1>
    <?php if ($message) echo '<div class="alert">'.$message.'</div>'; ?>
    <?php $img = !empty($article['image']) ? htmlspecialchars($article['image']) : 'default.jpg'; ?>
    <div style="margin-bottom:20px;">
        <img src="<?= $root_path ?>medias/<?= $img ?>" alt="Image article" style="max-width:180px;max-height:120px;border-radius:8px;">
    </div>
    <form method="POST" class="article-form" enctype="multipart/form-data">
        <label for="titre">Titre de l'article :</label>
        <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($article['titre']) ?>" required><br>
        <label for="categorie">Catégorie :</label>
        <select id="categorie" name="categorie" required>
            <option value="">-- Choisir une catégorie --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= ($article['categorie'] === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
        </select><br>
        <label for="description">Description courte :</label>
        <input type="text" id="description" name="description" value="<?= htmlspecialchars($article['description']) ?>" required><br>
        <label for="description_complete">Description complète :</label>
        <textarea id="description_complete" name="description_complete" rows="4" required><?= htmlspecialchars($article['description_complete']) ?></textarea><br>
        <label for="prix">Prix (€) :</label>
        <input type="number" id="prix" name="prix" step="0.01" min="0" value="<?= htmlspecialchars($article['prix']) ?>" required><br>
        <label for="image">Image (jpg, png) :</label>
        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png"><br>
        <label for="contenu">Contenu :</label>
        <textarea id="contenu" name="contenu" rows="8" required><?= htmlspecialchars($article['contenu']) ?></textarea><br>
        <button type="submit" class="btn">Enregistrer les modifications</button>
    </form>
    <a href="<?= $root_path ?>admin/liste_articles.php" class="btn" style="margin-top:2rem;">Retour à la liste</a>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html> 