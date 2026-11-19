<?php
$page_active = 'statistiques';
$titre_page  = 'Statistiques';
include 'navbar.php';
?>
<link rel="stylesheet" href="tableaux.css">
<style>
.kpi-valeur { font-size:38px; font-weight:bold; color:#013E37; }
.kpi-label  { color:#4b5563; font-size:15px; }
.grille-graphiques { display:grid; grid-template-columns:repeat(auto-fit, minmax(340px,1fr)); gap:24px; margin-top:30px; }
.grille-graphiques canvas { width:100%; height:auto; }
.maj { text-align:right; color:#6b7280; font-size:13px; margin-bottom:10px; }
</style>

<section class="section">
  <div class="container">
    <h2 class="titre-section">Statistiques</h2>
    <p class="maj" id="maj">Chargement...</p>

    <div class="grille-cartes">
      <div class="carte"><div class="kpi-valeur" id="kpi-eleves">0</div><div class="kpi-label">Élèves</div></div>
      <div class="carte"><div class="kpi-valeur" id="kpi-seances">0</div><div class="kpi-label">Séances</div></div>
      <div class="carte"><div class="kpi-valeur" id="kpi-taux">0 %</div><div class="kpi-label">Taux de réussite aux examens</div></div>
      <div class="carte"><div class="kpi-valeur" id="kpi-vehicules">0</div><div class="kpi-label">Véhicules disponibles</div></div>
    </div>

    <div class="grille-graphiques">
      <div class="carte"><h3>Résultats des examens</h3><canvas id="g-examens" width="420" height="260"></canvas></div>
      <div class="carte"><h3>Séances par statut</h3><canvas id="g-statut" width="420" height="260"></canvas></div>
      <div class="carte"><h3>Séances par mois</h3><canvas id="g-mois" width="420" height="260"></canvas></div>
    </div>
  </div>
</section>

<script>
const VERT = '#013E37', JAUNE = '#F5C842', ROUGE = '#991b1b';

function preparer(canvas) {
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    return ctx;
}

function messageVide(ctx, W, H) {
    ctx.fillStyle = '#6b7280'; ctx.font = '15px Arial'; ctx.textAlign = 'center';
    ctx.fillText('Pas de données', W / 2, H / 2);
}

// Graphique 1 : anneau
function dessinerAnneau(canvas, labels, valeurs, couleurs) {
    const ctx = preparer(canvas), W = canvas.width, H = canvas.height;
    const total = valeurs.reduce((a, b) => a + b, 0);
    if (total === 0) return messageVide(ctx, W, H);

    const cx = W / 2 - 60, cy = H / 2, r = Math.min(W, H) / 2 - 20;
    let debut = -Math.PI / 2;
    valeurs.forEach((v, i) => {
        const angle = 2 * Math.PI * v / total;
        ctx.beginPath(); ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, r, debut, debut + angle); ctx.closePath();
        ctx.fillStyle = couleurs[i]; ctx.fill();
        debut += angle;
    });
    ctx.beginPath(); ctx.arc(cx, cy, r * 0.55, 0, 2 * Math.PI);
    ctx.fillStyle = '#fff'; ctx.fill();

    ctx.textAlign = 'left'; ctx.font = '14px Arial';
    labels.forEach((l, i) => {
        const y = cy - 30 + i * 26;
        ctx.fillStyle = couleurs[i]; ctx.fillRect(W - 150, y - 11, 14, 14);
        ctx.fillStyle = '#1f2937'; ctx.fillText(l + ' (' + valeurs[i] + ')', W - 130, y);
    });
}

// Graphique 2 : barres
function dessinerBarres(canvas, labels, valeurs, couleurs) {
    const ctx = preparer(canvas), W = canvas.width, H = canvas.height;
    if (valeurs.every(v => v === 0)) return messageVide(ctx, W, H);

    const marge = 40, max = Math.max(...valeurs, 1);
    const largeur = (W - 2 * marge) / labels.length;
    ctx.font = '13px Arial'; ctx.textAlign = 'center';
    labels.forEach((l, i) => {
        const h = (H - 2 * marge) * valeurs[i] / max;
        const x = marge + i * largeur + largeur * 0.15, y = H - marge - h;
        ctx.fillStyle = couleurs[i]; ctx.fillRect(x, y, largeur * 0.7, h);
        ctx.fillStyle = '#1f2937';
        ctx.fillText(valeurs[i], x + largeur * 0.35, y - 6);
        ctx.fillText(l, x + largeur * 0.35, H - marge + 18);
    });
    ctx.strokeStyle = '#9ca3af'; ctx.beginPath();
    ctx.moveTo(marge, H - marge); ctx.lineTo(W - marge, H - marge); ctx.stroke();
}

// Graphique 3 : courbe
function dessinerLigne(canvas, labels, valeurs) {
    const ctx = preparer(canvas), W = canvas.width, H = canvas.height;
    if (labels.length === 0) return messageVide(ctx, W, H);

    const marge = 40, max = Math.max(...valeurs, 1);
    const pas = labels.length > 1 ? (W - 2 * marge) / (labels.length - 1) : 0;
    const pts = valeurs.map((v, i) => [marge + i * pas, H - marge - (H - 2 * marge) * v / max]);

    ctx.strokeStyle = VERT; ctx.lineWidth = 3; ctx.beginPath();
    pts.forEach((p, i) => i ? ctx.lineTo(p[0], p[1]) : ctx.moveTo(p[0], p[1]));
    ctx.stroke();

    ctx.font = '13px Arial'; ctx.textAlign = 'center';
    pts.forEach((p, i) => {
        ctx.beginPath(); ctx.arc(p[0], p[1], 4, 0, 2 * Math.PI);
        ctx.fillStyle = VERT; ctx.fill();
        ctx.fillStyle = '#1f2937';
        ctx.fillText(valeurs[i], p[0], p[1] - 10);
        ctx.fillText(labels[i], p[0], H - marge + 18);
    });
}

// Récupération des données + mise à jour de la page
async function rafraichir() {
    try {
        const rep = await fetch('api_stats.php');
        if (!rep.ok) throw new Error('HTTP ' + rep.status);
        const d = await rep.json();

        document.getElementById('kpi-eleves').textContent    = d.kpi.eleves;
        document.getElementById('kpi-seances').textContent   = d.kpi.seances;
        document.getElementById('kpi-taux').textContent      = d.kpi.taux_reussite + ' %';
        document.getElementById('kpi-vehicules').textContent = d.kpi.vehicules_dispo;

        dessinerAnneau(document.getElementById('g-examens'),
            ['Réussi', 'Ajourné', 'En attente'],
            [d.examens.reussi, d.examens.ajourne, d.examens.en_attente],
            [VERT, ROUGE, JAUNE]);

        dessinerBarres(document.getElementById('g-statut'),
            ['Planifiées', 'Terminées', 'Annulées'],
            [d.seances_statut.planifiee, d.seances_statut.terminee, d.seances_statut.annulee],
            [JAUNE, VERT, ROUGE]);

        dessinerLigne(document.getElementById('g-mois'),
            d.seances_mois.map(m => m.mois),
            d.seances_mois.map(m => parseInt(m.total)));

        document.getElementById('maj').textContent =
            'Dernière mise à jour : ' + new Date().toLocaleTimeString('fr-FR');
    } catch (e) {
        document.getElementById('maj').textContent = 'Erreur de mise à jour des données';
    }
}

rafraichir();
setInterval(rafraichir, 10000);   // toutes les 10 secondes
</script>
</body>
</html>