<?php
$current_path = $_SERVER['PHP_SELF'];
$path_parts = explode('/', $current_path);
$root_path = (in_array('admin', $path_parts) || in_array('pages', $path_parts)) ? '../' : '';
if (session_status() === PHP_SESSION_NONE) session_start();
include $root_path . 'includes/db.php';
$suivi_href = isset($_SESSION['user_id']) ? $root_path . 'pages/outils.php' : $root_path . 'pages/register.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriVie - Votre partenaire santé et bien-être</title>
    <link rel="stylesheet" href="<?= $root_path ?>css/style.css">
    <style>
    .articles-recents img {
        width: 180px;
        height: 120px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 2px 12px #d0e0d0;
        margin-bottom: 10px;
    }
    </style>
</head>
<body>
    <?php include $root_path . 'includes/header.php'; ?>

    <main>
        <section class="hero">
            <h1>Bienvenue sur NUTR'AISSYA</h1>
            <p>Votre partenaire pour une vie saine et naturelle : conseils nutrition, bien-être et outils personnalisés.</p>
            <a href="<?= $suivi_href ?>" class="btn">Commencer mon suivi</a>
        </section>

        <section class="section section-title">
            <h2>Notre philosophie</h2>
            <p>Chez Nutr’Aissya, nous cultivons une vision de la santé comme un jardin intérieur.
Un lieu sacré, où chaque choix – chaque souffle, chaque bouchée, chaque pensée – est une graine semée vers l’harmonie.

Nous croyons que le bien-être ne s’impose pas, il s’invite doucement.
Il se tisse dans l’équilibre entre le corps apaisé, l’esprit allégé, et l’âme écoutée.
Une alimentation consciente, des mouvements doux, un souffle régulier, un cœur tranquille… voilà notre alchimie.

Prendre soin de soi, c’est revenir à l’essentiel :
✨ se nourrir avec bonté,
🌸 bouger avec légèreté,
🍃 respirer avec gratitude,
💫 et s’aimer avec douceur.

Notre mission est simple :
t'accompagner, sans jamais te bousculer,
vers une version de toi plus sereine, plus alignée, plus vivante.

Car au fond, la santé n’est pas un objectif à atteindre,
mais un chemin à aimer.

</p>
        </section>

        <section class="section">
            <div class="section-title">
                <h2>Articles récents</h2>
            </div>
            <div class="articles-grid articles-recents">
                <?php
                $sql = "SELECT id, titre, description, contenu, date_creation, image FROM articles ORDER BY date_creation DESC LIMIT 3";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($article = $result->fetch_assoc()) {
                        $img = !empty($article['image']) ? htmlspecialchars($article['image']) : 'default.jpg';
                        echo '<div class="article-card">';
                        echo '<h3>' . htmlspecialchars($article['titre']) . '</h3>';
                        echo '<span class="date">' . date('d/m/Y', strtotime($article['date_creation'])) . '</span>';
                        echo '<img src="' . $root_path . 'medias/' . $img . '" alt="' . htmlspecialchars($article['titre']) . '" class="articles-recents-img">';
                        $desc = isset($article['description']) ? strip_tags($article['description']) : 'Pas de description';
                        echo '<p class="article-excerpt">' . htmlspecialchars(mb_substr($desc, 0, 120)) . '...</p>';
                        echo '<a href="' . $root_path . 'pages/details-produit.php?id=' . $article['id'] . '" class="en-savoir-plus">En savoir plus</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>Aucun article pour le moment.</p>';
                }
                ?>
            </div>
            <div style="text-align:center; margin-top:2rem;">
                <a href="<?= $root_path ?>pages/articles.php" class="btn">Voir tous les articles</a>
            </div>
        </section>
    </main>

    <?php include $root_path . 'includes/footer.php'; ?>
    <script src="<?= $root_path ?>js/main.js"></script>
</body>
</html>
