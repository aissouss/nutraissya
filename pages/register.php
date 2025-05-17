<?php
session_start();
include '../includes/db.php';

$message = '';

// Si l'utilisateur est déjà connecté, le rediriger vers la page d'accueil
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $age = isset($_POST['age']) ? intval($_POST['age']) : null;
    $sexe = isset($_POST['sexe']) ? $_POST['sexe'] : '';

    // Validation de base
    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Veuillez entrer une adresse email valide.';
    } elseif ($password !== $confirm_password) {
        $message = 'Les mots de passe ne correspondent pas.';
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = 'Cette adresse email est déjà utilisée.';
        } else {
            // Hachage du mot de passe
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insérer l'utilisateur dans la base de données
            $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, age, sexe) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nom, $prenom, $email, $password_hash, $age, $sexe);

            if ($stmt->execute()) {
                $message = 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.';

                // Redirection vers la page de connexion après 3 secondes
                header('Refresh: 3; URL=login.php');
            } else {
                $message = 'Une erreur est survenue. Veuillez réessayer plus tard.';
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - NUTR'AISSYA</title>
    <link rel="stylesheet" href="/miniprojet-master/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <section class="form-section">
            <h1>Créer un compte</h1>

            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <form action="register.php" method="post">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" placeholder="Votre nom" required>
                </div>

                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" placeholder="Votre prénom" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="exemple@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmez le mot de passe" required>
                </div>

                <div class="form-group">
                    <label for="age">Âge :</label>
                    <input type="number" id="age" name="age" min="0" max="120" required><br>
                </div>

                <div class="form-group">
                    <label for="sexe">Sexe :</label>
                    <select id="sexe" name="sexe" required>
                        <option value="">--Choisir--</option>
                        <option value="Homme">Homme</option>
                        <option value="Femme">Femme</option>
                        <option value="Autre">Autre</option>
                    </select><br>
                </div>

                <button type="submit" class="btn">S'inscrire</button>
            </form>

            <p style="text-align:center; margin-top:1.5rem;">Vous avez déjà un compte ? <a href="login.php">Connectez-vous</a></p>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
