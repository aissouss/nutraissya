<?php 
require_once '../includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo '<main class="section"><div class="alert alert-danger">Vous devez être connecté pour accéder au panier.</div></main>';
    include '../includes/footer.php';
    exit;
}
$user_id = $_SESSION['user_id'];

// Ajouter au panier
if (isset($_POST['add_panier']) && isset($_POST['id']) && isset($_POST['quantite'])) {
    $id = intval($_POST['id']);
    $qte = max(1, intval($_POST['quantite']));
    // Vérifier si déjà présent
    $stmt = $pdo->prepare('SELECT id, quantite FROM panier WHERE id_utilisateur = ? AND id_article = ?');
    $stmt->execute([$user_id, $id]);
    $row = $stmt->fetch();
    if ($row) {
        $newQte = $row['quantite'] + $qte;
        $pdo->prepare('UPDATE panier SET quantite = ? WHERE id = ?')->execute([$newQte, $row['id']]);
    } else {
        $pdo->prepare('INSERT INTO panier (id_utilisateur, id_article, quantite) VALUES (?, ?, ?)')->execute([$user_id, $id, $qte]);
    }
    header('Location: panier.php');
    exit;
}
// Supprimer un produit
if (isset($_POST['remove']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $pdo->prepare('DELETE FROM panier WHERE id_utilisateur = ? AND id_article = ?')->execute([$user_id, $id]);
}
// Vider le panier
if (isset($_POST['vider'])) {
    $pdo->prepare('DELETE FROM panier WHERE id_utilisateur = ?')->execute([$user_id]);
}
// Valider la commande (exemple simple)
if (isset($_POST['valider'])) {
    $pdo->prepare('DELETE FROM panier WHERE id_utilisateur = ?')->execute([$user_id]);
    $message = "Commande validée !";
}
// Affichage du panier
$stmt = $pdo->prepare('SELECT a.id, a.titre, a.prix, a.image, p.quantite FROM panier p JOIN articles a ON a.id = p.id_article WHERE p.id_utilisateur = ?');
$stmt->execute([$user_id]);
$produits = [];
$total = 0;
while($row = $stmt->fetch()) {
    $row['total'] = $row['quantite'] * (isset($row['prix']) ? $row['prix'] : 0);
    $produits[] = $row;
    $total += $row['total'];
}
?>
<main class="section">
<h1>Mon panier</h1>
<?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>
<?php if (!$produits): ?>
  <div class="alert alert-info">Votre panier est vide.</div>
<?php else: ?>
  <table class="table-panier">
    <tr>
      <th>Produit</th>
      <th>Image</th>
      <th>Prix</th>
      <th>Quantité</th>
      <th>Total</th>
      <th>Action</th>
    </tr>
    <?php foreach($produits as $prod): ?>
      <tr>
        <td><?= isset($prod['titre']) ? htmlspecialchars($prod['titre']) : 'Sans titre' ?></td>
        <td><img src="<?= $root_path ?>medias/<?= isset($prod['image']) ? htmlspecialchars($prod['image']) : 'default.jpg' ?>" width="60"></td>
        <td><?= isset($prod['prix']) ? number_format($prod['prix'], 2, ',', ' ') : '--' ?> €</td>
        <td><?= $prod['quantite'] ?></td>
        <td><?= number_format($prod['total'], 2, ',', ' ') ?> €</td>
        <td>
          <form method="post" style="display:inline;">
            <input type="hidden" name="id" value="<?= $prod['id'] ?>">
            <button type="submit" name="remove" class="btn-panier">Supprimer</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <tr>
      <td colspan="4" align="right"><strong>Total :</strong></td>
      <td colspan="2"><strong><?= number_format($total, 2, ',', ' ') ?> €</strong></td>
    </tr>
  </table>
  <form method="post" style="display:inline;">
    <button type="submit" name="vider" class="btn-panier">Vider le panier</button>
  </form>
  <form method="post" style="display:inline;">
    <button type="submit" name="valider" class="btn-panier">Valider la commande</button>
  </form>
<?php endif; ?>
<a href="articles.php" class="btn-panier">Continuer mes achats</a>
</main>
<?php include '../includes/footer.php'; ?> 