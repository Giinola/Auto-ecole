<?php
$page_active = 'seances';
$titre_page  = 'Séances';
include 'navbar.php';
require_once 'db.php';

$q      = trim($_GET['q'] ?? '');
$statut = $_GET['statut'] ?? '';

$sql = "SELECT s.id, s.date_seance, s.duree_minutes, s.statut, s.remarques,
               u.nom AS eleve_nom,
               m.prenom AS moniteur_prenom, m.nom AS moniteur_nom,
               v.marque, v.modele, v.immatriculation
        FROM seances s
        JOIN eleves e    ON e.id = s.eleve_id
        JOIN users u     ON u.id = e.user_id
        JOIN moniteurs m ON m.id = s.moniteur_id
        JOIN vehicules v ON v.id = s.vehicule_id
        WHERE (u.nom LIKE :q1 OR m.nom LIKE :q2 OR m.prenom LIKE :q3)";
$params = ['q1' => "%$q%", 'q2' => "%$q%", 'q3' => "%$q%"];

if ($statut !== '') {
    $sql .= " AND s.statut = :statut";
    $params['statut'] = $statut;
}
$sql .= " ORDER BY s.date_seance DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$seances = $stmt->fetchAll();
?>
<link rel="stylesheet" href="tableaux.css">

<section class="section">
  <div class="container">
    <h2 class="titre-section">Séances de conduite</h2>

    <form class="barre-outils" method="GET">
      <input type="text" name="q" placeholder="Rechercher un élève ou un moniteur..."
             value="<?= htmlspecialchars($q) ?>">

      <select name="statut">
        <option value="">Tous les statuts</option>
        <option value="planifiee" <?= $statut === 'planifiee' ? 'selected' : '' ?>>Planifiée</option>
        <option value="terminee"  <?= $statut === 'terminee'  ? 'selected' : '' ?>>Terminée</option>
        <option value="annulee"   <?= $statut === 'annulee'   ? 'selected' : '' ?>>Annulée</option>
      </select>

      <button class="bouton" type="submit" style="border:none;cursor:pointer;">Filtrer</button>
    </form>

    <div class="tableau-boite">
      <table>
        <thead>
          <tr>
            <th>Date</th><th>Élève</th><th>Moniteur</th><th>Véhicule</th>
            <th>Durée</th><th>Statut</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($seances) === 0): ?>
            <tr><td colspan="7" class="vide">Aucune séance trouvée.</td></tr>
          <?php endif; ?>

          <?php foreach ($seances as $s):
            $classe = $s['statut'] === 'terminee' ? 'badge-vert'
                    : ($s['statut'] === 'planifiee' ? 'badge-jaune' : 'badge-rouge'); ?>
            <tr>
              <td><?= date('d/m/Y H:i', strtotime($s['date_seance'])) ?></td>
              <td><?= htmlspecialchars($s['eleve_nom']) ?></td>
              <td><?= htmlspecialchars($s['moniteur_prenom'] . ' ' . $s['moniteur_nom']) ?></td>
              <td><?= htmlspecialchars($s['marque'] . ' ' . $s['modele']) ?><br>
                  <small><?= htmlspecialchars($s['immatriculation']) ?></small></td>
              <td><?= (int)$s['duree_minutes'] ?> min</td>
              <td><span class="badge <?= $classe ?>"><?= htmlspecialchars($s['statut']) ?></span></td>
              <td class="actions">
                <a class="bouton petit" href="modifier_seance.php?id=<?= (int)$s['id'] ?>">Modifier</a>
                <a class="bouton petit bouton-danger" href="supprimer_seance.php?id=<?= (int)$s['id'] ?>"
                   onclick="return confirm('Supprimer cette séance ?')">Supprimer</a>
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