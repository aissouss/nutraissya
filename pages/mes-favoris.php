<?php
session_start();
require_once '../includes/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo '<main class="section"><div class="alert alert-danger">Vous devez être connecté pour accéder aux favoris.</div></main>';
    include '../includes/footer.php';
    exit;
}
$user_id = $_SESSION['user_id'];

// Ajouter aux favoris
if (isset($_POST['add_favori']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    // Vérifier si déjà présent
    $stmt = $pdo->prepare('SELECT id FROM favoris WHERE id_utilisateur = ? AND id_article = ?');
    $stmt->execute([$user_id, $id]);
    if (!$stmt->fetch()) {
        $pdo->prepare('INSERT INTO favoris (id_utilisateur, id_article) VALUES (?, ?)')->execute([$user_id, $id]);
    }
    header('Location: mes-favoris.php');
    exit;
}
// Supprimer un favori
if (isset($_POST['remove']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $pdo->prepare('DELETE FROM favoris WHERE id_utilisateur = ? AND id_article = ?')->execute([$user_id, $id]);
}
// Affichage des favoris
$stmt = $pdo->prepare('SELECT a.* FROM favoris f JOIN articles a ON a.id = f.id_article WHERE f.id_utilisateur = ?');
$stmt->execute([$user_id]);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<main class="section">
    <h1>💖 Mes favoris</h1>
    <?php if (!$produits): ?>
        <p>Aucun produit en favori.</p>
    <?php else: ?>
        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
        <?php foreach($produits as $prod): ?>
            <div style="border:1px solid #ccc; padding:15px; width:250px;">
                <img src="<?= $root_path ?>medias/<?= htmlspecialchars($prod['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($prod['titre']) ?>" style="width:100%;height:180px;object-fit:cover;">
                <h2><?= htmlspecialchars($prod['titre']) ?></h2>
                <p><?= htmlspecialchars($prod['description']) ?></p>
                <p><strong><?= number_format($prod['prix'], 2, ',', ' ') ?> €</strong></p>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                    <button type="submit" name="remove">Retirer</button>
                </form>
                <a href="details-produit.php?id=<?= $prod['id'] ?>"><button>En savoir plus</button></a>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <a href="articles.php"><button>← Retour à la boutique</button></a>
</main>
<?php include '../includes/footer.php'; ?>
