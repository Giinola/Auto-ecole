<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['erreur' => 'non connecte']);
    exit;
}
require_once 'db.php';

$nb_eleves  = (int)$pdo->query("SELECT COUNT(*) FROM eleves")->fetchColumn();
$nb_seances = (int)$pdo->query("SELECT COUNT(*) FROM seances")->fetchColumn();
$vehicules_dispo = (int)$pdo->query("SELECT COUNT(*) FROM vehicules WHERE etat = 'disponible'")->fetchColumn();

// Examens par résultat
$ex = $pdo->query("SELECT resultat, COUNT(*) FROM examens GROUP BY resultat")
          ->fetchAll(PDO::FETCH_KEY_PAIR);
$reussi  = (int)($ex['reussi'] ?? 0);
$ajourne = (int)($ex['ajourne'] ?? 0);
$attente = (int)($ex['en_attente'] ?? 0);
// Taux calculé sur les examens déjà passés (on ignore "en attente")
$taux = ($reussi + $ajourne) > 0 ? round(100 * $reussi / ($reussi + $ajourne)) : 0;

// Séances par statut
$se = $pdo->query("SELECT statut, COUNT(*) FROM seances GROUP BY statut")
          ->fetchAll(PDO::FETCH_KEY_PAIR);

// Séances par mois (6 derniers mois)
$mois = $pdo->query(
    "SELECT DATE_FORMAT(date_seance, '%Y-%m') AS mois, COUNT(*) AS total
     FROM seances
     WHERE date_seance >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     GROUP BY DATE_FORMAT(date_seance, '%Y-%m')
     ORDER BY 1"
)->fetchAll();

echo json_encode([
    'kpi' => [
        'eleves'          => $nb_eleves,
        'seances'         => $nb_seances,
        'taux_reussite'   => $taux,
        'vehicules_dispo' => $vehicules_dispo,
    ],
    'examens' => ['reussi' => $reussi, 'ajourne' => $ajourne, 'en_attente' => $attente],
    'seances_statut' => [
        'planifiee' => (int)($se['planifiee'] ?? 0),
        'terminee'  => (int)($se['terminee'] ?? 0),
        'annulee'   => (int)($se['annulee'] ?? 0),
    ],
    'seances_mois' => $mois,
]);