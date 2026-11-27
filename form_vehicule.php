<?php
require_once 'fonctions.php';
exiger_connexion();
require_once 'db.php';

$id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$modif = $id > 0;
$erreurs = [];
$v = ['immatriculation' => '', 'marque' => '', 'modele' => '', 'categorie' => 'B', 'annee' => '', 'etat' => 'disponible'];
$etats = ['disponible' => 'Disponible', 'en_seance' => 'En séance', 'maintenance' => 'Maintenance'];

if ($modif) {
    $stmt = $pdo->prepare("SELECT id, immatriculation, marque, modele, categorie, annee, etat FROM vehicules WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $trouve = $stmt->fetch();
    if (!$trouve) {
        flash('erreur', 'Véhicule introuvable.');
        header('Location: vehicules.php');
        exit;
    }
    $v = $trouve;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v['immatriculation'] = trim($_POST['immatriculation'] ?? '');
    $v['marque']          = trim($_POST['marque'] ?? '');
    $v['modele']          = trim($_POST['modele'] ?? '');
    $v['categorie']       = $_POST['categorie'] ?? '';
    $v['annee']           = trim($_POST['annee'] ?? '');
    $v['etat']            = $_POST['etat'] ?? '';                 // radio

    // --- Validation PHP ---
    if ($v['immatriculation'] === '') {
        $erreurs[] = "L'immatriculation est obligatoire.";
    } elseif (strlen($v['immatriculation']) > 30) {
        $erreurs[] = "L'immatriculation est trop longue (30 caractères max).";
    }
    if ($v['marque'] === '') $erreurs[] = 'La marque est obligatoire.';
    if ($v['modele'] === '') $erreurs[] = 'Le modèle est obligatoire.';
    if (!in_array($v['categorie'], ['A', 'B', 'C'], true)) $erreurs[] = 'Catégorie invalide.';
    if ($v['annee'] !== '') {
        $max = (int)date('Y') + 1;
        if (!ctype_digit($v['annee']) || (int)$v['annee'] < 1980 || (int)$v['annee'] > $max) {
            $erreurs[] = "L'année doit être comprise entre 1980 et $max.";
        }
    }
    if (!array_key_exists($v['etat'], $etats)) $erreurs[] = "Veuillez choisir l'état du véhicule.";

    // Immatriculation unique
    if (!$erreurs) {
        $sql = "SELECT id FROM vehicules WHERE immatriculation = :immat" . ($modif ? " AND id <> :id" : "");
        $params = ['immat' => $v['immatriculation']];
        if ($modif) $params['id'] = $id;
        $verif = $pdo->prepare($sql);
        $verif->execute($params);
        if ($verif->fetch()) $erreurs[] = 'Cette immatriculation existe déjà.';
    }

    if (!$erreurs) {
        $annee = $v['annee'] !== '' ? (int)$v['annee'] : null;
        $libelle = $v['marque'] . ' ' . $v['modele'] . ' (' . $v['immatriculation'] . ')';
        try {
            if ($modif) {
                $pdo->prepare(
                    "UPDATE vehicules SET immatriculation = :immat, marque = :marque, modele = :modele,
                            categorie = :cat, annee = :annee, etat = :etat WHERE id = :id"
                )->execute([
                    'immat' => $v['immatriculation'], 'marque' => $v['marque'], 'modele' => $v['modele'],
                    'cat' => $v['categorie'], 'annee' => $annee, 'etat' => $v['etat'], 'id' => $id,
                ]);
                historique($pdo, 'modification', 'vehicules', $id, 'Modification du véhicule ' . $libelle);
                $message = 'Véhicule modifié avec succès.';
            } else {
                $pdo->prepare(
                    "INSERT INTO vehicules (immatriculation, marque, modele, categorie, annee, etat)
                     VALUES (:immat, :marque, :modele, :cat, :annee, :etat)"
                )->execute([
                    'immat' => $v['immatriculation'], 'marque' => $v['marque'], 'modele' => $v['modele'],
                    'cat' => $v['categorie'], 'annee' => $annee, 'etat' => $v['etat'],
                ]);
                historique($pdo, 'creation', 'vehicules', (int)$pdo->lastInsertId(), 'Ajout du véhicule ' . $libelle);
                $message = 'Véhicule ajouté avec succès.';
            }
            flash('succes', $message);
            header('Location: vehicules.php');
            exit;
        } catch (PDOException $ex) {
            $erreurs[] = "Erreur lors de l'enregistrement.";
        }
    }
}

$page_active = 'vehicules';
$titre_page  = $modif ? 'Modifier un véhicule' : 'Ajouter un véhicule';
include 'navbar.php';
?>
<link rel="stylesheet" href="tableaux.css">

<section class="section">
  <div class="container">
    <h2 class="titre-section"><?= $titre_page ?></h2>

    <form class="formulaire" id="form-vehicule" method="POST" novalidate>
      <ul class="liste-erreurs" id="erreurs-js"></ul>
      <?php if ($erreurs): ?>
        <ul class="liste-erreurs">
          <?php foreach ($erreurs as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <div class="champ">
        <label for="immatriculation">Immatriculation *</label>
        <input type="text" id="immatriculation" name="immatriculation" value="<?= htmlspecialchars($v['immatriculation']) ?>">
      </div>

      <div class="champ">
        <label for="marque">Marque *</label>
        <input type="text" id="marque" name="marque" value="<?= htmlspecialchars($v['marque']) ?>">
      </div>

      <div class="champ">
        <label for="modele">Modèle *</label>
        <input type="text" id="modele" name="modele" value="<?= htmlspecialchars($v['modele']) ?>">
      </div>

      <div class="champ">
        <label for="categorie">Catégorie *</label>
        <select id="categorie" name="categorie">
          <?php foreach (['A' => 'A (moto)', 'B' => 'B (voiture)', 'C' => 'C (camion)'] as $val => $lib): ?>
            <option value="<?= $val ?>" <?= $v['categorie'] === $val ? 'selected' : '' ?>><?= $lib ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="champ">
        <label for="annee">Année</label>
        <input type="number" id="annee" name="annee" min="1980" max="<?= (int)date('Y') + 1 ?>"
               value="<?= htmlspecialchars((string)($v['annee'] ?? '')) ?>">
      </div>

      <div class="champ">
        <label>État du véhicule *</label>
        <div class="radios">
          <?php foreach ($etats as $val => $lib): ?>
            <label class="ligne-case" style="font-weight:normal;">
              <input type="radio" name="etat" value="<?= $val ?>" <?= $v['etat'] === $val ? 'checked' : '' ?>>
              <?= $lib ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <button class="bouton" type="submit" style="border:none;cursor:pointer;">
        <?= $modif ? 'Enregistrer les modifications' : 'Ajouter le véhicule' ?>
      </button>
      <a href="vehicules.php" style="margin-left:12px;color:#013E37;">Annuler</a>
    </form>
  </div>
</section>

<script>
document.getElementById('form-vehicule').addEventListener('submit', function (ev) {
    const erreurs = [];
    const immat  = document.getElementById('immatriculation').value.trim();
    const marque = document.getElementById('marque').value.trim();
    const modele = document.getElementById('modele').value.trim();
    const annee  = document.getElementById('annee').value.trim();
    const etat   = document.querySelector('input[name="etat"]:checked');
    const maxAnnee = new Date().getFullYear() + 1;

    if (immat === '')  erreurs.push("L'immatriculation est obligatoire.");
    if (marque === '') erreurs.push('La marque est obligatoire.');
    if (modele === '') erreurs.push('Le modèle est obligatoire.');
    if (annee !== '' && (!/^\d{4}$/.test(annee) || +annee < 1980 || +annee > maxAnnee)) {
        erreurs.push("L'année doit être comprise entre 1980 et " + maxAnnee + '.');
    }
    if (!etat) erreurs.push("Veuillez choisir l'état du véhicule.");

    document.getElementById('erreurs-js').innerHTML = erreurs.map(e => '<li>' + e + '</li>').join('');
    if (erreurs.length > 0) ev.preventDefault();
});
</script>
</body>
</html>