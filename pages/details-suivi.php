<?php
require '../includes/auth.php';
require '../includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('<p>Accès non autorisé.</p><a href="mon-profil.php">Retour</a>');
}
$id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM bien_etre WHERE id = ? AND id_user = ?');
$stmt->execute([$id, $user_id]);
$suivi = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$suivi) {
    die('<p>Accès non autorisé.</p><a href="mon-profil.php">Retour</a>');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du suivi</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container">
    <h1>Détails du suivi du <?= htmlspecialchars($suivi['date_enregistrement']) ?></h1>
    <table class="details-table">
        <tr><th>Poids</th><td><?= htmlspecialchars($suivi['poids']) ?> kg</td></tr>
        <tr><th>Taille</th><td><?= htmlspecialchars($suivi['taille']) ?> cm</td></tr>
        <tr><th>IMC</th><td><?= ($suivi['taille'] > 0) ? round($suivi['poids'] / pow($suivi['taille']/100, 2), 1) : '' ?></td></tr>
        <tr><th>Humeur</th><td><?= htmlspecialchars($suivi['humeur']) ?></td></tr>
        <tr><th>Fatigue</th><td><?= htmlspecialchars($suivi['fatigue']) ?></td></tr>
        <tr><th>Sommeil</th><td><?= htmlspecialchars($suivi['sommeil']) ?> h</td></tr>
        <tr><th>Motivation</th><td><?= htmlspecialchars($suivi['motivation']) ?></td></tr>
        <tr><th>Activité physique</th><td><?= htmlspecialchars($suivi['activite_physique']) ?> min</td></tr>
        <tr><th>Calories</th><td><?= htmlspecialchars($suivi['calories']) ?></td></tr>
        <tr><th>Objectif</th><td><?= htmlspecialchars($suivi['objectif']) ?></td></tr>
        <tr><th>Notes</th><td><?= nl2br(htmlspecialchars($suivi['notes'])) ?></td></tr>
    </table>
    <a href="mon-profil.php" class="btn">Retour</a>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html> 