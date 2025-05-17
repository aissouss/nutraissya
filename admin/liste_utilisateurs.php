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
// Changement de rôle
if (isset($_GET['role']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $role = ($_GET['role'] === 'admin') ? 'admin' : 'utilisateur';
    $stmt = $pdo->prepare('UPDATE utilisateurs SET role = ? WHERE id = ?');
    $stmt->execute([$role, $id]);
    header('Location: ' . $root_path . 'admin/liste_utilisateurs.php');
    exit;
}
// Suppression d'un utilisateur
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare('DELETE FROM utilisateurs WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: ' . $root_path . 'admin/liste_utilisateurs.php');
    exit;
}
$users = $pdo->query('SELECT * FROM utilisateurs ORDER BY date_inscription DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des utilisateurs</title>
    <link rel="stylesheet" href="<?= $root_path ?>css/style.css">
</head>
<body>
<?php include $root_path . 'includes/header.php'; ?>
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="admin-main">
    <h1>Gestion des utilisateurs</h1>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Date inscription</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['nom']) ?></td>
                <td><?= htmlspecialchars($u['prenom']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><?= date('d/m/Y', strtotime($u['date_inscription'])) ?></td>
                <td>
                    <?php if ($u['role'] === 'admin'): ?>
                        <a href="<?= $root_path ?>admin/liste_utilisateurs.php?role=utilisateur&id=<?= $u['id'] ?>" class="btn">Rendre utilisateur</a>
                    <?php else: ?>
                        <a href="<?= $root_path ?>admin/liste_utilisateurs.php?role=admin&id=<?= $u['id'] ?>" class="btn">Rendre admin</a>
                    <?php endif; ?>
                    <a href="<?= $root_path ?>admin/liste_utilisateurs.php?delete=<?= $u['id'] ?>" class="btn" onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</a>
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