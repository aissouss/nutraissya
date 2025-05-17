<?php
$current_path = $_SERVER['PHP_SELF'];
$path_parts = explode('/', $current_path);
$root_path = (in_array('admin', $path_parts) || in_array('pages', $path_parts)) ? '../' : '';
require_once $root_path . 'includes/auth.php';
require_once $root_path . 'includes/db.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . $root_path . 'pages/articles.php');
    exit;
}
// Suppression d'un article
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare('DELETE FROM articles WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: ' . $root_path . 'admin/liste_articles.php');
    exit;
}
$articles = $pdo->query('SELECT a.*, u.nom, u.prenom FROM articles a LEFT JOIN utilisateurs u ON a.id_auteur = u.id ORDER BY date_creation DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des articles</title>
    <link rel="stylesheet" href="<?= $root_path ?>css/style.css">
</head>
<body>
<?php include $root_path . 'includes/header.php'; ?>
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="admin-main">
    <h1>Gestion des articles</h1>
    <a href="<?= $root_path ?>admin/ajouter_article.php" class="btn">Ajouter un article</a>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Catégorie</th>
                <th>Auteur</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($articles as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['titre']) ?></td>
                <td><?= htmlspecialchars($a['categorie']) ?></td>
                <td><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></td>
                <td><?= date('d/m/Y', strtotime($a['date_creation'])) ?></td>
                <td>
                    <a href="<?= $root_path ?>admin/modifier_article.php?id=<?= $a['id'] ?>" class="btn">Modifier</a>
                    <a href="<?= $root_path ?>admin/liste_articles.php?delete=<?= $a['id'] ?>" class="btn" onclick="return confirm('Supprimer cet article ?');">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <a href="<?= $root_path ?>admin/index.php" class="btn">Retour au tableau de bord</a>
</div>
<?php include $root_path . 'includes/footer.php'; ?>
</body>
</html> 