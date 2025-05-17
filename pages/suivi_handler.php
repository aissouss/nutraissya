<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    $data = [
        'humeur' => filter_input(INPUT_POST, 'humeur', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 5]
        ]),
        'sommeil' => filter_input(INPUT_POST, 'sommeil', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 24]
        ]),
        'activite' => filter_input(INPUT_POST, 'activite', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0]
        ]),
        'stress' => filter_input(INPUT_POST, 'stress', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 5]
        ]),
        'alimentation' => filter_input(INPUT_POST, 'alimentation', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 5]
        ]),
        'notes' => htmlspecialchars($_POST['notes'] ?? '')
    ];

    foreach ($data as $key => $value) {
        if ($value === false || $value === null) {
            $_SESSION['feedback'] = "Valeur invalide pour: $key";
            header("Location: ../outils.php");
            exit();
        }
    }

    $stmt = $conn->prepare("INSERT INTO suivi_sante 
        (id_utilisateur, date_enregistrement, humeur, sommeil, activite_physique, stress, qualite_alimentation, notes)
        VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("iiiiiss", 
        $userId,
        $data['humeur'],
        $data['sommeil'],
        $data['activite'],
        $data['stress'],
        $data['alimentation'],
        $data['notes']
    );

    if ($stmt->execute()) {
        $_SESSION['feedback'] = "✅ Suivi enregistré avec succès!";
    } else {
        $_SESSION['feedback'] = "❌ Erreur: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: ../outils.php");
    exit();
}