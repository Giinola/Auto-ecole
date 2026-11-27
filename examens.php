<?php
$page_active = 'examens';
$titre_page  = 'Examens';
include 'navbar.php';
require_once 'db.php';

$q        = trim($_GET['q'] ?? '');
$type     = $_GET['type'] ?? '';
$resultat = $_GET['resultat'] ?? '';

$sql = "SELECT x.id, x.type_examen, x.date_examen, x.resultat, x.note,
               u.nom AS eleve_nom
        FROM examens x
        JOIN eleves e ON e.id = x.eleve_id
        JOIN users u  ON u.id = e.user_id
        WHERE u.nom LIKE :q";
$params = ['q' => "%$q%"];

if ($type !== '') {
    $sql .= " AND x.type_examen = :type";
    $params['type'] = $type;
}
if ($resultat !== '') {
    $sql .= " AND x.resultat = :resultat";
    $params['resultat'] = $resultat;
}
$sql .= " ORDER BY x.date_examen DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$examens = $stmt->fetchAll();
?>
<link rel="stylesheet" href="tableaux.css">

<section class="section">
  <div class="container">
    <h2 class="titre-section">Examens</h2>
    <?php require_once 'fonctions.php'; afficher_flash(); ?>
      <p style="margin-bottom:16px;"><a class="bouton" href="form_examen.php">+ Ajouter un examen</a></p>

    <form class="barre-outils" method="GET">
      <input type="text" name="q" placeholder="Rechercher un élève..."
             value="<?= htmlspecialchars($q) ?>">

      <select name="type">
        <option value="">Tous les types</option>
        <option value="code"     <?= $type === 'code'     ? 'selected' : '' ?>>Code</option>
        <option value="conduite" <?= $type === 'conduite' ? 'selected' : '' ?>>Conduite</option>
      </select>

      <select name="resultat">
        <option value="">Tous les résultats</option>
        <option value="en_attente" <?= $resultat === 'en_attente' ? 'selected' : '' ?>>En attente</option>
        <option value="reussi"     <?= $resultat === 'reussi'     ? 'selected' : '' ?>>Réussi</option>
        <option value="ajourne"    <?= $resultat === 'ajourne'    ? 'selected' : '' ?>>Ajourné</option>
      </select>

      <button class="bouton" type="submit" style="border:none;cursor:pointer;">Filtrer</button>
    </form>

    <div class="tableau-boite">
      <table>
        <thead>
          <tr>
            <th>Date</th><th>Élève</th><th>Type</th>
            <th>Résultat</th><th>Note</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($examens) === 0): ?>
            <tr><td colspan="6" class="vide">Aucun examen trouvé.</td></tr>
          <?php endif; ?>

          <?php foreach ($examens as $x):
            $classe = $x['resultat'] === 'reussi' ? 'badge-vert'
                    : ($x['resultat'] === 'en_attente' ? 'badge-jaune' : 'badge-rouge'); ?>
            <tr>
              <td><?= date('d/m/Y', strtotime($x['date_examen'])) ?></td>
              <td><?= htmlspecialchars($x['eleve_nom']) ?></td>
              <td><?= htmlspecialchars(ucfirst($x['type_examen'])) ?></td>
              <td><span class="badge <?= $classe ?>"><?= htmlspecialchars(str_replace('_', ' ', $x['resultat'])) ?></span></td>
              <td><?= $x['note'] !== null ? number_format((float)$x['note'], 2, ',', '') . ' / 20' : '—' ?></td>
              <td class="actions">
                <a class="bouton petit" href="form_examen.php?id=<?= (int)$x['id'] ?>">Modifier</a>
                <a class="bouton petit bouton-danger" href="supprimer_examen.php?id=<?= (int)$x['id'] ?>"
                   onclick="return confirm('Supprimer cet examen ?')">Supprimer</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
</body>
</html>