<?php
include '../includes/header.php';
require_once '../includes/db.php';

// Gestion des filtres et recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 9;
$where = [];
$params = [];
if ($search) {
    $where[] = '(titre LIKE ? OR description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($categorie && $categorie !== 'all') {
    $where[] = 'categorie = ?';
    $params[] = $categorie;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
// Compter le total pour la pagination
$stmt = $conn->prepare("SELECT COUNT(*) FROM articles $whereSql");
$stmt->execute($params);
$total = $stmt->get_result()->fetch_row()[0];
$nbPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;
// Récupérer les articles filtrés (champs sécurisés)
$stmt = $conn->prepare("SELECT id, titre, description, prix, image, categorie FROM articles $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$result = $stmt->get_result();
$articles = $result->fetch_all(MYSQLI_ASSOC);

// Catégories exemple (à adapter selon ta BDD)
$categories = [
    'all' => 'Toutes les catégories',
    'Conseils nutritionnels' => 'Conseils nutritionnels',
    'Programmes de régimes' => 'Programmes de régimes',
    'Programmes d\'exercices' => 'Programmes d\'exercices',
    'Produits contre le stress' => 'Produits contre le stress',
    'Produits pour la perte de poids' => 'Produits pour la perte de poids',
    'Produits bien-être général' => 'Produits bien-être général',
];
?>
<main class="section">
    <h1 class="boutique-title">Boutique NUTR'AISSYA</h1>

    <div class="search-filter-container">
        <form method="get" class="search-form">
            <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>" class="search-input">
            <select name="categorie" class="category-select">
                <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($categorie === $key) ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-filter">Filtrer</button>
        </form>
    </div>

    <?php if (!$articles): ?>
        <div class="alert alert-info">Aucun produit trouvé.</div>
    <?php else: ?>
    <div class="boutique-cards">
        <?php foreach($articles as $row): ?>
        <div class="boutique-card">
            <?php $img = !empty($row['image']) ? htmlspecialchars($row['image']) : 'default.jpg'; ?>
            <img src="<?= $root_path ?>medias/<?= $img ?>" alt="<?= isset($row['titre']) ? htmlspecialchars($row['titre']) : '' ?>">

            <div class="boutique-card-content">
                <h2><?= isset($row['titre']) ? htmlspecialchars($row['titre']) : 'Sans titre' ?></h2>
                <?php if (!empty($row['categorie'])): ?>
                <div class="categorie">Catégorie : <?= htmlspecialchars($row['categorie']) ?></div>
                <?php endif; ?>
                <p><?= isset($row['description']) ? htmlspecialchars($row['description']) : '' ?></p>
                <div class="prix"><?= isset($row['prix']) ? number_format($row['prix'], 2, ',', ' ') : '--' ?> €</div>
                <a href="details-produit.php?id=<?= $row['id'] ?>" class="en-savoir-plus">En savoir plus</a>
            </div>

            <div class="boutique-actions">
                <form method="post" action="panier.php" class="panier-form">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <div class="quantity">
                        <input type="number" name="quantite" min="1" value="1">
                    </div>
                    <button type="submit" name="add_panier">Ajouter au panier</button>
                </form>
                <form method="post" action="mes-favoris.php">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <button type="button" name="add_favori" onclick="window.location.href='login.php?error=connectez-vous'">Ajouter aux favoris</button>
                    <?php else: ?>
                        <button type="submit" name="add_favori">Ajouter aux favoris</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($nbPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $nbPages; $i++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
               class="page-link <?= ($i == $page) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

