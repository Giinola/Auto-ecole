<?php
require_once 'fonctions.php';
exiger_connexion();
require_once 'db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT marque, modele, immatriculation FROM vehicules WHERE id = :id");
$stmt->execute(['id' => $id]);
$veh = $stmt->fetch();

if (!$veh) {
    flash('erreur', 'Véhicule introuvable.');
} else {
    $nb = $pdo->prepare("SELECT COUNT(*) FROM seances WHERE vehicule_id = :id");
    $nb->execute(['id' => $id]);
    $nbSeances = (int)$nb->fetchColumn();

    if ($nbSeances > 0) {
        flash('erreur', 'Impossible de supprimer ce véhicule : il est utilisé dans ' . $nbSeances
            . ' séance(s). Supprimez ou modifiez d\'abord ces séances.');
    } else {
        try {
            $pdo->prepare("DELETE FROM vehicules WHERE id = :id")->execute(['id' => $id]);
            historique($pdo, 'suppression', 'vehicules', $id,
                'Suppression du véhicule ' . $veh['marque'] . ' ' . $veh['modele'] . ' (' . $veh['immatriculation'] . ')');
            flash('succes', 'Véhicule supprimé avec succès.');
        } catch (PDOException $ex) {
            flash('erreur', 'Impossible de supprimer ce véhicule.');
        }
    }
}
header('Location: vehicules.php');
exit;