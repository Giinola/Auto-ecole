<?php
require_once 'fonctions.php';
exiger_connexion();
require_once 'db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT e.user_id, e.photo, u.nom, u.role
     FROM eleves e JOIN users u ON u.id = e.user_id WHERE e.id = :id"
);
$stmt->execute(['id' => $id]);
$el = $stmt->fetch();

if (!$el) {
    flash('erreur', 'Élève introuvable.');
} else {
    try {
        $pdo->beginTransaction();
       
        $pdo->prepare("DELETE FROM eleves WHERE id = :id")->execute(['id' => $id]);
        if ($el['role'] === 'eleve') {
            $pdo->prepare("DELETE FROM users WHERE id = :uid")->execute(['uid' => $el['user_id']]);
        }
        historique($pdo, 'suppression', 'eleves', $id, 'Suppression de l\'élève ' . $el['nom']);
        $pdo->commit();

        if (!empty($el['photo'])) @unlink(__DIR__ . '/uploads/' . $el['photo']);
        flash('succes', 'Élève supprimé avec succès.');
    } catch (PDOException $ex) {
        $pdo->rollBack();
        flash('erreur', "Impossible de supprimer cet élève.");
    }
}
header('Location: eleves.php');
exit;