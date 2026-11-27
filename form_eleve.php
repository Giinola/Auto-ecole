<?php
require_once 'fonctions.php';
exiger_connexion();
require_once 'db.php';

$id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$modif = $id > 0;
$erreurs = [];
$e = ['user_id' => null, 'nom' => '', 'email' => '', 'telephone' => '',
      'date_naissance' => '', 'categorie_permis' => 'B', 'photo' => null];

if ($modif) {
    $stmt = $pdo->prepare(
        "SELECT e.id, e.user_id, e.telephone, e.date_naissance, e.categorie_permis, e.photo, u.nom, u.email
         FROM eleves e JOIN users u ON u.id = e.user_id WHERE e.id = :id"
    );
    $stmt->execute(['id' => $id]);
    $trouve = $stmt->fetch();
    if (!$trouve) {
        flash('erreur', 'Élève introuvable.');
        header('Location: eleves.php');
        exit;
    }
    $e = $trouve;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $e['nom']              = trim($_POST['nom'] ?? '');
    $e['email']            = trim($_POST['email'] ?? '');
    $e['telephone']        = trim($_POST['telephone'] ?? '');
    $e['date_naissance']   = $_POST['date_naissance'] ?? '';
    $e['categorie_permis'] = $_POST['categorie_permis'] ?? '';

    // --- Validation PHP ---
    if ($e['nom'] === '') $erreurs[] = 'Le nom est obligatoire.';
    if (!filter_var($e['email'], FILTER_VALIDATE_EMAIL)) $erreurs[] = "L'adresse e-mail est invalide.";
    if ($e['telephone'] !== '' && !preg_match('/^[0-9 +]{6,20}$/', $e['telephone'])) {
        $erreurs[] = 'Le téléphone est invalide (chiffres, espaces et + uniquement).';
    }
    if ($e['date_naissance'] !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $e['date_naissance']);
        if (!$d || $d->format('Y-m-d') !== $e['date_naissance'] || $d > new DateTime()) {
            $erreurs[] = 'La date de naissance est invalide.';
        }
    }
    if (!in_array($e['categorie_permis'], ['A', 'B', 'C'], true)) $erreurs[] = 'Catégorie de permis invalide.';

    // E-mail unique
    if (!$erreurs) {
        $sql = "SELECT id FROM users WHERE email = :email" . ($modif ? " AND id <> :uid" : "");
        $params = ['email' => $e['email']];
        if ($modif) $params['uid'] = $e['user_id'];
        $verif = $pdo->prepare($sql);
        $verif->execute($params);
        if ($verif->fetch()) $erreurs[] = 'Cette adresse e-mail est déjà utilisée.';
    }

   
    $nouvellePhoto = null;
    if (!$erreurs) {
        $erreurPhoto = null;
        $nouvellePhoto = telecharger_photo($_FILES['photo'] ?? [], $erreurPhoto);
        if ($erreurPhoto) $erreurs[] = $erreurPhoto;
    }

    // Enregistrement
    if (!$erreurs) {
        $tel = $e['telephone'] !== '' ? $e['telephone'] : null;
        $dn  = $e['date_naissance'] !== '' ? $e['date_naissance'] : null;
        try {
            $pdo->beginTransaction();
            if ($modif) {
                $pdo->prepare("UPDATE users SET nom = :nom, email = :email WHERE id = :uid")
                    ->execute(['nom' => $e['nom'], 'email' => $e['email'], 'uid' => $e['user_id']]);
                $pdo->prepare(
                    "UPDATE eleves SET telephone = :tel, date_naissance = :dn,
                            categorie_permis = :cat, photo = :photo WHERE id = :id"
                )->execute([
                    'tel' => $tel, 'dn' => $dn, 'cat' => $e['categorie_permis'],
                    'photo' => $nouvellePhoto ?? $e['photo'], 'id' => $id,
                ]);
                historique($pdo, 'modification', 'eleves', $id, 'Modification de l\'élève ' . $e['nom']);
                $message = 'Élève modifié avec succès.';
            } else {
                // Compte créé par l'admin : mot de passe aléatoire (l'élève peut s'inscrire lui-même)
                $mdp = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO users (nom, email, mot_de_passe, role) VALUES (:nom, :email, :mdp, 'eleve')")
                    ->execute(['nom' => $e['nom'], 'email' => $e['email'], 'mdp' => $mdp]);
                $uid = (int)$pdo->lastInsertId();
                $pdo->prepare(
                    "INSERT INTO eleves (user_id, telephone, date_naissance, categorie_permis, date_inscription, photo)
                     VALUES (:uid, :tel, :dn, :cat, CURDATE(), :photo)"
                )->execute([
                    'uid' => $uid, 'tel' => $tel, 'dn' => $dn,
                    'cat' => $e['categorie_permis'], 'photo' => $nouvellePhoto,
                ]);
                $nouvelId = (int)$pdo->lastInsertId();
                historique($pdo, 'creation', 'eleves', $nouvelId, 'Inscription de l\'élève ' . $e['nom']);
                $message = 'Élève ajouté avec succès.';
            }
            $pdo->commit();

            if ($modif && $nouvellePhoto && $e['photo']) {
                @unlink(__DIR__ . '/uploads/' . $e['photo']);   // supprime l'ancienne photo
            }
            flash('succes', $message);
            header('Location: eleves.php');
            exit;
        } catch (PDOException $ex) {
            $pdo->rollBack();
            if ($nouvellePhoto) @unlink(__DIR__ . '/uploads/' . $nouvellePhoto);
            $erreurs[] = "Erreur lors de l'enregistrement (e-mail déjà utilisé ?).";
        }
    }
}

$page_active = 'eleves';
$titre_page  = $modif ? 'Modifier un élève' : 'Ajouter un élève';
include 'navbar.php';
?>
<link rel="stylesheet" href="tableaux.css">

<section class="section">
  <div class="container">
    <h2 class="titre-section"><?= $titre_page ?></h2>

    <form class="formulaire" id="form-eleve" method="POST" enctype="multipart/form-data" novalidate>
      <ul class="liste-erreurs" id="erreurs-js"></ul>
      <?php if ($erreurs): ?>
        <ul class="liste-erreurs">
          <?php foreach ($erreurs as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <div class="champ">
        <label for="nom">Nom complet *</label>
        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($e['nom']) ?>">
      </div>

      <div class="champ">
        <label for="email">E-mail *</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($e['email']) ?>">
      </div>

      <div class="champ">
        <label for="telephone">Téléphone</label>
        <input type="text" id="telephone" name="telephone" value="<?= htmlspecialchars($e['telephone'] ?? '') ?>">
      </div>

      <div class="champ">
        <label for="date_naissance">Date de naissance</label>
        <input type="date" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($e['date_naissance'] ?? '') ?>">
      </div>

      <div class="champ">
        <label for="categorie_permis">Catégorie de permis *</label>
        <select id="categorie_permis" name="categorie_permis">
          <?php foreach (['A' => 'A (moto)', 'B' => 'B (voiture)', 'C' => 'C (camion)'] as $val => $lib): ?>
            <option value="<?= $val ?>" <?= $e['categorie_permis'] === $val ? 'selected' : '' ?>><?= $lib ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="champ">
        <label for="photo">Photo de l'élève (JPG, PNG ou WEBP, 2 Mo max)</label>
        <?php if (!empty($e['photo'])): ?>
          <img class="photo-apercu" src="uploads/<?= htmlspecialchars($e['photo']) ?>" alt="Photo actuelle">
        <?php endif; ?>
        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
      </div>

      <button class="bouton" type="submit" style="border:none;cursor:pointer;">
        <?= $modif ? 'Enregistrer les modifications' : 'Ajouter l\'élève' ?>
      </button>
      <a href="eleves.php" style="margin-left:12px;color:#013E37;">Annuler</a>
    </form>
  </div>
</section>

<script>
document.getElementById('form-eleve').addEventListener('submit', function (ev) {
    const erreurs = [];
    const nom   = document.getElementById('nom').value.trim();
    const email = document.getElementById('email').value.trim();
    const tel   = document.getElementById('telephone').value.trim();
    const dn    = document.getElementById('date_naissance').value;
    const photo = document.getElementById('photo').files[0];

    if (nom === '') erreurs.push('Le nom est obligatoire.');
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) erreurs.push("L'adresse e-mail est invalide.");
    if (tel !== '' && !/^[0-9 +]{6,20}$/.test(tel)) erreurs.push('Le téléphone est invalide.');
    if (dn !== '' && new Date(dn) > new Date()) erreurs.push('La date de naissance est dans le futur.');
    if (photo) {
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(photo.type)) erreurs.push('La photo doit être en JPG, PNG ou WEBP.');
        if (photo.size > 2 * 1024 * 1024) erreurs.push('La photo dépasse 2 Mo.');
    }

    const boite = document.getElementById('erreurs-js');
    boite.innerHTML = erreurs.map(e => '<li>' + e + '</li>').join('');
    if (erreurs.length > 0) ev.preventDefault();
});
</script>
</body>
</html>