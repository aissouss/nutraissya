<?php
$current_path = $_SERVER['PHP_SELF'];
$path_parts = explode('/', $current_path);
$root_path = (in_array('admin', $path_parts) || in_array('pages', $path_parts)) ? '../' : '';
?>
<aside class="admin-sidebar">
    <div class="admin-logo">
        <h2>NutriVie Admin</h2>
    </div>
    <nav class="admin-nav">
        <ul>
            <li><a href="<?= $root_path ?>admin/index.php">Tableau de bord</a></li>
            <li><a href="<?= $root_path ?>admin/ajouter_article.php">Ajouter un article</a></li>
            <li><a href="<?= $root_path ?>admin/liste_articles.php">Gérer les articles</a></li>
            <li><a href="<?= $root_path ?>admin/liste_utilisateurs.php">Gérer les utilisateurs</a></li>
        </ul>
    </nav>
    <div class="admin-sidebar-footer">
        <p>Version 1.0</p>
    </div>
</aside>

