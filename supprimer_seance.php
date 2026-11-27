<?php
require_once 'fonctions.php';
exiger_connexion();
require_once 'db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT s.date_seance, u.nom
     FROM seances s
     JOIN eleves e ON e.id = s.eleve_id
     JOIN users u ON u.id = e.user_id
     WHERE s.id = :id"
);
$stmt->execute(['id' => $id]);
$se = $stmt->fetch();

if (!$se) {
    flash('erreur', 'Séance introuvable.');
} else {
    try {
        $pdo->prepare("DELETE FROM seances WHERE id = :id")->execute(['id' => $id]);
        historique($pdo, 'suppression', 'seances', $id,
            'Suppression de la séance du ' . date('d/m/Y H:i', strtotime($se['date_seance'])) . ' - élève ' . $se['nom']);
        flash('succes', 'Séance supprimée avec succès.');
    } catch (PDOException $ex) {
        flash('erreur', 'Impossible de supprimer cette séance.');
    }
}
header('Location: seances.php');
exit;