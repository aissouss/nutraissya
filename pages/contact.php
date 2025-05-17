<?php
session_start();
include '../includes/db.php';

$message = '';

// Traitement du formulaire de contact
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $sujet = trim($_POST['sujet']);
    $contenu = trim($_POST['message']);

    // Validation de base
    if (empty($nom) || empty($email) || empty($sujet) || empty($contenu)) {
        $message = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Veuillez entrer une adresse email valide.';
    } else {
        // Insérer le message dans la base de données
        $stmt = $conn->prepare("INSERT INTO contact (nom, email, sujet, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nom, $email, $sujet, $contenu);

        if ($stmt->execute()) {
            $message = 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.';

            // Envoi d'un email (simulé ici, à compléter selon votre configuration)
            // mail('contact@nutrivie.fr', 'Nouveau message de contact: ' . $sujet, $contenu, 'From: ' . $email);

            // Vider les champs du formulaire après envoi
            $nom = $email = $sujet = $contenu = '';
        } else {
            $message = 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer plus tard.';
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
    <title>Contact - NutriVie</title>
    <link rel="stylesheet" href="/miniprojet-master/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <section class="contact-section">
            <h1>Contactez-nous</h1>
            <p>Vous avez des questions sur nos services ou vous souhaitez obtenir des conseils personnalisés ? N'hésitez pas à nous contacter en remplissant le formulaire ci-dessous.</p>

            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="contact-container">
                <div class="contact-form">
                    <h2>Formulaire de contact</h2>
                    <form action="contact.php" method="post">
                        <div class="form-group">
                            <label for="nom">Nom complet</label>
                            <input type="text" id="nom" name="nom" value="<?php echo isset($nom) ? htmlspecialchars($nom) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="sujet">Sujet</label>
                            <input type="text" id="sujet" name="sujet" value="<?php echo isset($sujet) ? htmlspecialchars($sujet) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="message">Votre message</label>
                            <textarea id="message" name="message" rows="6" required><?php echo isset($contenu) ? htmlspecialchars($contenu) : ''; ?></textarea>
                        </div>

                        <button type="submit">Envoyer</button>
                    </form>
                </div>
            </div>

            <div class="faq-section">
                <h2>Questions fréquentes</h2>

                <div class="faq-item">
                    <h3>Comment puis-je prendre rendez-vous pour une consultation?</h3>
                    <p>Vous pouvez prendre rendez-vous en nous contactant par téléphone ou en remplissant le formulaire ci-dessus en précisant votre demande.</p>
                </div>

                <div class="faq-item">
                    <h3>Proposez-vous des suivis nutritionnels à distance?</h3>
                    <p>Oui, nous proposons des consultations par visioconférence pour les personnes ne pouvant pas se déplacer dans nos locaux.</p>
                </div>

                <div class="faq-item">
                    <h3>Les consultations sont-elles remboursées par la sécurité sociale?</h3>
                    <p>Les consultations en nutrition ne sont généralement pas remboursées par la sécurité sociale, mais certaines mutuelles peuvent prendre en charge une partie des frais.</p>
                </div>

                <div class="faq-item">
                    <h3>Comment fonctionnent les outils de suivi sur votre site?</h3>
                    <p>Nos outils de suivi permettent de saisir vos données (poids, activité physique, etc.) et de visualiser votre progression au fil du temps. Ils sont accessibles après création d'un compte gratuit.</p>
                </div>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>

    <style>
    .faq-section {
      margin-left: 0 !important;
      width: 100% !important;
      max-width: 100% !important;
    }
    </style>
</body>
</html>
