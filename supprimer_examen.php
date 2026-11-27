<?php
require_once 'fonctions.php';
exiger_connexion();
require_once 'db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT x.type_examen, x.date_examen, u.nom
     FROM examens x
     JOIN eleves e ON e.id = x.eleve_id
     JOIN users u ON u.id = e.user_id
     WHERE x.id = :id"
);
$stmt->execute(['id' => $id]);
$ex = $stmt->fetch();

if (!$ex) {
    flash('erreur', 'Examen introuvable.');
} else {
    try {
        $pdo->prepare("DELETE FROM examens WHERE id = :id")->execute(['id' => $id]);
        historique($pdo, 'suppression', 'examens', $id,
            'Suppression de l\'examen de ' . $ex['type_examen'] . ' du '
            . date('d/m/Y', strtotime($ex['date_examen'])) . ' - élève ' . $ex['nom']);
        flash('succes', 'Examen supprimé avec succès.');
    } catch (PDOException $e) {
        flash('erreur', 'Impossible de supprimer cet examen.');
    }
}
header('Location: examens.php');
exit;