<?php
session_start();
require_once '../includes/db.php';

if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>Produit introuvable.</div>";
    exit;
}
$id = intval($_GET['id']);
$sql = "SELECT * FROM articles WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$article = $stmt->fetch();
if (!$article) {
    echo "<div class='alert alert-danger'>Produit introuvable.</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($article['titre']) ?> - Détail</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
    .fiche-produit {
        max-width: 600px;
        margin: 40px auto;
        background: #f8fcfa;
        border-radius: 18px;
        box-shadow: 0 4px 24px #e0e0e0;
        padding: 2.5rem 2rem 2rem 2rem;
        text-align: center;
    }
    .fiche-produit img {
        display: block;
        margin: 0 auto 1.5rem auto;
        max-width: 320px;
        max-height: 320px;
        border-radius: 14px;
        box-shadow: 0 2px 12px #d0e0d0;
        background: #fff;
    }
    .fiche-produit h1 {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: #2d4032;
    }
    .fiche-produit .prix {
        color: #1b7e3b;
        font-size: 1.5rem;
        font-weight: bold;
        margin: 1.2rem 0 1.5rem 0;
    }
    .fiche-produit .categorie {
        color: #3a5d3a;
        font-size: 1.1rem;
        margin-bottom: 0.7rem;
    }
    .fiche-produit .date {
        color: #888;
        font-size: 0.98em;
        margin-bottom: 1.2rem;
    }
    .fiche-produit .contenu {
        text-align: left;
        margin: 1.5rem 0;
        color: #444;
        background: #fff;
        border-radius: 10px;
        padding: 1.2rem;
        box-shadow: 0 1px 6px #e0e0e0;
    }
    .fiche-produit .btns {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin-top: 1.5rem;
    }
    .fiche-produit button, .fiche-produit a.btn {
        background: #3a5d3a;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px 22px;
        font-size: 1em;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s;
    }
    .fiche-produit button:hover, .fiche-produit a.btn:hover {
        background: #1b3d1b;
    }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<main>
<div class="fiche-produit">
    <?php $img = !empty($article['image']) ? htmlspecialchars($article['image']) : 'default.jpg'; ?>
    <img src="<?= $root_path ?>medias/<?= $img ?>" alt="<?= htmlspecialchars($article['titre']) ?>">
    <h1><?= htmlspecialchars($article['titre']) ?></h1>
    <div class="categorie">Catégorie : <?= htmlspecialchars($article['categorie']) ?></div>
    <div class="date">Publié le <?= date('d/m/Y', strtotime($article['date_creation'])) ?></div>
    <div class="prix">Prix : <?= number_format($article['prix'], 2, ',', ' ') ?> €</div>
    <div class="desc-courte"><strong>Description courte :</strong> <?= htmlspecialchars($article['description']) ?></div>
    <p style="margin-bottom:1.5rem;"> <strong>Description complète :</strong> <?= nl2br(htmlspecialchars($article['description_complete'])) ?> </p>
    <div class="contenu">
        <?= nl2br(htmlspecialchars($article['contenu'])) ?>
    </div>
    <div class="btns">
        <form method="post" action="panier.php" style="display:inline;">
          <input type="hidden" name="id" value="<?= $article['id'] ?>">
          <button type="submit" name="add_panier">Ajouter au panier</button>
        </form>
        <form method="post" action="mes-favoris.php" style="display:inline;">
          <input type="hidden" name="id" value="<?= $article['id'] ?>">
          <?php if (!isset($_SESSION['user_id'])): ?>
            <button type="button" onclick="window.location.href='login.php?error=connectez-vous'">Ajouter aux favoris</button>
          <?php else: ?>
            <button type="submit" name="add_favori">Ajouter aux favoris</button>
          <?php endif; ?>
        </form>
        <a href="articles.php" class="btn">Retour à la boutique</a>
    </div>
</div>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html> 