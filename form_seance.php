<?php
require_once 'fonctions.php';
exiger_connexion();
require_once 'db.php';

$id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$modif = $id > 0;
$erreurs = [];
$statuts = ['planifiee' => 'Planifiée', 'terminee' => 'Terminée', 'annulee' => 'Annulée'];

// Listes pour les selects
$eleves    = $pdo->query("SELECT e.id, u.nom FROM eleves e JOIN users u ON u.id = e.user_id ORDER BY u.nom")->fetchAll();
$moniteurs = $pdo->query("SELECT id, prenom, nom, disponible FROM moniteurs ORDER BY nom")->fetchAll();
$vehicules = $pdo->query("SELECT id, marque, modele, immatriculation, etat FROM vehicules ORDER BY marque")->fetchAll();

if (!$eleves || !$moniteurs || !$vehicules) {
    flash('erreur', 'Pour créer une séance, il faut au moins un élève, un moniteur et un véhicule.');
    header('Location: seances.php');
    exit;
}

$s = ['eleve_id' => 0, 'moniteur_id' => 0, 'vehicule_id' => 0, 'date_seance' => '',
      'duree_minutes' => 60, 'statut' => 'planifiee', 'remarques' => ''];

if ($modif) {
    $stmt = $pdo->prepare("SELECT id, eleve_id, moniteur_id, vehicule_id, date_seance, duree_minutes, statut, remarques FROM seances WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $trouve = $stmt->fetch();
    if (!$trouve) {
        flash('erreur', 'Séance introuvable.');
        header('Location: seances.php');
        exit;
    }
    $s = $trouve;
    $s['date_seance'] = date('Y-m-d\TH:i', strtotime($trouve['date_seance']));   // format du champ datetime-local
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s['eleve_id']      = (int)($_POST['eleve_id'] ?? 0);
    $s['moniteur_id']   = (int)($_POST['moniteur_id'] ?? 0);
    $s['vehicule_id']   = (int)($_POST['vehicule_id'] ?? 0);
    $s['date_seance']   = $_POST['date_seance'] ?? '';
    $s['duree_minutes'] = trim($_POST['duree_minutes'] ?? '');
    $s['statut']        = $_POST['statut'] ?? '';
    $s['remarques']     = trim($_POST['remarques'] ?? '');

    // --- Validation PHP ---
    $idsEleves    = array_map('intval', array_column($eleves, 'id'));
    $idsMoniteurs = array_map('intval', array_column($moniteurs, 'id'));
    $idsVehicules = array_map('intval', array_column($vehicules, 'id'));

    if (!in_array($s['eleve_id'], $idsEleves, true))       $erreurs[] = 'Veuillez choisir un élève.';
    if (!in_array($s['moniteur_id'], $idsMoniteurs, true)) $erreurs[] = 'Veuillez choisir un moniteur.';
    if (!in_array($s['vehicule_id'], $idsVehicules, true)) $erreurs[] = 'Veuillez choisir un véhicule.';

    $d = DateTime::createFromFormat('Y-m-d\TH:i', $s['date_seance']);
    if (!$d || $d->format('Y-m-d\TH:i') !== $s['date_seance']) $erreurs[] = 'La date et l\'heure sont invalides.';

    if (!ctype_digit($s['duree_minutes']) || (int)$s['duree_minutes'] < 15 || (int)$s['duree_minutes'] > 240) {
        $erreurs[] = 'La durée doit être comprise entre 15 et 240 minutes.';
    }
    if (!array_key_exists($s['statut'], $statuts)) $erreurs[] = 'Statut invalide.';
    if (strlen($s['remarques']) > 1000) $erreurs[] = 'Les remarques sont trop longues (1000 caractères max).';

    // Véhicule en maintenance : impossible de planifier
    if (!$erreurs && $s['statut'] === 'planifiee') {
        foreach ($vehicules as $veh) {
            if ((int)$veh['id'] === $s['vehicule_id'] && $veh['etat'] === 'maintenance') {
                $erreurs[] = 'Ce véhicule est en maintenance.';
            }
        }
    }

    // Conflits de créneau (élève, moniteur, véhicule) pour les séances non annulées
    if (!$erreurs && $s['statut'] !== 'annulee') {
        $debut = $d->format('Y-m-d H:i:s');
        $fin   = (clone $d)->modify('+' . (int)$s['duree_minutes'] . ' minutes')->format('Y-m-d H:i:s');
        $colonnes = ['eleve_id' => 'Cet élève', 'moniteur_id' => 'Ce moniteur', 'vehicule_id' => 'Ce véhicule'];
        foreach ($colonnes as $col => $libelle) {
            $sql = "SELECT COUNT(*) FROM seances
                    WHERE $col = :val AND statut <> 'annulee'
                      AND date_seance < :fin
                      AND DATE_ADD(date_seance, INTERVAL duree_minutes MINUTE) > :debut"
                 . ($modif ? " AND id <> :id" : "");
            $params = ['val' => $s[$col], 'fin' => $fin, 'debut' => $debut];
            if ($modif) $params['id'] = $id;
            $verif = $pdo->prepare($sql);
            $verif->execute($params);
            if ((int)$verif->fetchColumn() > 0) $erreurs[] = $libelle . ' a déjà une séance sur ce créneau.';
        }
    }

    if (!$erreurs) {
        $nomEleve = '';
        foreach ($eleves as $el) { if ((int)$el['id'] === $s['eleve_id']) $nomEleve = $el['nom']; }
        $details = 'Séance du ' . $d->format('d/m/Y H:i') . ' - élève ' . $nomEleve;
        $remarques = $s['remarques'] !== '' ? $s['remarques'] : null;
        $dateSql = $d->format('Y-m-d H:i:s');

        try {
            if ($modif) {
                $pdo->prepare(
                    "UPDATE seances SET eleve_id = :e, moniteur_id = :m, vehicule_id = :v, date_seance = :d,
                            duree_minutes = :du, statut = :st, remarques = :r WHERE id = :id"
                )->execute([
                    'e' => $s['eleve_id'], 'm' => $s['moniteur_id'], 'v' => $s['vehicule_id'], 'd' => $dateSql,
                    'du' => (int)$s['duree_minutes'], 'st' => $s['statut'], 'r' => $remarques, 'id' => $id,
                ]);
                historique($pdo, 'modification', 'seances', $id, 'Modification de la ' . lcfirst($details));
                $message = 'Séance modifiée avec succès.';
            } else {
                $pdo->prepare(
                    "INSERT INTO seances (eleve_id, moniteur_id, vehicule_id, date_seance, duree_minutes, statut, remarques)
                     VALUES (:e, :m, :v, :d, :du, :st, :r)"
                )->execute([
                    'e' => $s['eleve_id'], 'm' => $s['moniteur_id'], 'v' => $s['vehicule_id'], 'd' => $dateSql,
                    'du' => (int)$s['duree_minutes'], 'st' => $s['statut'], 'r' => $remarques,
                ]);
                historique($pdo, 'creation', 'seances', (int)$pdo->lastInsertId(), 'Création de la ' . lcfirst($details));
                $message = 'Séance ajoutée avec succès.';
            }
            flash('succes', $message);
            header('Location: seances.php');
            exit;
        } catch (PDOException $ex) {
            $erreurs[] = "Erreur lors de l'enregistrement.";
        }
    }
}

$page_active = 'seances';
$titre_page  = $modif ? 'Modifier une séance' : 'Ajouter une séance';
include 'navbar.php';
?>
<link rel="stylesheet" href="tableaux.css">

<section class="section">
  <div class="container">
    <h2 class="titre-section"><?= $titre_page ?></h2>

    <form class="formulaire" id="form-seance" method="POST" novalidate>
      <ul class="liste-erreurs" id="erreurs-js"></ul>
      <?php if ($erreurs): ?>
        <ul class="liste-erreurs">
          <?php foreach ($erreurs as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <div class="champ">
        <label for="eleve_id">Élève *</label>
        <select id="eleve_id" name="eleve_id">
          <option value="">-- Choisir un élève --</option>
          <?php foreach ($eleves as $el): ?>
            <option value="<?= (int)$el['id'] ?>" <?= (int)$s['eleve_id'] === (int)$el['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($el['nom']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="champ">
        <label for="moniteur_id">Moniteur *</label>
        <select id="moniteur_id" name="moniteur_id">
          <option value="">-- Choisir un moniteur --</option>
          <?php foreach ($moniteurs as $mo): ?>
            <option value="<?= (int)$mo['id'] ?>" <?= (int)$s['moniteur_id'] === (int)$mo['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($mo['prenom'] . ' ' . $mo['nom']) ?><?= $mo['disponible'] ? '' : ' (indisponible)' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="champ">
        <label for="vehicule_id">Véhicule *</label>
        <select id="vehicule_id" name="vehicule_id">
          <option value="">-- Choisir un véhicule --</option>
          <?php foreach ($vehicules as $ve): ?>
            <option value="<?= (int)$ve['id'] ?>" <?= (int)$s['vehicule_id'] === (int)$ve['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($ve['marque'] . ' ' . $ve['modele'] . ' (' . $ve['immatriculation'] . ')') ?><?= $ve['etat'] === 'maintenance' ? ' - maintenance' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="champ">
        <label for="date_seance">Date et heure *</label>
        <input type="datetime-local" id="date_seance" name="date_seance" value="<?= htmlspecialchars($s['date_seance']) ?>">
      </div>

      <div class="champ">
        <label for="duree_minutes">Durée (minutes) *</label>
        <input type="number" id="duree_minutes" name="duree_minutes" min="15" max="240" step="15"
               value="<?= htmlspecialchars((string)$s['duree_minutes']) ?>">
      </div>

      <div class="champ">
        <label for="statut">Statut *</label>
        <select id="statut" name="statut">
          <?php foreach ($statuts as $val => $lib): ?>
            <option value="<?= $val ?>" <?= $s['statut'] === $val ? 'selected' : '' ?>><?= $lib ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="champ">
        <label for="remarques">Remarques</label>
        <textarea id="remarques" name="remarques" rows="3"><?= htmlspecialchars($s['remarques'] ?? '') ?></textarea>
      </div>

      <button class="bouton" type="submit" style="border:none;cursor:pointer;">
        <?= $modif ? 'Enregistrer les modifications' : 'Ajouter la séance' ?>
      </button>
      <a href="seances.php" style="margin-left:12px;color:#013E37;">Annuler</a>
    </form>
  </div>
</section>

<script>
document.getElementById('form-seance').addEventListener('submit', function (ev) {
    const erreurs = [];
    const duree = document.getElementById('duree_minutes').value.trim();

    if (document.getElementById('eleve_id').value === '')    erreurs.push('Veuillez choisir un élève.');
    if (document.getElementById('moniteur_id').value === '') erreurs.push('Veuillez choisir un moniteur.');
    if (document.getElementById('vehicule_id').value === '') erreurs.push('Veuillez choisir un véhicule.');
    if (document.getElementById('date_seance').value === '') erreurs.push('La date et l\'heure sont obligatoires.');
    if (!/^\d+$/.test(duree) || +duree < 15 || +duree > 240) erreurs.push('La durée doit être comprise entre 15 et 240 minutes.');
    if (document.getElementById('remarques').value.length > 1000) erreurs.push('Les remarques sont trop longues.');

    document.getElementById('erreurs-js').innerHTML = erreurs.map(e => '<li>' + e + '</li>').join('');
    if (erreurs.length > 0) ev.preventDefault();
});
</script>
</body>
</html>