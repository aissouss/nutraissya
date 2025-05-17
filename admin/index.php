<?php
$current_path = $_SERVER['PHP_SELF'];
$path_parts = explode('/', $current_path);
$root_path = (in_array('admin', $path_parts) || in_array('pages', $path_parts)) ? '../' : '';
require $root_path . 'includes/auth.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . $root_path . 'pages/articles.php');
    exit;
}
require $root_path . 'includes/db.php';
// Statistiques
$total_articles = $pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn();
$total_users = $pdo->query('SELECT COUNT(*) FROM utilisateurs')->fetchColumn();
$cat_stats = $pdo->query('SELECT categorie, COUNT(*) as nb FROM articles GROUP BY categorie')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord Admin</title>
    <link rel="stylesheet" href="<?= $root_path ?>admin/style.css">
    <link rel="stylesheet" href="<?= $root_path ?>css/style.css">
</head>
<body>
<?php include $root_path . 'includes/header.php'; ?>
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="admin-main">
    <h1>Tableau de bord Administrateur</h1>
    <div class="admin-stats">
        <div class="stat-card"><strong><?= $total_articles ?></strong><br>Articles</div>
        <div class="stat-card"><strong><?= $total_users ?></strong><br>Utilisateurs</div>
    </div>
    <h2>Articles par catégorie</h2>
    <ul class="cat-list">
        <?php foreach ($cat_stats as $cat): ?>
            <li><?= htmlspecialchars($cat['categorie']) ?> : <strong><?= $cat['nb'] ?></strong></li>
        <?php endforeach; ?>
    </ul>
    <div class="admin-actions">
        <a href="<?= $root_path ?>admin/ajouter_article.php" class="btn">Ajouter un article</a>
        <a href="<?= $root_path ?>admin/liste_articles.php" class="btn">Gérer les articles</a>
        <a href="<?= $root_path ?>admin/liste_utilisateurs.php" class="btn">Gérer les utilisateurs</a>
    </div>
</div>
<?php include $root_path . 'includes/footer.php'; ?>
</body>
</html>
