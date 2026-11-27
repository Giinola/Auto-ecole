<?php
$page_active = 'vehicules';
$titre_page  = 'Véhicules';
include 'navbar.php';
require_once 'db.php';

$q    = trim($_GET['q'] ?? '');
$cat  = $_GET['categorie'] ?? '';
$etat = $_GET['etat'] ?? '';

$sql = "SELECT id, immatriculation, marque, modele, categorie, annee, etat
        FROM vehicules
        WHERE (marque LIKE :q1 OR modele LIKE :q2 OR immatriculation LIKE :q3)";
$params = ['q1' => "%$q%", 'q2' => "%$q%", 'q3' => "%$q%"];

if ($cat !== '') {
    $sql .= " AND categorie = :cat";
    $params['cat'] = $cat;
}
if ($etat !== '') {
    $sql .= " AND etat = :etat";
    $params['etat'] = $etat;
}
$sql .= " ORDER BY marque, modele";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vehicules = $stmt->fetchAll();
?>
<link rel="stylesheet" href="tableaux.css">

<section class="section">
  <div class="container">
    <h2 class="titre-section">Véhicules</h2>
    <?php require_once 'fonctions.php'; afficher_flash(); ?>
       <p style="margin-bottom:16px;"><a class="bouton" href="form_vehicule.php">+ Ajouter un véhicule</a></p>

    <form class="barre-outils" method="GET">
      <input type="text" name="q" placeholder="Rechercher (marque, modèle, immatriculation)..."
             value="<?= htmlspecialchars($q) ?>">

      <select name="categorie">
        <option value="">Toutes les catégories</option>
        <?php foreach (['A','B','C'] as $c): ?>
          <option value="<?= $c ?>" <?= $cat === $c ? 'selected' : '' ?>>Catégorie <?= $c ?></option>
        <?php endforeach; ?>
      </select>

      <select name="etat">
        <option value="">Tous les états</option>
        <option value="disponible"  <?= $etat === 'disponible'  ? 'selected' : '' ?>>Disponible</option>
        <option value="en_seance"   <?= $etat === 'en_seance'   ? 'selected' : '' ?>>En séance</option>
        <option value="maintenance" <?= $etat === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
      </select>

      <button class="bouton" type="submit" style="border:none;cursor:pointer;">Filtrer</button>
    </form>

    <?php if (count($vehicules) === 0): ?>
      <p class="vide">Aucun véhicule trouvé.</p>
    <?php endif; ?>

    <div class="grille-cartes">
      <?php foreach ($vehicules as $v):
        $classe = $v['etat'] === 'disponible' ? 'badge-vert'
                : ($v['etat'] === 'en_seance' ? 'badge-jaune' : 'badge-rouge'); ?>
        <div class="carte">
          <h3><?= htmlspecialchars($v['marque'] . ' ' . $v['modele']) ?></h3>
          <p>
            Immatriculation : <?= htmlspecialchars($v['immatriculation']) ?><br>
            Catégorie : <?= htmlspecialchars($v['categorie']) ?>
            · Année : <?= $v['annee'] ? (int)$v['annee'] : '—' ?>
          </p>

          <span class="badge <?= $classe ?>"><?= htmlspecialchars(str_replace('_', ' ', $v['etat'])) ?></span>

          <div class="actions" style="margin-top:14px;">
            <a class="bouton petit" href="form_vehicule.php?id=<?= (int)$v['id'] ?>">Modifier</a>
            <a class="bouton petit bouton-danger" href="supprimer_vehicule.php?id=<?= (int)$v['id'] ?>"
               onclick="return confirm('Supprimer ce véhicule ?')">Supprimer</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
</body>
</html>