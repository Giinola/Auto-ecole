<?php
require_once 'fonctions.php';
exiger_connexion();
require_once 'db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT nom, prenom FROM moniteurs WHERE id = :id");
$stmt->execute(['id' => $id]);
$mon = $stmt->fetch();

if (!$mon) {
    flash('erreur', 'Moniteur introuvable.');
} else {
    $nb = $pdo->prepare("SELECT COUNT(*) FROM seances WHERE moniteur_id = :id");
    $nb->execute(['id' => $id]);
    $nbSeances = (int)$nb->fetchColumn();

    if ($nbSeances > 0) {
        flash('erreur', 'Impossible de supprimer ce moniteur : il est affecté à ' . $nbSeances
            . ' séance(s). Supprimez ou modifiez d\'abord ces séances.');
    } else {
        try {
            $pdo->prepare("DELETE FROM moniteurs WHERE id = :id")->execute(['id' => $id]);
            historique($pdo, 'suppression', 'moniteurs', $id,
                'Suppression du moniteur ' . $mon['prenom'] . ' ' . $mon['nom']);
            flash('succes', 'Moniteur supprimé avec succès.');
        } catch (PDOException $ex) {
            flash('erreur', 'Impossible de supprimer ce moniteur.');
        }
    }
}
header('Location: moniteurs.php');
exit;