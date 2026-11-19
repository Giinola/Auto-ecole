<?php
$page_active = 'moniteurs';
$titre_page  = 'Moniteurs';
include 'navbar.php';
require_once 'db.php';

$q    = trim($_GET['q'] ?? '');
$cat  = $_GET['categorie'] ?? '';
$dispo = $_GET['disponible'] ?? '';

$sql = "SELECT id, nom, prenom, telephone, categorie_enseignee, disponible
        FROM moniteurs
        WHERE (nom LIKE :q1 OR prenom LIKE :q2)";
$params = ['q1' => "%$q%", 'q2' => "%$q%"];

if ($cat !== '') {
    $sql .= " AND categorie_enseignee = :cat";
    $params['cat'] = $cat;
}
if ($dispo !== '') {
    $sql .= " AND disponible = :dispo";
    $params['dispo'] = (int)$dispo;
}
$sql .= " ORDER BY nom";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$moniteurs = $stmt->fetchAll();
?>
<link rel="stylesheet" href="tableaux.css">

<section class="section">
  <div class="container">
    <h2 class="titre-section">Moniteurs</h2>

    <form class="barre-outils" method="GET">
      <input type="text" name="q" placeholder="Rechercher un moniteur..."
             value="<?= htmlspecialchars($q) ?>">

      <select name="categorie">
        <option value="">Toutes les catégories</option>
        <?php foreach (['A','B','C'] as $c): ?>
          <option value="<?= $c ?>" <?= $cat === $c ? 'selected' : '' ?>>Permis <?= $c ?></option>
        <?php endforeach; ?>
      </select>

      <select name="disponible">
        <option value="">Tous les statuts</option>
        <option value="1" <?= $dispo === '1' ? 'selected' : '' ?>>Disponible</option>
        <option value="0" <?= $dispo === '0' ? 'selected' : '' ?>>Indisponible</option>
      </select>

      <button class="bouton" type="submit" style="border:none;cursor:pointer;">Filtrer</button>
    </form>

    <?php if (count($moniteurs) === 0): ?>
      <p class="vide">Aucun moniteur trouvé.</p>
    <?php endif; ?>

    <div class="grille-cartes">
      <?php foreach ($moniteurs as $m): ?>
        <div class="carte">
          <h3> <?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?></h3>
          <p>
            Tél : <?= htmlspecialchars($m['telephone'] ?? '—') ?><br>
            Catégorie enseignée : <?= htmlspecialchars($m['categorie_enseignee']) ?>
          </p>

          <?php if ($m['disponible']): ?>
            <span class="badge badge-vert">Disponible</span>
          <?php else: ?>
            <span class="badge badge-rouge">Indisponible</span>
          <?php endif; ?>

          <div class="actions" style="margin-top:14px;">
            <a class="bouton petit" href="modifier_moniteur.php?id=<?= (int)$m['id'] ?>">Modifier</a>
            <a class="bouton petit bouton-danger" href="supprimer_moniteur.php?id=<?= (int)$m['id'] ?>"
               onclick="return confirm('Supprimer ce moniteur ?')">Supprimer</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
</body>
</html>