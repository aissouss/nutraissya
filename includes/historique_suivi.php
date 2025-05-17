<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<p>Veuillez vous connecter pour voir votre historique.</p>';
    return;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT date_enregistrement, poids, taille, activite_physique, calories_consommees, notes FROM suivi_sante WHERE id_utilisateur = ? ORDER BY date_enregistrement DESC');
$stmt->execute([$user_id]);
$suivis = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$suivis) {
    echo '<p>Aucun suivi enregistré pour le moment.</p>';
} else {
    echo '<table class="history-table">';
    echo '<tr><th>Date</th><th>Poids (kg)</th><th>Taille (cm)</th><th>Activité (min)</th><th>Calories</th><th>Notes</th></tr>';
    foreach ($suivis as $suivi) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($suivi['date_enregistrement']) . '</td>';
        echo '<td>' . htmlspecialchars($suivi['poids']) . '</td>';
        echo '<td>' . htmlspecialchars($suivi['taille']) . '</td>';
        echo '<td>' . htmlspecialchars($suivi['activite_physique']) . '</td>';
        echo '<td>' . htmlspecialchars($suivi['calories_consommees']) . '</td>';
        echo '<td>' . nl2br(htmlspecialchars($suivi['notes'])) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} 