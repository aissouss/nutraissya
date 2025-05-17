<?php
session_start();
include '../includes/db.php';

$message = '';

// Si l'utilisateur est déjà connecté, le rediriger vers la page d'accueil
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = 'Veuillez remplir tous les champs.';
    } else {
        // Rechercher l'utilisateur dans la base de données
        $stmt = $conn->prepare("SELECT id, nom, prenom, email, mot_de_passe, role FROM utilisateurs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Vérifier le mot de passe
            if (password_verify($password, $user['mot_de_passe'])) {
                // Connexion réussie, créer une session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Rediriger vers la page d'accueil
                header('Location: ../index.php');
                exit;
            } else {
                $message = 'Identifiants incorrects. Veuillez réessayer.';
            }
        } else {
            $message = 'Identifiants incorrects. Veuillez réessayer.';
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - NutriVie</title>
    <link rel="stylesheet" href="/miniprojet-master/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <section class="form-section">
            <h1>Connexion</h1>

            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit">Se connecter</button>
            </form>

            <p>Vous n'avez pas de compte ? <a href="register.php">Inscrivez-vous</a></p>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
