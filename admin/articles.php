<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

session_start();

// Vérifier si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . $basePath . '/index.php');
    exit;
}

$message = '';

// Traitement de la suppression d'article
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $article_id = (int)$_GET['id'];

    // Supprimer l'article
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->bind_param("i", $article_id);

    if ($stmt->execute()) {
        $message = 'L\'article a été supprimé avec succès.';
    } else {
        $message = 'Une erreur est survenue lors de la suppression de l\'article.';
    }

    $stmt->close();
}

// Pagination
$articles_par_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$debut = ($page - 1) * $articles_par_page;

// Recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$search_params = [];

if (!empty($search)) {
    $where_clause = " WHERE a.titre LIKE ? OR a.contenu LIKE ? ";
    $search_params = ["%$search%", "%$search%"];
}

// Récupérer le nombre total d'articles
$sql_count = "SELECT COUNT(*) as total FROM articles a" . $where_clause;
$stmt_count = $conn->prepare($sql_count);

if (!empty($search_params)) {
    $stmt_count->bind_param(str_repeat("s", count($search_params)), ...$search_params);
}

$stmt_count->execute();
$result_count = $stmt_count->get_result();
$row_count = $result_count->fetch_assoc();
$total_articles = $row_count['total'];
$total_pages = ceil($total_articles / $articles_par_page);

// Récupérer les articles pour la page actuelle
$sql = "SELECT a.*, u.nom, u.prenom
        FROM articles a
        LEFT JOIN utilisateurs u ON a.id_auteur = u.id " .
        $where_clause .
        "ORDER BY a.date_creation DESC
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);

if (!empty($search_params)) {
    $params = array_merge($search_params, [$debut, $articles_par_page]);
    $types = str_repeat("s", count($search_params)) . "ii";
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $debut, $articles_par_page);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des articles - NutriVie Admin</title>
    <link rel="stylesheet" href="<?= $basePath ?>/css/style.css">
</head>
<body>
    <div class="admin-container">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Gestion des articles</h1>
                <div class="admin-user">
                    <span>Connecté en tant que <?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></span>
                    <a href="<?= $basePath ?>/pages/logout.php" class="logout-btn">Déconnexion</a>
                </div>
            </header>

            <div class="admin-content">
                <?php if (!empty($message)): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>

                <div class="admin-actions">
                    <a href="<?= $basePath ?>/admin/article_add.php" class="primary-btn">Ajouter un article</a>

                    <form action="<?= $basePath ?>/admin/articles.php" method="get" class="search-form">
                        <input type="text" name="search" placeholder="Rechercher un article..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit">Rechercher</button>
                    </form>
                </div>

                <div class="articles-list">
                    <?php if ($result->num_rows > 0): ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Titre</th>
                                    <th>Auteur</th>
                                    <th>Date de création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($article = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $article['id']; ?></td>
                                        <td><?php echo htmlspecialchars($article['titre']); ?></td>
                                        <td>
                                            <?php
                                                if ($article['prenom'] && $article['nom']) {
                                                    echo htmlspecialchars($article['prenom'] . ' ' . $article['nom']);
                                                } else {
                                                    echo 'Inconnu';
                                                }
                                            ?>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($article['date_creation'])); ?></td>
                                        <td class="actions">
                                            <a href="<?= $basePath ?>/pages/article.php?id=<?php echo $article['id']; ?>" class="view-btn" title="Voir l'article" target="_blank">Voir</a>
                                            <a href="<?= $basePath ?>/admin/article_edit.php?id=<?php echo $article['id']; ?>" class="edit-btn" title="Modifier l'article">Modifier</a>
                                            <a href="<?= $basePath ?>/admin/articles.php?action=delete&id=<?php echo $article['id']; ?>" class="delete-btn" title="Supprimer l'article" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')">Supprimer</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-link">&laquo; Précédent</a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <span class="pagination-current"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-link"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-link">Suivant &raquo;</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="no-result">
                            <p>Aucun article trouvé.</p>
                            <?php if (!empty($search)): ?>
                                <p>Aucun résultat pour la recherche "<?php echo htmlspecialchars($search); ?>".</p>
                                <p><a href="<?= $basePath ?>/admin/articles.php">Afficher tous les articles</a></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
