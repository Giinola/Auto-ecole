<?php
$page_active = 'eleves';
$titre_page  = 'Élèves';
include 'navbar.php';
require_once 'db.php';

$q   = trim($_GET['q'] ?? '');
$cat = $_GET['categorie'] ?? '';

$sql = "SELECT e.id, u.nom, u.email, e.telephone, e.categorie_permis, e.date_inscription
        FROM eleves e
        JOIN users u ON u.id = e.user_id
        WHERE (u.nom LIKE :q1 OR u.email LIKE :q2)";
$params = ['q1' => "%$q%", 'q2' => "%$q%"];

if ($cat !== '') {
    $sql .= " AND e.categorie_permis = :cat";
    $params['cat'] = $cat;
}
$sql .= " ORDER BY u.nom";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$eleves = $stmt->fetchAll();
?>
<link rel="stylesheet" href="tableaux.css">

<section class="section">
  <div class="container">
    <h2 class="titre-section">Élèves</h2>
    <?php require_once 'fonctions.php'; afficher_flash(); ?>
       <p style="margin-bottom:16px;"><a class="bouton" href="form_eleve.php">+ Ajouter un élève</a></p>

    <form class="barre-outils" method="GET">
      <input type="text" name="q" placeholder="Rechercher un nom ou un e-mail..."
             value="<?= htmlspecialchars($q) ?>">
      <select name="categorie">
        <option value="">Toutes les catégories</option>
        <?php foreach (['A','B','C'] as $c): ?>
          <option value="<?= $c ?>" <?= $cat === $c ? 'selected' : '' ?>>Permis <?= $c ?></option>
        <?php endforeach; ?>
      </select>
      <button class="bouton" type="submit" style="border:none;cursor:pointer;">Filtrer</button>
    </form>

    <div class="tableau-boite">
      <table>
        <thead>
          <tr>
            <th>Nom</th><th>E-mail</th><th>Téléphone</th>
            <th>Permis</th><th>Inscrit le</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($eleves) === 0): ?>
            <tr><td colspan="6" class="vide">Aucun élève trouvé.</td></tr>
          <?php endif; ?>
          <?php foreach ($eleves as $e): ?>
            <tr>
              <td><?= htmlspecialchars($e['nom']) ?></td>
              <td><?= htmlspecialchars($e['email']) ?></td>
              <td><?= htmlspecialchars($e['telephone'] ?? '—') ?></td>
              <td><span class="badge badge-jaune"><?= htmlspecialchars($e['categorie_permis']) ?></span></td>
              <td><?= date('d/m/Y', strtotime($e['date_inscription'])) ?></td>
              <td class="actions">
                <a class="bouton petit" href="form_eleve.php?id=<?= (int)$e['id'] ?>">Modifier</a>
                <a class="bouton petit bouton-danger" href="supprimer_eleve.php?id=<?= (int)$e['id'] ?>"
                   onclick="return confirm('Supprimer cet élève ?')">Supprimer</a>
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