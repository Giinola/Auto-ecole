<?php
require_once 'fonctions.php';
exiger_connexion();
require_once 'db.php';

$id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$modif = $id > 0;
$erreurs = [];
$types     = ['code' => 'Code', 'conduite' => 'Conduite'];
$resultats = ['en_attente' => 'En attente', 'reussi' => 'Réussi', 'ajourne' => 'Ajourné'];

$eleves = $pdo->query("SELECT e.id, u.nom FROM eleves e JOIN users u ON u.id = e.user_id ORDER BY u.nom")->fetchAll();
if (!$eleves) {
    flash('erreur', 'Pour créer un examen, il faut d\'abord au moins un élève.');
    header('Location: examens.php');
    exit;
}

$x = ['eleve_id' => 0, 'type_examen' => 'code', 'date_examen' => '', 'resultat' => 'en_attente', 'note' => ''];

if ($modif) {
    $stmt = $pdo->prepare("SELECT id, eleve_id, type_examen, date_examen, resultat, note FROM examens WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $trouve = $stmt->fetch();
    if (!$trouve) {
        flash('erreur', 'Examen introuvable.');
        header('Location: examens.php');
        exit;
    }
    $x = $trouve;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $x['eleve_id']    = (int)($_POST['eleve_id'] ?? 0);
    $x['type_examen'] = $_POST['type_examen'] ?? '';            // radio
    $x['date_examen'] = $_POST['date_examen'] ?? '';
    $x['resultat']    = $_POST['resultat'] ?? '';
    $x['note']        = str_replace(',', '.', trim($_POST['note'] ?? ''));

    // --- Validation PHP ---
    $idsEleves = array_map('intval', array_column($eleves, 'id'));
    if (!in_array($x['eleve_id'], $idsEleves, true)) $erreurs[] = 'Veuillez choisir un élève.';
    if (!array_key_exists($x['type_examen'], $types)) $erreurs[] = "Veuillez choisir le type d'examen.";
    if (!array_key_exists($x['resultat'], $resultats)) $erreurs[] = 'Résultat invalide.';

    $d = DateTime::createFromFormat('Y-m-d', $x['date_examen']);
    if (!$d || $d->format('Y-m-d') !== $x['date_examen']) {
        $erreurs[] = 'La date de l\'examen est invalide.';
    } elseif ($x['resultat'] !== 'en_attente' && $d > new DateTime('today')) {
        $erreurs[] = 'Un examen dans le futur ne peut pas avoir de résultat (laissez « En attente »).';
    }

    if ($x['note'] !== '') {
        if (!is_numeric($x['note']) || (float)$x['note'] < 0 || (float)$x['note'] > 20) {
            $erreurs[] = 'La note doit être comprise entre 0 et 20.';
        } elseif ($x['resultat'] === 'en_attente') {
            $erreurs[] = 'Un examen « En attente » ne peut pas avoir de note.';
        }
    }

    if (!$erreurs) {
        $note = $x['note'] !== '' ? round((float)$x['note'], 2) : null;
        $nomEleve = '';
        foreach ($eleves as $el) { if ((int)$el['id'] === $x['eleve_id']) $nomEleve = $el['nom']; }
        $details = 'examen de ' . $x['type_examen'] . ' du ' . $d->format('d/m/Y') . ' - élève ' . $nomEleve;

        try {
            if ($modif) {
                $pdo->prepare(
                    "UPDATE examens SET eleve_id = :e, type_examen = :t, date_examen = :d,
                            resultat = :r, note = :n WHERE id = :id"
                )->execute([
                    'e' => $x['eleve_id'], 't' => $x['type_examen'], 'd' => $x['date_examen'],
                    'r' => $x['resultat'], 'n' => $note, 'id' => $id,
                ]);
                historique($pdo, 'modification', 'examens', $id, 'Modification de l\'' . $details);
                $message = 'Examen modifié avec succès.';
            } else {
                $pdo->prepare(
                    "INSERT INTO examens (eleve_id, type_examen, date_examen, resultat, note)
                     VALUES (:e, :t, :d, :r, :n)"
                )->execute([
                    'e' => $x['eleve_id'], 't' => $x['type_examen'], 'd' => $x['date_examen'],
                    'r' => $x['resultat'], 'n' => $note,
                ]);
                historique($pdo, 'creation', 'examens', (int)$pdo->lastInsertId(), 'Création de l\'' . $details);
                $message = 'Examen ajouté avec succès.';
            }
            flash('succes', $message);
            header('Location: examens.php');
            exit;
        } catch (PDOException $ex) {
            $erreurs[] = "Erreur lors de l'enregistrement.";
        }
    }
}

$page_active = 'examens';
$titre_page  = $modif ? 'Modifier un examen' : 'Ajouter un examen';
include 'navbar.php';
?>
<link rel="stylesheet" href="tableaux.css">

<section class="section">
  <div class="container">
    <h2 class="titre-section"><?= $titre_page ?></h2>

    <form class="formulaire" id="form-examen" method="POST" novalidate>
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
            <option value="<?= (int)$el['id'] ?>" <?= (int)$x['eleve_id'] === (int)$el['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($el['nom']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="champ">
        <label>Type d'examen *</label>
        <div class="radios">
          <?php foreach ($types as $val => $lib): ?>
            <label class="ligne-case" style="font-weight:normal;">
              <input type="radio" name="type_examen" value="<?= $val ?>" <?= $x['type_examen'] === $val ? 'checked' : '' ?>>
              <?= $lib ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="champ">
        <label for="date_examen">Date de l'examen *</label>
        <input type="date" id="date_examen" name="date_examen" value="<?= htmlspecialchars($x['date_examen']) ?>">
      </div>

      <div class="champ">
        <label for="resultat">Résultat *</label>
        <select id="resultat" name="resultat">
          <?php foreach ($resultats as $val => $lib): ?>
            <option value="<?= $val ?>" <?= $x['resultat'] === $val ? 'selected' : '' ?>><?= $lib ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="champ">
        <label for="note">Note (sur 20)</label>
        <input type="number" id="note" name="note" min="0" max="20" step="0.25"
               value="<?= htmlspecialchars((string)($x['note'] ?? '')) ?>">
      </div>

      <button class="bouton" type="submit" style="border:none;cursor:pointer;">
        <?= $modif ? 'Enregistrer les modifications' : 'Ajouter l\'examen' ?>
      </button>
      <a href="examens.php" style="margin-left:12px;color:#013E37;">Annuler</a>
    </form>
  </div>
</section>

<script>
document.getElementById('form-examen').addEventListener('submit', function (ev) {
    const erreurs = [];
    const date     = document.getElementById('date_examen').value;
    const resultat = document.getElementById('resultat').value;
    const note     = document.getElementById('note').value.trim();
    const aujourdhui = new Date().toISOString().slice(0, 10);

    if (document.getElementById('eleve_id').value === '') erreurs.push('Veuillez choisir un élève.');
    if (!document.querySelector('input[name="type_examen"]:checked')) erreurs.push("Veuillez choisir le type d'examen.");
    if (date === '') {
        erreurs.push("La date de l'examen est obligatoire.");
    } else if (resultat !== 'en_attente' && date > aujourdhui) {
        erreurs.push('Un examen dans le futur ne peut pas avoir de résultat.');
    }
    if (note !== '') {
        if (isNaN(note) || +note < 0 || +note > 20) erreurs.push('La note doit être comprise entre 0 et 20.');
        else if (resultat === 'en_attente') erreurs.push('Un examen « En attente » ne peut pas avoir de note.');
    }

    document.getElementById('erreurs-js').innerHTML = erreurs.map(e => '<li>' + e + '</li>').join('');
    if (erreurs.length > 0) ev.preventDefault();
});
</script>
</body>
</html>