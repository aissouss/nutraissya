<?php require '../includes/auth.php'; ?>
<?php
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['suivi'])) {
    $user_id = $_SESSION['user_id'];
    $date = $_POST['date'] ?? date('Y-m-d');
    $poids = floatval($_POST['poids'] ?? 0);
    $taille = floatval($_POST['taille'] ?? 0);
    $humeur = $_POST['humeur'] ?? '';
    $fatigue = $_POST['fatigue'] ?? '';
    $sommeil = intval($_POST['sommeil'] ?? 0);
    $motivation = $_POST['motivation'] ?? '';
    $activite_physique = intval($_POST['activite_physique'] ?? 0);
    $calories = intval($_POST['calories'] ?? 0);
    $objectif = $_POST['objectif'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $imc = ($taille > 0) ? $poids / pow($taille/100, 2) : null;
    $stmt = $pdo->prepare('INSERT INTO bien_etre (id_user, date_enregistrement, poids, taille, imc, humeur, fatigue, sommeil, motivation, activite_physique, calories, objectif, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$user_id, $date, $poids, $taille, $imc, $humeur, $fatigue, $sommeil, $motivation, $activite_physique, $calories, $objectif, $notes]);
    $msg = "✅ Suivi bien-être enregistré !";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Outils Bien-être</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
    function calculIMC() {
        var poids = parseFloat(document.getElementById('poids').value);
        var taille = parseFloat(document.getElementById('taille').value) / 100;
        var res = document.getElementById('imc-resultat');
        if (poids > 0 && taille > 0) {
            var imc = poids / (taille * taille);
            var msg = '';
            if (imc < 18.5) msg = 'Maigreur';
            else if (imc < 25) msg = 'Corpulence normale';
            else if (imc < 30) msg = 'Surpoids';
            else msg = 'Obésité';
            res.innerHTML = 'IMC : ' + imc.toFixed(1) + ' (' + msg + ')';
        } else {
            res.innerHTML = '';
        }
    }
    </script>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container">
    <h1>Suivi Bien-être</h1>
    <?php if (!empty($msg)) echo '<div class="alert">'.$msg.'</div>'; ?>
    <form method="POST" oninput="calculIMC(); return false;">
        <input type="hidden" name="suivi" value="1">
        <label>Date :</label>
        <input type="date" name="date" value="<?= date('Y-m-d') ?>" required><br>
        <label>Poids (kg) :</label>
        <input type="number" id="poids" name="poids" min="0" step="0.1" required><br>
        <label>Taille (cm) :</label>
        <input type="number" id="taille" name="taille" min="0" step="0.1" required><br>
        <div id="imc-resultat" style="margin:10px 0;font-weight:bold;"></div>
        <label>Humeur :</label>
        <select name="humeur" required>
            <option value="">--Choisir--</option>
            <option value="Très mauvaise">😞 Très mauvaise</option>
            <option value="Mauvaise">🙁 Mauvaise</option>
            <option value="Moyenne">😐 Moyenne</option>
            <option value="Bonne">🙂 Bonne</option>
            <option value="Excellente">😃 Excellente</option>
        </select><br>
        <label>Fatigue :</label>
        <select name="fatigue" required>
            <option value="">--Choisir--</option>
            <option value="Très fatigué(e)">Très fatigué(e)</option>
            <option value="Fatigué(e)">Fatigué(e)</option>
            <option value="Normal">Normal</option>
            <option value="Reposé(e)">Reposé(e)</option>
            <option value="En pleine forme">En pleine forme</option>
        </select><br>
        <label>Sommeil (heures) :</label>
        <input type="number" name="sommeil" min="0" max="24" required><br>
        <label>Motivation :</label>
        <select name="motivation" required>
            <option value="">--Choisir--</option>
            <option value="Aucune">Aucune</option>
            <option value="Faible">Faible</option>
            <option value="Moyenne">Moyenne</option>
            <option value="Bonne">Bonne</option>
            <option value="Excellente">Excellente</option>
        </select><br>
        <label>Activité physique (minutes) :</label>
        <input type="number" name="activite_physique" min="0" required><br>
        <label>Calories consommées :</label>
        <input type="number" name="calories" min="0" required><br>
        <label>Objectif :</label>
        <textarea name="objectif" rows="2"></textarea><br>
        <label>Notes :</label>
        <textarea name="notes" rows="2"></textarea><br>
        <button type="submit">Enregistrer</button>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
