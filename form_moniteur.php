<?php
require_once 'fonctions.php';
exiger_connexion();
require_once 'db.php';

$id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$modif = $id > 0;
$erreurs = [];
$m = ['nom' => '', 'prenom' => '', 'telephone' => '', 'categorie_enseignee' => 'B', 'disponible' => 1];

if ($modif) {
    $stmt = $pdo->prepare("SELECT id, nom, prenom, telephone, categorie_enseignee, disponible FROM moniteurs WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $trouve = $stmt->fetch();
    if (!$trouve) {
        flash('erreur', 'Moniteur introuvable.');
        header('Location: moniteurs.php');
        exit;
    }
    $m = $trouve;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $m['nom']                 = trim($_POST['nom'] ?? '');
    $m['prenom']              = trim($_POST['prenom'] ?? '');
    $m['telephone']           = trim($_POST['telephone'] ?? '');
    $m['categorie_enseignee'] = $_POST['categorie_enseignee'] ?? '';
    $m['disponible']          = isset($_POST['disponible']) ? 1 : 0;   // checkbox

    // --- Validation PHP ---
    if ($m['nom'] === '')    $erreurs[] = 'Le nom est obligatoire.';
    if ($m['prenom'] === '') $erreurs[] = 'Le prénom est obligatoire.';
    if ($m['telephone'] !== '' && !preg_match('/^[0-9 +]{6,20}$/', $m['telephone'])) {
        $erreurs[] = 'Le téléphone est invalide (chiffres, espaces et + uniquement).';
    }
    if (!in_array($m['categorie_enseignee'], ['A', 'B', 'C'], true)) {
        $erreurs[] = 'Catégorie enseignée invalide.';
    }

    if (!$erreurs) {
        $tel = $m['telephone'] !== '' ? $m['telephone'] : null;
        try {
            if ($modif) {
                $pdo->prepare(
                    "UPDATE moniteurs SET nom = :nom, prenom = :prenom, telephone = :tel,
                            categorie_enseignee = :cat, disponible = :dispo WHERE id = :id"
                )->execute([
                    'nom' => $m['nom'], 'prenom' => $m['prenom'], 'tel' => $tel,
                    'cat' => $m['categorie_enseignee'], 'dispo' => $m['disponible'], 'id' => $id,
                ]);
                historique($pdo, 'modification', 'moniteurs', $id,
                    'Modification du moniteur ' . $m['prenom'] . ' ' . $m['nom']);
                $message = 'Moniteur modifié avec succès.';
            } else {
                $pdo->prepare(
                    "INSERT INTO moniteurs (nom, prenom, telephone, categorie_enseignee, disponible)
                     VALUES (:nom, :prenom, :tel, :cat, :dispo)"
                )->execute([
                    'nom' => $m['nom'], 'prenom' => $m['prenom'], 'tel' => $tel,
                    'cat' => $m['categorie_enseignee'], 'dispo' => $m['disponible'],
                ]);
                historique($pdo, 'creation', 'moniteurs', (int)$pdo->lastInsertId(),
                    'Ajout du moniteur ' . $m['prenom'] . ' ' . $m['nom']);
                $message = 'Moniteur ajouté avec succès.';
            }
            flash('succes', $message);
            header('Location: moniteurs.php');
            exit;
        } catch (PDOException $ex) {
            $erreurs[] = "Erreur lors de l'enregistrement.";
        }
    }
}

$page_active = 'moniteurs';
$titre_page  = $modif ? 'Modifier un moniteur' : 'Ajouter un moniteur';
include 'navbar.php';
?>
<link rel="stylesheet" href="tableaux.css">

<section class="section">
  <div class="container">
    <h2 class="titre-section"><?= $titre_page ?></h2>

    <form class="formulaire" id="form-moniteur" method="POST" novalidate>
      <ul class="liste-erreurs" id="erreurs-js"></ul>
      <?php if ($erreurs): ?>
        <ul class="liste-erreurs">
          <?php foreach ($erreurs as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <div class="champ">
        <label for="nom">Nom *</label>
        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($m['nom']) ?>">
      </div>

      <div class="champ">
        <label for="prenom">Prénom *</label>
        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($m['prenom']) ?>">
      </div>

      <div class="champ">
        <label for="telephone">Téléphone</label>
        <input type="text" id="telephone" name="telephone" value="<?= htmlspecialchars($m['telephone'] ?? '') ?>">
      </div>

      <div class="champ">
        <label for="categorie_enseignee">Catégorie enseignée *</label>
        <select id="categorie_enseignee" name="categorie_enseignee">
          <?php foreach (['A' => 'A (moto)', 'B' => 'B (voiture)', 'C' => 'C (camion)'] as $val => $lib): ?>
            <option value="<?= $val ?>" <?= $m['categorie_enseignee'] === $val ? 'selected' : '' ?>><?= $lib ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="champ">
        <div class="ligne-case">
          <input type="checkbox" id="disponible" name="disponible" value="1" <?= $m['disponible'] ? 'checked' : '' ?>>
          <label for="disponible" style="margin:0;">Moniteur disponible</label>
        </div>
      </div>

      <button class="bouton" type="submit" style="border:none;cursor:pointer;">
        <?= $modif ? 'Enregistrer les modifications' : 'Ajouter le moniteur' ?>
      </button>
      <a href="moniteurs.php" style="margin-left:12px;color:#013E37;">Annuler</a>
    </form>
  </div>
</section>

<script>
document.getElementById('form-moniteur').addEventListener('submit', function (ev) {
    const erreurs = [];
    const nom    = document.getElementById('nom').value.trim();
    const prenom = document.getElementById('prenom').value.trim();
    const tel    = document.getElementById('telephone').value.trim();

    if (nom === '')    erreurs.push('Le nom est obligatoire.');
    if (prenom === '') erreurs.push('Le prénom est obligatoire.');
    if (tel !== '' && !/^[0-9 +]{6,20}$/.test(tel)) erreurs.push('Le téléphone est invalide.');

    document.getElementById('erreurs-js').innerHTML = erreurs.map(e => '<li>' + e + '</li>').join('');
    if (erreurs.length > 0) ev.preventDefault();
});
</script>
</body>
</html>