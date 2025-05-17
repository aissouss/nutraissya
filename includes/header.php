<?php
$current_path = $_SERVER['PHP_SELF'];
$path_parts = explode('/', $current_path);
$root_path = (in_array('admin', $path_parts) || in_array('pages', $path_parts)) ? '../' : '';

if (session_status() === PHP_SESSION_NONE) session_start();
$nbArticles = 0;
if (!empty($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $qte) {
        $nbArticles += $qte;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NUTR'AISSYA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $root_path ?>css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?= $root_path ?>js/main.js" defer></script>
</head>
<body>
<header>
    <div class="navbar">
        <div class="logo" style="display:flex;align-items:center;gap:12px;">
            <img src="<?= $root_path ?>medias/logo-nutraissya.png" alt="Logo NUTR'AISSYA">
            <h1>NUTR'AISSYA</h1>
        </div>
        <nav>
            <ul>
                <li><a href="<?= $root_path ?>index.php">Accueil</a></li>
                <li><a href="<?= $root_path ?>pages/articles.php">Articles</a></li>
                <li><a href="<?= $root_path ?>pages/outils.php">Outils bien-être</a></li>
                <li><a href="<?= $root_path ?>pages/prevention.php">Prévention</a></li>
                <li><a href="<?= $root_path ?>pages/contact.php">Contact</a></li>
                <li>
                    <a href="<?= $root_path ?>pages/panier.php">
                        PANIER
                        <?php if ($nbArticles > 0): ?>
                            <span class="badge-panier"><?= $nbArticles ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php
                if (isset($_SESSION['user_id'])) {
                    echo '<li><a href="' . $root_path . 'pages/mon-profil.php">Mon Profil</a></li>';
                    if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
                        echo '<li><a href="' . $root_path . 'admin/index.php">Administration</a></li>';
                    }
                    echo '<li><a href="' . $root_path . 'pages/logout.php">Déconnexion</a></li>';
                } else {
                    echo '<li><a href="' . $root_path . 'pages/login.php">Connexion</a></li>';
                    echo '<li><a href="' . $root_path . 'pages/register.php">Inscription</a></li>';
                }
                ?>
            </ul>
        </nav>
    </div>
</header>
