<?php
$current_path = $_SERVER['PHP_SELF'];
$path_parts = explode('/', $current_path);
$root_path = (in_array('admin', $path_parts) || in_array('pages', $path_parts)) ? '../' : '';
?>
<footer>
    <div class="footer-content" style="display:flex;align-items:center;gap:32px;">
        <img src="<?= $root_path ?>medias/logo-nutraissya.png" alt="Logo NUTR'AISSYA" style="height:54px;width:auto;">
        <div class="footer-section about">
            <h3>À propos de NUTR'AISSYA</h3>
            <p>NUTR'AISSYA est votre partenaire pour une vie saine et naturelle, offrant conseils en nutrition, programmes d'exercices et suivi personnalisé.</p>
        </div>
        <div class="footer-section links">
            <h3>Liens rapides</h3>
            <ul>
                <li><a href="/miniprojet-master/index.php">Accueil</a></li>
                <li><a href="/miniprojet-master/pages/articles.php">Articles</a></li>
                <li><a href="/miniprojet-master/pages/outils.php">Outils bien-être</a></li>
                <li><a href="/miniprojet-master/pages/contact.php">Contact</a></li>
            </ul>
        </div>
        <div class="footer-section contact">
            <h3>Contact</h3>
            <p><strong>Nos coordonnées</strong></p>
            <p>Email : contact@nutrivie.fr</p>
            <p>Téléphone : +33 1 23 45 67 89</p>
            <p>Adresse : 123 Avenue de la Nutrition, 75000 Paris</p>
            <p>Horaires : Lundi au Vendredi, 9h-18h</p>
            <p><strong>Suivez-nous</strong></p>
            <div class="footer-social">
                <a href="#" title="Instagram" style="margin-right:10px;color:#fff;font-size:1.5em;"><i class="fa fa-instagram"></i></a>
                <a href="#" title="Twitter" style="margin-right:10px;color:#fff;font-size:1.5em;"><i class="fa fa-twitter"></i></a>
                <a href="#" title="Facebook" style="color:#fff;font-size:1.5em;"><i class="fa fa-facebook"></i></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> NUTR'AISSYA. Tous droits réservés.<br>Projet réalisé dans le cadre d'un projet scolaire.</p>
    </div>
</footer>
