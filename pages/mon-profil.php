<?php
include '../includes/header.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db.php';

// Rediriger si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// Traitement des modifications du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $age = isset($_POST['age']) ? intval($_POST['age']) : null;
    $sexe = isset($_POST['sexe']) ? $_POST['sexe'] : '';

    // Vérification de base
    if (empty($nom) || empty($prenom) || empty($email)) {
        $message = 'Les champs nom, prénom et email sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Veuillez entrer une adresse email valide.';
    } else {
        // Vérifier si l'email existe déjà pour un autre utilisateur
        $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = 'Cette adresse email est déjà utilisée par un autre compte.';
        } else {
            // Mise à jour des informations de base
            $stmt = $conn->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, email = ?, age = ?, sexe = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $nom, $prenom, $email, $age, $sexe, $user_id);

            if ($stmt->execute()) {
                // Mise à jour des informations de session
                $_SESSION['user_nom'] = $nom;
                $_SESSION['user_prenom'] = $prenom;
                $_SESSION['user_email'] = $email;

                $message = 'Votre profil a été mis à jour avec succès.';
            } else {
                $message = 'Une erreur est survenue lors de la mise à jour du profil.';
            }

            // Si l'utilisateur souhaite changer son mot de passe
            if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
                if ($new_password !== $confirm_password) {
                    $message .= ' Cependant, les nouveaux mots de passe ne correspondent pas.';
                } else {
                    // Vérification du mot de passe actuel
                    $stmt = $conn->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();

                    if (password_verify($current_password, $user['mot_de_passe'])) {
                        // Mettre à jour le mot de passe
                        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
                        $stmt->bind_param("si", $password_hash, $user_id);

                        if ($stmt->execute()) {
                            $message .= ' Votre mot de passe a été modifié avec succès.';
                        } else {
                            $message .= ' Une erreur est survenue lors de la mise à jour du mot de passe.';
                        }
                    } else {
                        $message .= ' Le mot de passe actuel est incorrect.';
                    }
                }
            }
        }
    }
}

// Traitement de l'ajout d'un suivi de santé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_health_tracking') {
    $date = $_POST['date'];
    $poids = !empty($_POST['poids']) ? $_POST['poids'] : null;
    $taille = !empty($_POST['taille']) ? $_POST['taille'] : null;
    $activite = !empty($_POST['activite_physique']) ? $_POST['activite_physique'] : null;
    $calories = !empty($_POST['calories']) ? $_POST['calories'] : null;
    $notes = $_POST['notes'];

    if (empty($date)) {
        $message = 'La date est obligatoire.';
    } else {
        // Insérer le suivi dans la base de données
        $stmt = $conn->prepare("INSERT INTO suivi_sante (id_utilisateur, date_enregistrement, poids, taille, activite_physique, calories_consommees, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isddiss", $user_id, $date, $poids, $taille, $activite, $calories, $notes);

        if ($stmt->execute()) {
            $message = 'Votre suivi de santé a été enregistré avec succès.';
        } else {
            $message = 'Une erreur est survenue lors de l\'enregistrement du suivi.';
        }
    }
}

// Gestion de la suppression d'un favori
if (isset($_POST['remove_favori']) && isset($_POST['id'])) {
    unset($_SESSION['favoris'][$_POST['id']]);
    header('Location: mon-profil.php');
    exit;
}

// Récupérer les informations de l'utilisateur
$stmt = $conn->prepare("SELECT nom, prenom, email, age, sexe FROM utilisateurs WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Récupérer l'historique bien-être
$stmt = $conn->prepare("SELECT * FROM bien_etre WHERE id_user = ? ORDER BY date_enregistrement DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$suivis = $stmt->get_result();
$suivis_data = [];
while ($row = $suivis->fetch_assoc()) {
    $suivis_data[] = $row;
}
?>
<main class="section">
<div class="tabs-profil">
    <button class="tab-btn-profil active" data-tab="infos">🧾 Informations personnelles</button>
    <a href="mes-favoris.php" class="tab-button">❤️ Favoris</a>
    <button class="tab-btn-profil" data-tab="suivi">🩺 Suivi de santé</button>
</div>

<div id="tab-infos" class="tab-content-profil active">
    <h2>Informations personnelles</h2>
    <form action="mon-profil.php" method="post">
        <input type="hidden" name="action" value="update_profile">

        <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="age">Âge</label>
            <input type="number" id="age" name="age" min="0" max="120" value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="sexe">Sexe</label>
            <select id="sexe" name="sexe" required>
                <option value="">--Choisir--</option>
                <option value="Homme" <?php if(($user['sexe'] ?? '')==='Homme') echo 'selected'; ?>>Homme</option>
                <option value="Femme" <?php if(($user['sexe'] ?? '')==='Femme') echo 'selected'; ?>>Femme</option>
                <option value="Autre" <?php if(($user['sexe'] ?? '')==='Autre') echo 'selected'; ?>>Autre</option>
            </select>
        </div>

        <h3>Changer de mot de passe</h3>
        <p>(Laissez vide si vous ne souhaitez pas modifier votre mot de passe)</p>

        <div class="form-group">
            <label for="current_password">Mot de passe actuel</label>
            <input type="password" id="current_password" name="current_password">
        </div>

        <div class="form-group">
            <label for="new_password">Nouveau mot de passe</label>
            <input type="password" id="new_password" name="new_password">
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirmer le nouveau mot de passe</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>

        <button type="submit">Mettre à jour le profil</button>
    </form>
</div>

<div id="tab-favoris" class="tab-content-profil">
    <h2>❤️ Mes favoris</h2>
    <?php
    $favoris = isset($_SESSION['favoris']) ? array_keys($_SESSION['favoris']) : [];
    $produits = [];
    if ($favoris) {
        $sql = "SELECT * FROM articles WHERE id IN (".implode(',', $favoris).")";
        $result = $conn->query($sql);
        while($row = $result->fetch_assoc()) {
            $produits[] = $row;
        }
    }
    ?>
    <?php if (!$produits): ?>
        <p>Vous n'avez aucun produit en favori.</p>
    <?php else: ?>
        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
        <?php foreach($produits as $prod): ?>
            <div style="border:1px solid #ccc; padding:15px; width:220px;">
                <img src="/images/<?= htmlspecialchars($prod['image'] ?? '') ?>" alt="<?= htmlspecialchars($prod['titre'] ?? '') ?>" style="width:100%;height:120px;object-fit:cover;">
                <h3><?= htmlspecialchars($prod['titre'] ?? '') ?></h3>
                <p><strong><?= number_format($prod['prix'], 2, ',', ' ') ?> €</strong></p>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                    <button type="submit" name="remove_favori">Retirer</button>
                </form>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="tab-suivi" class="tab-content-profil">
    <h2>Suivi de santé</h2>
    <h3>Graphique IMC</h3>
    <canvas id="imcChart" height="80"></canvas>
    <h3>Activité physique & Calories</h3>
    <canvas id="actChart" height="80"></canvas>
    <h3>Historique complet</h3>
    <?php if ($suivis_data): ?>
        <table class="health-tracking-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Poids</th>
                    <th>IMC</th>
                    <th>Sommeil</th>
                    <th>Calories</th>
                    <th>Objectif</th>
                    <th>Détails</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suivis_data as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['date_enregistrement']) ?></td>
                        <td><?= htmlspecialchars($s['poids']) ?></td>
                        <td><?= ($s['taille'] > 0) ? round($s['poids'] / pow($s['taille']/100, 2), 1) : '' ?></td>
                        <td><?= htmlspecialchars($s['sommeil']) ?></td>
                        <td><?= htmlspecialchars($s['calories']) ?></td>
                        <td><?= htmlspecialchars($s['objectif']) ?></td>
                        <td><a href="details-suivi.php?id=<?= $s['id'] ?>" class="btn">Détails</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun suivi enregistré.</p>
    <?php endif; ?>
</div>
</main>
<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn-profil');
    const tabContents = {
        'infos': document.getElementById('tab-infos'),
        'favoris': document.getElementById('tab-favoris'),
        'suivi': document.getElementById('tab-suivi')
    };
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            Object.values(tabContents).forEach(div => div.classList.remove('active'));
            tabContents[this.dataset.tab].classList.add('active');
        });
    });
});

window.imcLabels = <?= json_encode(array_reverse(array_column($suivis_data, 'date_enregistrement'))) ?>;
window.imcData = <?= json_encode(array_reverse(array_map(fn($s) => ($s['taille'] > 0 ? round($s['poids'] / pow($s['taille']/100, 2), 1) : null), $suivis_data))) ?>;
window.activiteData = <?= json_encode(array_reverse(array_map(fn($s) => intval($s['activite_physique']), $suivis_data))) ?>;
window.caloriesData = <?= json_encode(array_reverse(array_map(fn($s) => intval($s['calories']), $suivis_data))) ?>;
</script>
