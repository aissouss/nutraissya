<?php
$current_path = $_SERVER['PHP_SELF'];
$path_parts = explode('/', $current_path);
$root_path = (in_array('admin', $path_parts) || in_array('pages', $path_parts)) ? '../' : '';
require_once $root_path . 'includes/auth.php';
require_once $root_path . 'includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $categorie = $_POST['categorie'];
    $description = trim($_POST['description']);
    $description_complete = trim($_POST['description_complete']);
    $prix = trim($_POST['prix']);
    $contenu = trim($_POST['contenu']);
    $id_auteur = $_SESSION['user_id'];
    $image_name = null;
    $erreur_upload = '';

    // Validation des champs obligatoires
    if (!empty($titre) && !empty($categorie) && !empty($description) && !empty($description_complete) && !empty($prix) && !empty($contenu)) {
        // Gestion de l'upload d'image
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== 4) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $file = $_FILES['image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $erreur_upload = "Format d'image non autorisé.";
            } elseif ($file['error'] !== 0) {
                $erreur_upload = "Erreur lors de l'upload de l'image.";
            } else {
                $nom_image = uniqid('img_', true) . '.' . $ext;
                $chemin_dossier = $root_path . 'medias';
                $chemin_destination = $chemin_dossier . '/' . $nom_image;

                if (!is_dir($chemin_dossier)) {
                    mkdir($chemin_dossier, 0755, true);
                }

                if (move_uploaded_file($file['tmp_name'], $chemin_destination)) {
                    chmod($chemin_destination, 0644);
                    $image_name = $nom_image;
                } else {
                    $erreur_upload = "Impossible de sauvegarder l'image sur le serveur.";
                }
            }
        }

        if ($erreur_upload) {
            $message = '<span style="color:red;">' . $erreur_upload . '</span>';
        } else {
            $stmt = $pdo->prepare('INSERT INTO articles (id_auteur, titre, categorie, description, description_complete, prix, image, contenu) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$id_auteur, $titre, $categorie, $description, $description_complete, $prix, $image_name, $contenu]);
            $message = "✅ Article ajouté avec succès !";
        }
    } else {
        $message = "<span style='color:red;'>Veuillez remplir tous les champs obligatoires.</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un article - Admin</title>
    <link rel="stylesheet" href="<?= $root_path ?>css/style.css">
    <style>
    .error-message {
      color: red;
      font-size: 0.9em;
      margin-top: 3px;
      display: none;
    }
    input:invalid, textarea:invalid, select:invalid {
      border: 2px solid #e74c3c;
    }
    </style>
</head>
<body>
<?php include $root_path . 'includes/header.php'; ?>
<div class="container">
    <h1>Ajouter un article</h1>
    <?php if ($message) echo '<div class="alert">'.$message.'</div>'; ?>
    <form method="POST" class="article-form" enctype="multipart/form-data">
        <label for="titre">Titre de l'article :</label>
        <input type="text" id="titre" name="titre" placeholder="Titre de l'article" required><small class="error-msg">Champ obligatoire</small><br>

        <label for="categorie">Catégorie :</label>
        <select id="categorie" name="categorie" required>
            <option value="">-- Choisir une catégorie --</option>
            <optgroup label="Conseils & Nutrition">
                <option value="Conseils nutritionnels">Conseils nutritionnels</option>
                <option value="Recettes santé">Recettes santé</option>
            </optgroup>
            <optgroup label="Programmes personnalisés">
                <option value="Programmes de régimes">Programmes de régimes</option>
                <option value="Programmes d'exercices">Programmes d'exercices</option>
            </optgroup>
            <optgroup label="Produits bien-être">
                <option value="Produits contre le stress">Produits contre le stress</option>
                <option value="Produits pour la perte de poids">Produits pour la perte de poids</option>
                <option value="Produits bien-être général">Produits bien-être général</option>
            </optgroup>
        </select><small class="error-msg">Champ obligatoire</small><br>

        <label for="description">Description courte :</label>
        <input type="text" id="description" name="description" placeholder="Résumé court de l'article" required><small class="error-msg">Champ obligatoire</small><br>

        <label for="description_complete">Description complète :</label>
        <textarea id="description_complete" name="description_complete" rows="4" placeholder="Description détaillée du produit ou de l'article" required></textarea><small class="error-msg">Champ obligatoire</small><br>

        <label for="prix">Prix (€) :</label>
        <input type="number" id="prix" name="prix" step="0.01" min="0" placeholder="Prix du produit" required><small class="error-msg">Champ obligatoire</small><br>

        <label for="image">Image (jpg, png) :</label>
        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png" required><small class="error-msg">Champ obligatoire</small><br>

        <label for="contenu">Contenu :</label>
        <textarea id="contenu" name="contenu" rows="8" placeholder="Rédigez ici votre article sur le bien-être, la nutrition, les conseils santé, etc." required></textarea><small class="error-msg">Champ obligatoire</small><br>

        <button type="submit" class="btn">Publier l'article</button>
    </form>

    <a href="<?= $root_path ?>admin/index.php" class="btn" style="margin-top:2rem;">Retour à l'accueil</a>
</div>
<?php include $root_path . 'includes/footer.php'; ?>

<script>
document.querySelector("form").addEventListener("submit", function(e) {
  let valid = true;
  this.querySelectorAll("[required]").forEach(field => {
    const msg = field.parentElement.querySelector(".error-msg");
    if (!field.value.trim()) {
      valid = false;
      field.classList.add("error");
      if (msg) msg.style.display = "inline";
    } else {
      field.classList.remove("error");
      if (msg) msg.style.display = "none";
    }
  });
  if (!valid) e.preventDefault();
});
</script>
</body>
</html>
